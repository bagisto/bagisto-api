<?php

namespace Webkul\BagistoApi\Admin\Services;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuthAuditor;
use Webkul\BagistoApi\Exception\AdminAuthException;
use Webkul\Core\Helpers\SessionPurger;
use Webkul\User\Models\Admin;
use Webkul\User\Repositories\AdminRepository;

class AdminApiPasswordService
{
    /**
     * The password broker core's admin forgot and reset screens use.
     */
    public const BROKER = 'admins';

    /**
     * The forgot or reset requests a client address may make before waiting.
     */
    public const MAX_REQUESTS = 5;

    /**
     * Seconds forgot and reset requests are counted for.
     */
    public const DECAY_SECONDS = 60;

    /**
     * Create a new admin password service.
     */
    public function __construct(
        protected AdminRepository $adminRepository,
        protected AdminApiCredentialRevoker $revoker,
        protected AdminApiAuthAuditor $auditor,
        protected RateLimiter $limiter,
        protected SessionPurger $sessionPurger,
    ) {}

    /**
     * Send core's reset link when the email belongs to an admin, answering the same way when it does not.
     *
     * @throws AdminAuthException
     */
    public function sendResetLink(string $email, Request $request): void
    {
        $this->throttle('forgot', $request);

        try {
            $status = $this->broker()->sendResetLink(['email' => $email]);
        } catch (\Throwable $exception) {
            report($exception);

            $status = null;
        }

        $this->auditor->record(AdminApiAuthAuditor::EVENT_PASSWORD_RESET_REQUESTED, null, null, $request, [
            'email' => $email,
            'status' => $status,
        ]);
    }

    /**
     * Reset a password with a reset token as core does, then end every API session of the admin.
     *
     * @param  array{email: string, password: string, password_confirmation: string, token: string}  $credentials
     *
     * @throws AdminAuthException
     */
    public function reset(array $credentials, Request $request): Admin
    {
        $this->throttle('reset', $request);

        $resetAdmin = null;

        $status = $this->broker()->reset($credentials, function (Admin $admin, string $password) use (&$resetAdmin) {
            $resetAdmin = $this->resetPassword($admin, $password);
        });

        if (
            $status !== Password::PASSWORD_RESET
            || ! $resetAdmin instanceof Admin
        ) {
            $this->auditor->record(AdminApiAuthAuditor::EVENT_PASSWORD_RESET, null, null, $request, [
                'email' => $credentials['email'],
                'status' => $status,
            ]);

            throw new AdminAuthException(
                trans('bagistoapi::app.admin.auth.errors.password-reset-token-invalid'),
                AdminAuthException::CODE_PASSWORD_RESET_TOKEN_INVALID,
                422,
            );
        }

        $this->auditor->record(AdminApiAuthAuditor::EVENT_PASSWORD_RESET, $resetAdmin, null, $request, [
            'status' => $status,
        ]);

        return $resetAdmin;
    }

    /**
     * Store the new password and remember token, drop core's web sessions and end the API sessions.
     */
    protected function resetPassword(Admin $admin, string $password): Admin
    {
        $admin = $this->adminRepository->update([
            'password' => Hash::make($password),
        ], $admin->id);

        $admin->setRememberToken(Str::random(60));

        $admin->save();

        $this->sessionPurger->forget('admin', $admin->id);

        $this->revoker->passwordReset($admin);

        Event::dispatch(new PasswordReset($admin));

        return $admin;
    }

    /**
     * Refuse a forgot or reset request once the client address has made too many in a minute.
     *
     * @throws AdminAuthException
     */
    protected function throttle(string $action, Request $request): void
    {
        $key = 'bagistoapi-admin-password-'.$action.':'.$request->ip();

        if ($this->limiter->tooManyAttempts($key, self::MAX_REQUESTS)) {
            $seconds = max(1, $this->limiter->availableIn($key));

            throw new AdminAuthException(
                trans('bagistoapi::app.admin.auth.errors.too-many-password-requests', ['seconds' => $seconds]),
                AdminAuthException::CODE_TOO_MANY_PASSWORD_REQUESTS,
                429,
                ['Retry-After' => (string) $seconds],
            );
        }

        $this->limiter->hit($key, self::DECAY_SECONDS);
    }

    /**
     * The admin password broker.
     */
    protected function broker(): PasswordBroker
    {
        return Password::broker(self::BROKER);
    }
}
