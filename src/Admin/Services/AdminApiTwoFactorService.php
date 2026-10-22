<?php

namespace Webkul\BagistoApi\Admin\Services;

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuthAuditor;
use Webkul\BagistoApi\Admin\Models\AdminApiTwoFactorChallenge;
use Webkul\BagistoApi\Admin\Repositories\AdminApiTwoFactorChallengeRepository;
use Webkul\BagistoApi\Admin\Repositories\AdminCredentialRepository;
use Webkul\BagistoApi\Exception\AdminAuthException;
use Webkul\User\Models\Admin;
use Webkul\User\Repositories\AdminRepository;

class AdminApiTwoFactorService
{
    /**
     * The number of random bytes in a challenge token's secret.
     */
    public const CHALLENGE_SECRET_BYTES = 32;

    /**
     * Wrong codes an admin may enter across all challenges before waiting.
     */
    public const MAX_FAILURES_PER_ADMIN = 5;

    /**
     * Seconds an admin's wrong codes are remembered for.
     */
    public const FAILURE_DECAY_SECONDS = 60;

    /**
     * The outcome of a code that was accepted and recorded.
     */
    protected const OUTCOME_ACCEPTED = 'accepted';

    /**
     * The outcome of a code or backup code that did not match.
     */
    protected const OUTCOME_CODE_REJECTED = 'code-rejected';

    /**
     * The outcome of a challenge that was used up, or whose admin is gone, while the code was checked.
     */
    protected const OUTCOME_CHALLENGE_INVALID = 'challenge-invalid';

    /**
     * Create a new two-factor service.
     */
    public function __construct(
        protected AdminApiTwoFactorChallengeRepository $challengeRepository,
        protected AdminRepository $adminRepository,
        protected AdminCredentialRepository $credentialRepository,
        protected AdminApiAuthAuditor $auditor,
        protected RateLimiter $limiter,
    ) {}

    /**
     * Whether the admin must pass a second factor, as core decides it: enabled and set up.
     */
    public function isRequiredFor(Admin $admin): bool
    {
        return (bool) $admin->two_factor_enabled
            && ! empty($admin->two_factor_secret);
    }

    /**
     * Start a single-use challenge for an admin whose password was accepted.
     *
     * @param  array{name?: ?string, client_type?: ?string, ip_address?: ?string, user_agent?: ?string}  $client
     */
    public function createChallenge(Admin $admin, array $client = []): AdminApiTwoFactorChallengeToken
    {
        Event::dispatch('bagistoapi.admin.api-two-factor-challenge.create.before', $admin);

        $secret = bin2hex(random_bytes(self::CHALLENGE_SECRET_BYTES));

        $challenge = $this->challengeRepository->create([
            'admin_id' => $admin->id,
            'token' => $this->hash($secret),
            'name' => $this->limit($client['name'] ?? null, 100),
            'client_type' => $client['client_type'] ?? null,
            'ip_address' => $client['ip_address'] ?? null,
            'user_agent' => $this->limit($client['user_agent'] ?? null, 1023),
            'expires_at' => Carbon::now()->addMinutes(AdminApiTwoFactorChallenge::LIFETIME_MINUTES),
        ]);

        Event::dispatch('bagistoapi.admin.api-two-factor-challenge.create.after', $challenge);

        return new AdminApiTwoFactorChallengeToken($challenge, $challenge->id.'|'.$secret);
    }

    /**
     * Check a code or backup code against a challenge and use the challenge up, as core's verify screen does.
     *
     * @throws AdminAuthException
     */
    public function verify(string $challengeToken, ?string $code, ?string $backupCode, Request $request): AdminApiTwoFactorChallenge
    {
        $challenge = $this->findPending($challengeToken);

        $admin = $challenge->admin;

        if (
            ! $admin instanceof Admin
            || ! $this->isRequiredFor($admin)
        ) {
            $this->rejectChallenge();
        }

        $this->assertNotLockedOut($admin);

        $outcome = $this->credentialRepository->withLockedAdmin(
            $admin->id,
            fn (?Admin $locked) => $this->useSecondFactor($locked, $challenge, $code, $backupCode),
        );

        if ($outcome === self::OUTCOME_CODE_REJECTED) {
            $this->rejectCode($challenge, $admin, $request, $backupCode !== null);
        }

        if ($outcome !== self::OUTCOME_ACCEPTED) {
            $this->rejectChallenge();
        }

        $this->limiter->clear($this->throttleKey($admin));

        return $challenge;
    }

    /**
     * Revoke every challenge of an admin still waiting for a code.
     */
    public function revokePendingForAdmin(int $adminId): int
    {
        return $this->challengeRepository->revokePendingForAdmin($adminId);
    }

    /**
     * Find the pending challenge a plain token names, when its secret matches.
     *
     * @throws AdminAuthException
     */
    protected function findPending(string $plain): AdminApiTwoFactorChallenge
    {
        $parts = explode('|', $plain, 2);

        if (
            count($parts) !== 2
            || ! ctype_digit($parts[0])
            || $parts[1] === ''
        ) {
            $this->rejectChallenge();
        }

        $challenge = $this->challengeRepository->findWithAdmin((int) $parts[0]);

        if (
            ! $challenge
            || ! hash_equals($challenge->token, $this->hash($parts[1]))
            || ! $challenge->isPending()
        ) {
            $this->rejectChallenge();
        }

        return $challenge;
    }

    /**
     * Check the code against the locked admin, use the challenge up and record the code, returning the outcome.
     */
    protected function useSecondFactor(?Admin $admin, AdminApiTwoFactorChallenge $challenge, ?string $code, ?string $backupCode): string
    {
        if ($admin === null) {
            return self::OUTCOME_CHALLENGE_INVALID;
        }

        $changes = $backupCode !== null
            ? $this->acceptBackupCode($admin, $backupCode)
            : $this->acceptCode($admin, (string) $code);

        if ($changes === null) {
            return self::OUTCOME_CODE_REJECTED;
        }

        if (! $this->challengeRepository->consume($challenge->id)) {
            return self::OUTCOME_CHALLENGE_INVALID;
        }

        $this->adminRepository->update($changes, $admin->id);

        return self::OUTCOME_ACCEPTED;
    }

    /**
     * The admin changes that record an accepted authenticator code, or null when it is not accepted.
     */
    protected function acceptCode(Admin $admin, string $code): ?array
    {
        try {
            $secret = decrypt($admin->two_factor_secret);
        } catch (DecryptException $exception) {
            return null;
        }

        $window = two_factor_authentication()->verifyQrCode($secret, $code, $admin->two_factor_last_used_window);

        return $window
            ? ['two_factor_last_used_window' => $window]
            : null;
    }

    /**
     * The admin changes that burn an accepted backup code, or null when no backup code matches.
     */
    protected function acceptBackupCode(Admin $admin, string $backupCode): ?array
    {
        $remaining = two_factor_authentication()->verifyBackupCode($admin->two_factor_backup_codes ?? [], $backupCode);

        return $remaining === null
            ? null
            : ['two_factor_backup_codes' => $remaining];
    }

    /**
     * Count a wrong code, revoking the challenge on the last allowed attempt, and refuse the request.
     *
     * @throws AdminAuthException
     */
    protected function rejectCode(AdminApiTwoFactorChallenge $challenge, Admin $admin, Request $request, bool $backupCode): never
    {
        $this->limiter->hit($this->throttleKey($admin), self::FAILURE_DECAY_SECONDS);

        $challenge = $this->challengeRepository->recordFailure($challenge->id);

        $this->auditor->record(AdminApiAuthAuditor::EVENT_TWO_FACTOR_FAILED, $admin, null, $request, [
            'challenge_id' => (int) $challenge->id,
            'backup_code' => $backupCode,
            'failed_attempts' => $challenge->failed_attempts,
            'revoked' => $challenge->revoked_at !== null,
        ]);

        if ($challenge->revoked_at !== null) {
            throw new AdminAuthException(
                trans('bagistoapi::app.admin.auth.errors.two-factor-attempts-exceeded'),
                AdminAuthException::CODE_TWO_FACTOR_CHALLENGE_INVALID,
            );
        }

        throw new AdminAuthException(
            trans('admin::app.account.messages.invalid-code'),
            AdminAuthException::CODE_INVALID_TWO_FACTOR_CODE,
        );
    }

    /**
     * Refuse a code while the admin has entered too many wrong ones, telling the client when to retry.
     *
     * @throws AdminAuthException
     */
    protected function assertNotLockedOut(Admin $admin): void
    {
        $key = $this->throttleKey($admin);

        if (! $this->limiter->tooManyAttempts($key, self::MAX_FAILURES_PER_ADMIN)) {
            return;
        }

        $seconds = max(1, $this->limiter->availableIn($key));

        throw new AdminAuthException(
            trans('bagistoapi::app.admin.auth.errors.too-many-login-attempts', ['seconds' => $seconds]),
            AdminAuthException::CODE_TOO_MANY_LOGIN_ATTEMPTS,
            429,
            ['Retry-After' => (string) $seconds],
        );
    }

    /**
     * The throttle key of an admin's wrong two-factor codes.
     */
    protected function throttleKey(Admin $admin): string
    {
        return 'bagistoapi-admin-two-factor:'.$admin->id;
    }

    /**
     * Refuse a challenge token that is unknown, used, revoked or expired.
     *
     * @throws AdminAuthException
     */
    protected function rejectChallenge(): never
    {
        throw new AdminAuthException(
            trans('bagistoapi::app.admin.auth.errors.two-factor-challenge-invalid'),
            AdminAuthException::CODE_TWO_FACTOR_CHALLENGE_INVALID,
        );
    }

    /**
     * Truncate a client-supplied value to its column length.
     */
    protected function limit(?string $value, int $length): ?string
    {
        return $value === null
            ? null
            : Str::limit($value, $length, '');
    }

    /**
     * The stored hash of a challenge token secret.
     */
    protected function hash(string $secret): string
    {
        return hash('sha256', $secret);
    }
}
