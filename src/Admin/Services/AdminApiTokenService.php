<?php

namespace Webkul\BagistoApi\Admin\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Models\AdminApiRefreshToken;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Repositories\AdminApiRefreshTokenRepository;
use Webkul\BagistoApi\Admin\Repositories\AdminApiSessionRepository;
use Webkul\BagistoApi\Admin\Repositories\AdminApiTokenRepository;
use Webkul\BagistoApi\Admin\Repositories\AdminApiTwoFactorChallengeRepository;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtKeyStore;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtSigner;
use Webkul\User\Contracts\Admin;

class AdminApiTokenService
{
    /**
     * The number of random bytes in a refresh token's secret.
     */
    public const REFRESH_SECRET_BYTES = 32;

    /**
     * Seconds a retired signing key is kept after the longest access token it signed has expired.
     */
    public const RETIRED_KEY_GRACE_SECONDS = 300;

    /**
     * Create a new token service.
     */
    public function __construct(
        protected AdminApiSessionRepository $sessionRepository,
        protected AdminApiRefreshTokenRepository $refreshTokenRepository,
        protected AdminApiTokenRepository $apiTokenRepository,
        protected AdminApiTwoFactorChallengeRepository $challengeRepository,
        protected AdminApiAuthSettings $settings,
        protected JwtSigner $signer,
        protected JwtKeyStore $keys,
    ) {}

    /**
     * Start a session for an admin and issue its first access and refresh tokens.
     *
     * @param  array{name?: ?string, client_type?: ?string, ip_address?: ?string, user_agent?: ?string, channel?: ?string}  $client
     */
    public function issue(Admin $admin, array $client = []): AdminApiTokenPair
    {
        Event::dispatch('bagistoapi.admin.api-session.create.before', $admin);

        $now = Carbon::now();

        $expiresAt = $now->copy()->addDays($this->settings->sessionLifetime());

        $session = $this->sessionRepository->create([
            'admin_id' => $admin->id,
            'name' => $this->limit($client['name'] ?? null, 100),
            'client_type' => $client['client_type'] ?? null,
            'ip_address' => $client['ip_address'] ?? null,
            'user_agent' => $this->limit($client['user_agent'] ?? null, 1023),
            'last_ip_address' => $client['ip_address'] ?? null,
            'last_user_agent' => $this->limit($client['user_agent'] ?? null, 1023),
            'last_used_at' => $now,
            'last_channel' => $this->channel($client['channel'] ?? null),
            'refresh_expires_at' => $this->refreshExpiry($now, $expiresAt),
            'expires_at' => $expiresAt,
        ]);

        $pair = $this->issuePair($session, $now);

        Event::dispatch('bagistoapi.admin.api-session.create.after', $session);

        return $pair;
    }

    /**
     * Exchange a refresh token for a new pair; a reused token revokes the whole session.
     *
     * @param  array{ip_address?: ?string, user_agent?: ?string, channel?: ?string}  $client
     *
     * @throws InvalidRefreshTokenException
     */
    public function rotate(string $refreshToken, array $client = []): AdminApiTokenPair
    {
        $token = $this->findRefreshToken($refreshToken);

        $session = $token->session;

        if ($token->isUsed()) {
            $this->revokeForReuse($session);
        }

        if (
            ! $session->isActive()
            || $token->isExpired()
            || ! $this->isAdminActive($session)
        ) {
            throw new InvalidRefreshTokenException(session: $session);
        }

        $pair = $this->refreshTokenRepository->transaction(fn () => $this->claimAndReplace($token, $session, $client));

        if (! $pair) {
            $this->revokeForReuse($session);
        }

        Event::dispatch('bagistoapi.admin.api-session.refresh.after', $pair->session);

        return $pair;
    }

    /**
     * Revoke one session, which invalidates its access and refresh tokens at once.
     */
    public function revoke(AdminApiSession $session, string $reason): AdminApiSession
    {
        Event::dispatch('bagistoapi.admin.api-session.revoke.before', $session);

        $session = $this->sessionRepository->revoke($session, $reason);

        Event::dispatch('bagistoapi.admin.api-session.revoke.after', $session);

        return $session;
    }

    /**
     * Revoke every live session of an admin, optionally keeping one, and return how many were revoked.
     */
    public function revokeAllForAdmin(int $adminId, string $reason, ?int $exceptSessionId = null): int
    {
        Event::dispatch('bagistoapi.admin.api-session.revoke-all.before', $adminId);

        $revoked = $this->sessionRepository->revokeAllForAdmin($adminId, $reason, $exceptSessionId);

        Event::dispatch('bagistoapi.admin.api-session.revoke-all.after', $adminId);

        return $revoked;
    }

    /**
     * Whether a session may still authenticate requests: live, inside its lifetime, with an active admin.
     */
    public function isSessionUsable(AdminApiSession $session): bool
    {
        return $session->isActive()
            && $this->isAdminActive($session);
    }

    /**
     * Delete expired refresh tokens and challenges, long-ended sessions and retired keys nothing uses.
     *
     * @return array{refresh_tokens: int, sessions: int, two_factor_challenges: int, keys: array<int, string>}
     */
    public function prune(): array
    {
        $now = Carbon::now();

        $refreshTokens = $this->refreshTokenRepository->deleteExpiredBefore($now);

        $sessions = $this->sessionRepository->deleteEndedBefore(
            $now->copy()->subDays($this->settings->pruneAfterDays())
        );

        return [
            'refresh_tokens' => $refreshTokens,
            'sessions' => $sessions,
            'two_factor_challenges' => $this->challengeRepository->deleteExpiredBefore($now),
            'keys' => $this->pruneRetiredKeys($now),
        ];
    }

    /**
     * Issue an access token and a new refresh token inside a session.
     */
    protected function issuePair(AdminApiSession $session, CarbonInterface $now): AdminApiTokenPair
    {
        [$refreshToken, $plainRefreshToken] = $this->createRefreshToken($session);

        $accessExpiresAt = $now->copy()->addMinutes($this->settings->accessTokenTtl());

        if ($accessExpiresAt->greaterThan($session->expires_at)) {
            $accessExpiresAt = $session->expires_at->copy();
        }

        return new AdminApiTokenPair(
            accessToken: $this->signer->signAccessToken($session->admin_id, $session->id, $now, $accessExpiresAt),
            accessTokenExpiresAt: $accessExpiresAt,
            refreshToken: $plainRefreshToken,
            refreshTokenId: $refreshToken->id,
            refreshTokenExpiresAt: $refreshToken->expires_at,
            session: $session,
            issuedAt: $now,
        );
    }

    /**
     * Create a refresh token for a session and return it with its plain `<id>|<secret>` value.
     *
     * @return array{0: AdminApiRefreshToken, 1: string}
     */
    protected function createRefreshToken(AdminApiSession $session): array
    {
        $secret = bin2hex(random_bytes(self::REFRESH_SECRET_BYTES));

        $refreshToken = $this->refreshTokenRepository->create([
            'session_id' => $session->id,
            'token' => $this->hash($secret),
            'expires_at' => $session->refresh_expires_at,
        ]);

        return [$refreshToken, $refreshToken->id.'|'.$secret];
    }

    /**
     * Find the refresh token a plain value names, when its secret matches.
     *
     * @throws InvalidRefreshTokenException
     */
    protected function findRefreshToken(string $plain): AdminApiRefreshToken
    {
        $parts = explode('|', $plain, 2);

        if (
            count($parts) !== 2
            || ! ctype_digit($parts[0])
            || $parts[1] === ''
        ) {
            throw new InvalidRefreshTokenException;
        }

        $token = $this->refreshTokenRepository->findWithSession((int) $parts[0]);

        if (
            ! $token
            || ! $token->session
            || ! hash_equals($token->token, $this->hash($parts[1]))
        ) {
            throw new InvalidRefreshTokenException;
        }

        return $token;
    }

    /**
     * Claim a refresh token and issue the pair that replaces it, or return null when another request claimed it first.
     *
     * @param  array{ip_address?: ?string, user_agent?: ?string, channel?: ?string}  $client
     */
    protected function claimAndReplace(AdminApiRefreshToken $token, AdminApiSession $session, array $client): ?AdminApiTokenPair
    {
        if (! $this->refreshTokenRepository->claim($token->id)) {
            return null;
        }

        Event::dispatch('bagistoapi.admin.api-session.refresh.before', $session);

        $now = Carbon::now();

        $session = $this->sessionRepository->touchUsage($session, [
            'refresh_expires_at' => $this->refreshExpiry($now, $session->expires_at),
            'last_ip_address' => $client['ip_address'] ?? $session->last_ip_address,
            'last_user_agent' => $this->limit($client['user_agent'] ?? $session->last_user_agent, 1023),
            'last_used_at' => $now,
            'last_channel' => $this->channel($client['channel'] ?? $session->last_channel),
        ]);

        $pair = $this->issuePair($session, $now);

        $this->refreshTokenRepository->markReplacedBy($token->id, $pair->refreshTokenId);

        return $pair;
    }

    /**
     * Revoke the session a used refresh token was presented for, and reject the request.
     *
     * @throws InvalidRefreshTokenException
     */
    protected function revokeForReuse(AdminApiSession $session): never
    {
        if (! $session->isRevoked()) {
            $this->revoke($session, AdminApiSession::REVOKED_REFRESH_REUSE);
        }

        throw new InvalidRefreshTokenException(reused: true, session: $session);
    }

    /**
     * Drop the retired keys past their grace period that no active API token is signed with.
     *
     * @return array<int, string>
     */
    protected function pruneRetiredKeys(CarbonInterface $now): array
    {
        $retired = $this->keys->retiredKeys();

        if ($retired === []) {
            return [];
        }

        $inUse = $this->apiTokenRepository->signingKidsInUse();

        $cutoff = $now->copy()
            ->subMinutes(JwtSigner::longestAccessTokenMinutes())
            ->subSeconds((int) config('bagistoapi.admin_auth.jwt.leeway', 30) + self::RETIRED_KEY_GRACE_SECONDS);

        $dropped = [];

        foreach ($retired as $kid => $retiredAt) {
            if (
                in_array($kid, $inUse, true)
                || $retiredAt->greaterThan($cutoff)
            ) {
                continue;
            }

            $this->keys->forget($kid);

            $dropped[] = $kid;
        }

        return $dropped;
    }

    /**
     * The refresh expiry for a token issued now, never past the session's absolute lifetime.
     */
    protected function refreshExpiry(CarbonInterface $now, CarbonInterface $sessionExpiresAt): CarbonInterface
    {
        $expiresAt = $now->copy()->addDays($this->settings->refreshTokenTtl());

        return $expiresAt->greaterThan($sessionExpiresAt)
            ? $sessionExpiresAt->copy()
            : $expiresAt;
    }

    /**
     * Whether the session's admin exists and is active.
     */
    protected function isAdminActive(AdminApiSession $session): bool
    {
        return $session->admin !== null
            && (bool) $session->admin->status;
    }

    /**
     * A known channel, or null.
     */
    protected function channel(?string $channel): ?string
    {
        return in_array($channel, AdminApiSession::CHANNELS, true)
            ? $channel
            : null;
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
     * The stored hash of a refresh token secret.
     */
    protected function hash(string $secret): string
    {
        return hash('sha256', $secret);
    }
}
