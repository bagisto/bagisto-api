<?php

namespace Webkul\BagistoApi\Services\CustomerAuth;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuthAuditor;
use Webkul\BagistoApi\Auth\CustomerApiAuthenticator;
use Webkul\BagistoApi\Exception\CustomerAuthException;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\BagistoApi\Repositories\CustomerApiRefreshTokenRepository;
use Webkul\BagistoApi\Services\Cart\GuestCartMerger;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\User\Models\Admin;

class CustomerApiAuthService
{
    /**
     * Create a new customer authentication service.
     */
    public function __construct(
        protected CustomerApiTokenService $tokenService,
        protected CustomerApiAuthSettings $settings,
        protected CustomerRepository $customerRepository,
        protected CustomerApiRefreshTokenRepository $refreshTokenRepository,
        protected RateLimiter $limiter,
        protected AdminApiAuthAuditor $auditor,
        protected GuestCartMerger $guestCartMerger,
    ) {}

    /**
     * Check a customer's email and password on the current channel, as core's storefront login does, and start a session.
     *
     * A guest cart token, when it names a guest cart that can still be merged, merges that cart into the customer's
     * cart in the same transaction as the session; any other token is ignored, so a stale cart never blocks a login.
     *
     * @param  array{name?: ?string, client_type?: ?string}  $client
     *
     * @throws CustomerAuthException
     */
    public function login(string $email, string $password, Request $request, array $client = [], ?string $guestCartToken = null): CustomerApiTokenPair
    {
        $throttleKey = 'bagistoapi-customer-login:'.Str::lower($email).'|'.$request->ip();

        if ($this->limiter->tooManyAttempts($throttleKey, $this->settings->loginMaxAttempts())) {
            $this->auditor->recordForCustomer(AdminApiAuthAuditor::EVENT_LOCKOUT, null, null, $request, ['email' => $email]);

            $this->rejectThrottled('too-many-login-attempts', CustomerAuthException::CODE_TOO_MANY_LOGIN_ATTEMPTS, $throttleKey);
        }

        $customer = $this->customerRepository->findOneWhere([
            'email' => $email,
            'channel_id' => core()->getCurrentChannel()->id,
        ]);

        if (
            ! $customer
            || ! Hash::check($password, (string) $customer->password)
        ) {
            $this->limiter->hit($throttleKey, $this->settings->loginLockoutSeconds());

            $this->auditor->recordForCustomer(AdminApiAuthAuditor::EVENT_LOGIN_FAILED, $customer, null, $request, ['email' => $email]);

            throw new CustomerAuthException(
                trans('bagistoapi::app.graphql.login.invalid-credentials'),
                CustomerAuthException::CODE_INVALID_CREDENTIALS,
            );
        }

        $this->limiter->clear($throttleKey);

        $this->assertCanLogIn($customer, $request);

        $pair = DB::transaction(function () use ($customer, $request, $client, $guestCartToken) {
            $pair = $this->startSession($customer, $request, $client);

            $guestCart = $this->guestCartMerger->findMergeable($guestCartToken);

            if ($guestCart) {
                $pair->mergedCartId = (int) $this->guestCartMerger->merge($guestCart, $customer)->id;
            }

            return $pair;
        });

        $this->auditor->recordForCustomer(AdminApiAuthAuditor::EVENT_LOGIN, $customer, $pair->session, $request, [
            'client_type' => $pair->session->client_type,
            'merged_cart_id' => $pair->mergedCartId,
        ]);

        Event::dispatch('customer.after.login', $customer);

        return $pair;
    }

    /**
     * Start a session for a customer who proved who they are another way, such as registering or a social login.
     *
     * @param  array{name?: ?string, client_type?: ?string}  $client
     */
    public function startSession(Customer $customer, Request $request, array $client = []): CustomerApiTokenPair
    {
        return $this->tokenService->issue($customer, (int) core()->getCurrentChannel()->id, [
            'name' => $client['name'] ?? null,
            'client_type' => $client['client_type'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'channel' => CustomerApiAuthenticator::channel($request),
        ]);
    }

    /**
     * Exchange a refresh token for a new pair, limited per session.
     *
     * @throws CustomerAuthException
     */
    public function refresh(string $refreshToken, Request $request): CustomerApiTokenPair
    {
        $throttleKey = 'bagistoapi-customer-refresh:'.$this->refreshThrottleScope($refreshToken, $request);

        if ($this->limiter->tooManyAttempts($throttleKey, $this->settings->refreshRequestsPerMinute())) {
            $this->rejectThrottled('too-many-refresh-requests', CustomerAuthException::CODE_TOO_MANY_REFRESH_REQUESTS, $throttleKey);
        }

        $this->limiter->hit($throttleKey, 60);

        try {
            $pair = $this->tokenService->rotate($refreshToken, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'channel' => CustomerApiAuthenticator::channel($request),
            ]);
        } catch (InvalidCustomerRefreshTokenException $exception) {
            if ($exception->wasReused()) {
                $session = $exception->session();

                $this->auditor->recordForCustomer(AdminApiAuthAuditor::EVENT_REFRESH_REUSE, $session?->customer, $session, $request);
            }

            throw $exception;
        }

        $this->auditor->recordForCustomer(AdminApiAuthAuditor::EVENT_REFRESH, $pair->session->customer, $pair->session, $request);

        return $pair;
    }

    /**
     * End the session the request authenticated with.
     */
    public function logout(CustomerApiSession $session): CustomerApiSession
    {
        $session = $this->tokenService->revoke($session, CustomerApiSession::REVOKED_LOGOUT);

        $this->auditor->recordForCustomer(AdminApiAuthAuditor::EVENT_LOGOUT, $session->customer, $session, request());

        return $session;
    }

    /**
     * End every session of a customer and return how many were ended.
     */
    public function logoutAll(Customer $customer): int
    {
        $revoked = $this->tokenService->revokeAllForCustomer((int) $customer->id, CustomerApiSession::REVOKED_LOGOUT_ALL);

        $this->auditor->recordForCustomer(AdminApiAuthAuditor::EVENT_LOGOUT_ALL, $customer, null, request(), ['revoked' => $revoked]);

        return $revoked;
    }

    /**
     * End one of the customer's own sessions.
     */
    public function revokeSession(CustomerApiSession $session): CustomerApiSession
    {
        $session = $this->tokenService->revoke($session, CustomerApiSession::REVOKED_BY_CUSTOMER);

        $this->auditor->recordForCustomer(AdminApiAuthAuditor::EVENT_SESSION_REVOKED, $session->customer, $session, request());

        return $session;
    }

    /**
     * End one customer session on an admin's behalf.
     */
    public function revokeSessionAsAdmin(Admin $admin, CustomerApiSession $session, Request $request): CustomerApiSession
    {
        $session = $this->tokenService->revoke($session, CustomerApiSession::REVOKED_BY_ADMIN);

        $this->auditor->recordForCustomer(AdminApiAuthAuditor::EVENT_SESSION_REVOKED, $session->customer, $session, $request, [], $admin);

        return $session;
    }

    /**
     * End every session of a customer on an admin's behalf and return how many were ended.
     */
    public function revokeAllAsAdmin(Admin $admin, Customer $customer, Request $request): int
    {
        $revoked = $this->tokenService->revokeAllForCustomer((int) $customer->id, CustomerApiSession::REVOKED_BY_ADMIN);

        $this->auditor->recordForCustomer(AdminApiAuthAuditor::EVENT_SESSIONS_REVOKED, $customer, null, $request, ['revoked' => $revoked], $admin);

        return $revoked;
    }

    /**
     * End every session of a customer for a reason the system decided, and audit it.
     */
    public function revokeAutomatically(Customer $customer, string $reason, ?int $exceptSessionId = null): int
    {
        $revoked = $this->tokenService->revokeAllForCustomer((int) $customer->id, $reason, $exceptSessionId);

        if ($revoked > 0) {
            $this->auditor->recordForCustomer(AdminApiAuthAuditor::EVENT_CREDENTIALS_AUTO_REVOKED, $customer, null, request(), [
                'reason' => $reason,
                'revoked' => $revoked,
            ]);
        }

        return $revoked;
    }

    /**
     * End the customer's other sessions after a password change, when the store is set to.
     */
    public function passwordChanged(Customer $customer, ?CustomerApiSession $current): int
    {
        if (! $this->settings->revokeSessionsOnPasswordChange()) {
            return 0;
        }

        return $this->revokeAutomatically($customer, CustomerApiSession::REVOKED_PASSWORD_CHANGE, $current?->id);
    }

    /**
     * End every session of a customer whose password was reset.
     */
    public function passwordReset(Customer $customer): int
    {
        return $this->revokeAutomatically($customer, CustomerApiSession::REVOKED_PASSWORD_RESET);
    }

    /**
     * Refuse a customer core's storefront would not log in: inactive, or not yet verified.
     *
     * @throws CustomerAuthException
     */
    protected function assertCanLogIn(Customer $customer, Request $request): void
    {
        if (
            ! $customer->status
            || ! $customer->is_verified
        ) {
            $this->auditor->recordForCustomer(AdminApiAuthAuditor::EVENT_LOGIN_FAILED, $customer, null, $request, [
                'reason' => ! $customer->status ? 'inactive' : 'unverified',
            ]);
        }

        if (! $customer->status) {
            throw new CustomerAuthException(
                trans('bagistoapi::app.graphql.login.account-inactive'),
                CustomerAuthException::CODE_ACCOUNT_INACTIVE,
                403,
            );
        }

        if (! $customer->is_verified) {
            throw new CustomerAuthException(
                trans('bagistoapi::app.graphql.login.email-not-verified'),
                CustomerAuthException::CODE_ACCOUNT_UNVERIFIED,
                403,
            );
        }
    }

    /**
     * The session a refresh token belongs to, or the client address when the token names none.
     */
    protected function refreshThrottleScope(string $refreshToken, Request $request): string
    {
        $id = explode('|', $refreshToken, 2)[0];

        $sessionId = ctype_digit($id)
            ? $this->refreshTokenRepository->find((int) $id)?->session_id
            : null;

        return $sessionId !== null
            ? 'session:'.$sessionId
            : 'address:'.$request->ip();
    }

    /**
     * Refuse a throttled request with the seconds to wait.
     *
     * @throws CustomerAuthException
     */
    protected function rejectThrottled(string $message, string $code, string $throttleKey): never
    {
        $seconds = max(1, $this->limiter->availableIn($throttleKey));

        throw new CustomerAuthException(
            trans('bagistoapi::app.graphql.auth.'.$message, ['seconds' => $seconds]),
            $code,
            429,
            ['Retry-After' => (string) $seconds],
        );
    }
}
