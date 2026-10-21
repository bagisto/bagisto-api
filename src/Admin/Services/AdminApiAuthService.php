<?php

namespace Webkul\BagistoApi\Admin\Services;

use Illuminate\Auth\AuthManager;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuthAuditor;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Exception\AdminAuthException;
use Webkul\User\Models\Admin;

class AdminApiAuthService
{
    /**
     * The guard name the login and logout events are fired for.
     */
    public const GUARD = 'admin-api';

    /**
     * The failed logins an email address and client address may make before waiting, as core allows.
     */
    public const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Seconds failed logins are remembered for.
     */
    public const LOGIN_DECAY_SECONDS = 60;

    /**
     * Create a new admin API authentication service.
     */
    public function __construct(
        protected AdminApiTokenService $tokenService,
        protected AdminApiAuthAuditor $auditor,
        protected RateLimiter $limiter,
        protected AuthManager $auth,
    ) {}

    /**
     * Check an admin's email and password and start a session, firing core's login events.
     *
     * @param  array{email: string, password: string, device_name?: ?string, client_type?: ?string}  $input
     *
     * @throws AdminAuthException
     */
    public function login(array $input, Request $request): AdminApiTokenPair
    {
        $credentials = [
            'email' => (string) $input['email'],
            'password' => (string) $input['password'],
        ];

        $throttleKey = $this->throttleKey($credentials['email'], $request);

        if ($this->limiter->tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $this->rejectLockout($throttleKey, $credentials['email'], $request);
        }

        Event::dispatch(new Attempting(self::GUARD, $credentials, false));

        $provider = $this->userProvider();

        $admin = $provider->retrieveByCredentials($credentials);

        if (
            ! $admin instanceof Admin
            || ! $provider->validateCredentials($admin, $credentials)
        ) {
            $this->rejectCredentials($throttleKey, $admin instanceof Admin ? $admin : null, $credentials, $request);
        }

        $this->limiter->clear($throttleKey);

        $this->assertCanLogIn($admin, $request);

        $pair = $this->tokenService->issue($admin, [
            'name' => $input['device_name'] ?? null,
            'client_type' => $input['client_type'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'channel' => AdminAuthHelper::channel($request),
        ]);

        Event::dispatch(new Login(self::GUARD, $admin, false));

        $this->auditor->record(AdminApiAuthAuditor::EVENT_LOGIN, $admin, $pair->session, $request, [
            'client_type' => $pair->session->client_type,
        ]);

        return $pair;
    }

    /**
     * Exchange a refresh token for a new pair, auditing a reused token as theft.
     *
     * @throws InvalidRefreshTokenException
     */
    public function refresh(string $refreshToken, Request $request): AdminApiTokenPair
    {
        try {
            $pair = $this->tokenService->rotate($refreshToken, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'channel' => AdminAuthHelper::channel($request),
            ]);
        } catch (InvalidRefreshTokenException $exception) {
            if ($exception->wasReused()) {
                $session = $exception->session();

                $this->auditor->record(AdminApiAuthAuditor::EVENT_REFRESH_REUSE, $session?->admin, $session, $request);
            }

            throw $exception;
        }

        $this->auditor->record(AdminApiAuthAuditor::EVENT_REFRESH, $pair->session->admin, $pair->session, $request);

        return $pair;
    }

    /**
     * End the session the request was made with.
     */
    public function logout(Admin $admin, AdminApiSession $session, Request $request): AdminApiSession
    {
        $session = $this->tokenService->revoke($session, AdminApiSession::REVOKED_LOGOUT);

        Event::dispatch(new Logout(self::GUARD, $admin));

        $this->auditor->record(AdminApiAuthAuditor::EVENT_LOGOUT, $admin, $session, $request);

        return $session;
    }

    /**
     * End every session of the admin, including the current one, and return how many were ended.
     */
    public function logoutAll(Admin $admin, AdminApiSession $session, Request $request): int
    {
        $revoked = $this->tokenService->revokeAllForAdmin($admin->id, AdminApiSession::REVOKED_LOGOUT_ALL);

        Event::dispatch(new Logout(self::GUARD, $admin));

        $this->auditor->record(AdminApiAuthAuditor::EVENT_LOGOUT_ALL, $admin, $session, $request, [
            'revoked' => $revoked,
        ]);

        return $revoked;
    }

    /**
     * Revoke one session from a sessions list, on behalf of the acting admin.
     */
    public function revokeSession(Admin $actor, AdminApiSession $session, Request $request): AdminApiSession
    {
        $reason = (int) $session->admin_id === (int) $actor->id
            ? AdminApiSession::REVOKED_LOGOUT
            : AdminApiSession::REVOKED_BY_ADMIN;

        $session = $this->tokenService->revoke($session, $reason);

        $this->auditor->record(AdminApiAuthAuditor::EVENT_SESSION_REVOKED, $actor, $session, $request, [
            'session_admin_id' => (int) $session->admin_id,
        ]);

        return $session;
    }

    /**
     * Revoke every live session of another admin and return how many were revoked.
     */
    public function revokeAdminSessions(Admin $actor, Admin $admin, Request $request): int
    {
        $revoked = $this->tokenService->revokeAllForAdmin($admin->id, AdminApiSession::REVOKED_BY_ADMIN);

        $this->auditor->record(AdminApiAuthAuditor::EVENT_SESSIONS_REVOKED, $actor, null, $request, [
            'admin_id' => (int) $admin->id,
            'revoked' => $revoked,
        ]);

        return $revoked;
    }

    /**
     * Refuse a login the throttle has locked out, telling the client when to retry.
     *
     * @throws AdminAuthException
     */
    protected function rejectLockout(string $throttleKey, string $email, Request $request): never
    {
        Event::dispatch(new Lockout($request));

        $this->auditor->record(AdminApiAuthAuditor::EVENT_LOCKOUT, null, null, $request, [
            'email' => $email,
        ]);

        $seconds = max(1, $this->limiter->availableIn($throttleKey));

        throw new AdminAuthException(
            trans('bagistoapi::app.admin.auth.errors.too-many-login-attempts', ['seconds' => $seconds]),
            AdminAuthException::CODE_TOO_MANY_LOGIN_ATTEMPTS,
            429,
            ['Retry-After' => (string) $seconds],
        );
    }

    /**
     * Count a failed login and refuse it with core's message, which never says whether the email exists.
     *
     * @throws AdminAuthException
     */
    protected function rejectCredentials(string $throttleKey, ?Admin $admin, array $credentials, Request $request): never
    {
        $this->limiter->hit($throttleKey, self::LOGIN_DECAY_SECONDS);

        Event::dispatch(new Failed(self::GUARD, $admin, $credentials));

        $this->auditor->record(AdminApiAuthAuditor::EVENT_LOGIN_FAILED, $admin, null, $request, [
            'email' => $credentials['email'],
            'reason' => AdminAuthException::CODE_INVALID_CREDENTIALS,
        ]);

        throw new AdminAuthException(
            trans('admin::app.settings.users.login-error'),
            AdminAuthException::CODE_INVALID_CREDENTIALS,
        );
    }

    /**
     * Refuse an inactive admin with core's message, and an admin who must pass a second factor.
     *
     * @throws AdminAuthException
     */
    protected function assertCanLogIn(Admin $admin, Request $request): void
    {
        if (! $admin->status) {
            $this->auditor->record(AdminApiAuthAuditor::EVENT_LOGIN_FAILED, $admin, null, $request, [
                'email' => $admin->email,
                'reason' => AdminAuthException::CODE_ACCOUNT_INACTIVE,
            ]);

            throw new AdminAuthException(
                trans('admin::app.settings.users.activate-warning'),
                AdminAuthException::CODE_ACCOUNT_INACTIVE,
                403,
            );
        }

        if ($admin->two_factor_enabled) {
            throw new AdminAuthException(
                trans('bagistoapi::app.admin.auth.errors.two-factor-required'),
                AdminAuthException::CODE_TWO_FACTOR_REQUIRED,
                403,
            );
        }
    }

    /**
     * The user provider of the admin API guard, the same `admins` provider core's admin guard uses.
     */
    protected function userProvider(): UserProvider
    {
        return $this->auth->createUserProvider(config('auth.guards.admin-api.provider', 'admins'));
    }

    /**
     * The throttle key of an email address and client address, as core's login throttle builds it.
     */
    protected function throttleKey(string $email, Request $request): string
    {
        return 'bagistoapi-admin-login:'.Str::transliterate(Str::lower($email)).'|'.$request->ip();
    }
}
