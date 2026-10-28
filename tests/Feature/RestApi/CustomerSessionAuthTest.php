<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtSigner;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthSettings;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Core\Models\CoreConfig;
use Webkul\Customer\Models\Customer;

class CustomerSessionAuthTest extends RestApiTestCase
{
    /**
     * The customer's password in every test.
     */
    protected const PASSWORD = 'Password123!';

    // ========================================================================
    // Login
    // ========================================================================

    /**
     * Test a login returns a token pair whose access token authenticates customer requests.
     */
    public function test_login_returns_a_token_pair_that_authenticates(): void
    {
        Event::fake(['customer.after.login']);

        $customer = $this->customerWithPassword();

        $response = $this->login($customer->email, ['device_name' => 'iPhone 15', 'client_type' => 'mobile']);

        $response->assertCreated()
            ->assertJsonPath('tokenType', 'Bearer')
            ->assertJsonPath('expiresIn', 900)
            ->assertJsonPath('session.name', 'iPhone 15')
            ->assertJsonPath('session.clientType', 'mobile')
            ->assertJsonPath('customer.id', $customer->id)
            ->assertJsonPath('success', true);

        $this->assertSame($response->json('accessToken'), $response->json('token'));
        $this->assertMatchesRegularExpression('/^\d+\|[0-9a-f]{64}$/', $response->json('refreshToken'));

        $this->profileWith($response->json('accessToken'))->assertOk();

        Event::assertDispatched('customer.after.login');
    }

    /**
     * Test a wrong password and an unknown email get the same 401.
     */
    public function test_wrong_credentials_get_the_same_401(): void
    {
        $customer = $this->customerWithPassword();

        $wrong = $this->login($customer->email, [], 'Wrong-password-1');
        $unknown = $this->login('nobody-'.uniqid().'@example.com');

        $wrong->assertStatus(401)->assertJsonPath('error', 'invalid_credentials');
        $unknown->assertStatus(401)->assertJsonPath('error', 'invalid_credentials');

        $this->assertSame($wrong->json('message'), $unknown->json('message'));
    }

    /**
     * Test an inactive and an unverified customer are refused with their codes.
     */
    public function test_inactive_and_unverified_customers_are_refused(): void
    {
        $inactive = $this->customerWithPassword(['status' => 0]);
        $unverified = $this->customerWithPassword(['is_verified' => 0]);

        $this->login($inactive->email)->assertStatus(403)->assertJsonPath('error', 'account_inactive');
        $this->login($unverified->email)->assertStatus(403)->assertJsonPath('error', 'account_unverified');

        $this->assertSame(0, CustomerApiSession::query()->whereIn('customer_id', [$inactive->id, $unverified->id])->count());
    }

    /**
     * Test a customer of another channel cannot log in on this one.
     */
    public function test_a_customer_of_another_channel_cannot_log_in(): void
    {
        $customer = $this->customerWithPassword(['channel_id' => null]);

        $this->login($customer->email)->assertStatus(401)->assertJsonPath('error', 'invalid_credentials');
    }

    /**
     * Test failed logins lock the email and address out for the configured time.
     */
    public function test_failed_logins_lock_out_with_retry_after(): void
    {
        $this->configure([
            'login_protection.login_max_attempts' => 3,
            'login_protection.login_lockout_seconds' => 120,
        ]);

        $customer = $this->customerWithPassword();

        foreach (range(1, 3) as $attempt) {
            $this->login($customer->email, [], 'Wrong-password-'.$attempt)->assertStatus(401);
        }

        $response = $this->login($customer->email);

        $response->assertStatus(429)->assertJsonPath('error', 'too_many_login_attempts');
        $this->assertGreaterThan(60, (int) $response->headers->get('Retry-After'));
    }

    // ========================================================================
    // Access Tokens
    // ========================================================================

    /**
     * Test an expired access token is answered with access_token_expired.
     */
    public function test_an_expired_access_token_is_refused(): void
    {
        $token = $this->customerAccessToken($this->createCustomer());

        $this->travel(16)->minutes();

        $this->profileWith($token)
            ->assertStatus(401)
            ->assertJsonPath('error', 'access_token_expired');

        $this->getJson('/api/shop/products', $this->bearer($token))
            ->assertStatus(401)
            ->assertJsonPath('error', 'access_token_expired');
    }

    /**
     * Test a revoked session's access token is answered with unauthenticated.
     */
    public function test_a_revoked_sessions_access_token_is_refused_with_its_code(): void
    {
        $pair = $this->customerTokenPair($this->createCustomer());

        $pair->session->forceFill(['revoked_at' => now(), 'revoked_reason' => CustomerApiSession::REVOKED_LOGOUT])->save();

        $this->profileWith($pair->accessToken)
            ->assertStatus(401)
            ->assertJsonPath('error', 'unauthenticated');
    }

    /**
     * Test an expired access token left on the client never blocks logging in again.
     */
    public function test_a_stale_bearer_does_not_block_login(): void
    {
        $customer = $this->customerWithPassword();

        $stale = $this->customerAccessToken($customer);

        $this->travel(16)->minutes();

        $this->postJson('/api/shop/customer/login', [
            'email' => $customer->email,
            'password' => self::PASSWORD,
        ], $this->bearer($stale))->assertSuccessful();
    }

    /**
     * Test a session used on another channel than it was created on is refused.
     */
    public function test_a_session_of_another_channel_is_refused(): void
    {
        $pair = $this->customerTokenPair($this->createCustomer());

        CustomerApiSession::query()->whereKey($pair->session->id)->update(['channel_id' => 999]);

        $this->assertContains($this->profileWith($pair->accessToken)->getStatusCode(), [401, 403]);
    }

    /**
     * Test a customer who signed in on another channel through X-Channel stays signed in there.
     */
    public function test_a_session_started_through_x_channel_authenticates_on_that_channel(): void
    {
        $channel = $this->secondChannel();

        $customer = $this->customerWithPassword(['channel_id' => $channel->id]);

        $login = $this->postJson('/api/shop/customer/login', [
            'email' => $customer->email,
            'password' => self::PASSWORD,
        ], array_merge($this->storefrontHeaders(), ['X-Channel' => $channel->code]));

        $login->assertCreated();

        $this->forgetCurrentChannel();

        $headers = array_merge($this->bearer($login->json('accessToken')), ['X-Channel' => $channel->code]);

        $this->getJson('/api/shop/customer-profile', $headers)->assertOk();

        $this->forgetCurrentChannel();

        $this->assertContains($this->profileWith($login->json('accessToken'))->getStatusCode(), [401, 403]);
    }

    /**
     * Test an admin access token is refused on the storefront and a customer token on the admin API.
     */
    public function test_tokens_are_refused_on_the_other_surface(): void
    {
        $admin = app(JwtSigner::class)->signAccessToken(1, 1, now(), now()->addMinutes(15));

        $this->assertContains($this->profileWith($admin)->getStatusCode(), [401, 403]);

        $customerToken = $this->customerAccessToken($this->createCustomer());

        $this->getJson('/api/admin/get', ['Authorization' => 'Bearer '.$customerToken])->assertStatus(401);
    }

    /**
     * Test the per-customer request limit answers 429 once reached.
     */
    public function test_the_per_customer_request_limit_is_enforced(): void
    {
        $this->configure(['rate_limits.requests_per_minute' => 2]);

        $token = $this->customerAccessToken($this->createCustomer());

        $this->profileWith($token)->assertOk();
        $this->profileWith($token)->assertOk();

        $response = $this->profileWith($token);

        $response->assertStatus(429)->assertJsonPath('error', 'rate_limit_exceeded');
        $this->assertNotNull($response->headers->get('Retry-After'));
    }

    // ========================================================================
    // Refresh
    // ========================================================================

    /**
     * Test a refresh rotates both tokens and a reused refresh token ends the session.
     */
    public function test_refresh_rotates_and_reuse_ends_the_session(): void
    {
        $pair = $this->customerTokenPair($this->createCustomer());

        $response = $this->postJson('/api/shop/customer/refresh', ['refresh_token' => $pair->refreshToken], $this->storefrontHeaders());

        $response->assertOk()->assertJsonPath('session.id', $pair->session->id);

        $this->assertNotSame($pair->refreshToken, $response->json('refreshToken'));
        $this->profileWith($response->json('accessToken'))->assertOk();

        $this->postJson('/api/shop/customer/refresh', ['refresh_token' => $pair->refreshToken], $this->storefrontHeaders())
            ->assertStatus(401)
            ->assertJsonPath('error', 'refresh_token_invalid');

        $this->assertSame(CustomerApiSession::REVOKED_REFRESH_REUSE, $pair->session->fresh()->revoked_reason);
    }

    /**
     * Test token refreshes are limited per session.
     */
    public function test_refreshes_are_limited_per_session(): void
    {
        $this->configure(['login_protection.refresh_requests_per_minute' => 1]);

        $pair = $this->customerTokenPair($this->createCustomer());

        $next = $this->postJson('/api/shop/customer/refresh', ['refresh_token' => $pair->refreshToken], $this->storefrontHeaders());

        $next->assertOk();

        $this->postJson('/api/shop/customer/refresh', ['refresh_token' => $next->json('refreshToken')], $this->storefrontHeaders())
            ->assertStatus(429)
            ->assertJsonPath('error', 'too_many_refresh_requests');
    }

    /**
     * Test a refresh token with a wrong secret cannot use up its session's refresh budget.
     */
    public function test_a_forged_refresh_token_does_not_throttle_the_real_session(): void
    {
        $this->configure(['login_protection.refresh_requests_per_minute' => 1]);

        $pair = $this->customerTokenPair($this->createCustomer());

        $forged = explode('|', $pair->refreshToken, 2)[0].'|'.str_repeat('0', 64);

        $this->postJson('/api/shop/customer/refresh', ['refresh_token' => $forged], $this->storefrontHeaders())
            ->assertStatus(401);

        $this->postJson('/api/shop/customer/refresh', ['refresh_token' => $pair->refreshToken], $this->storefrontHeaders())
            ->assertOk();
    }

    /**
     * Test a refused refresh token is answered with 401 and not written to the error log.
     */
    public function test_a_refused_refresh_token_is_not_logged_as_an_error(): void
    {
        Log::spy();

        $this->postJson('/api/shop/customer/refresh', ['refresh_token' => '999999|'.str_repeat('a', 64)], $this->storefrontHeaders())
            ->assertStatus(401);

        Log::shouldNotHaveReceived('error');
    }

    /**
     * Test a refresh that fails part way leaves the refresh token unused, so a retry still works.
     */
    public function test_a_failed_refresh_can_be_retried_with_the_same_token(): void
    {
        $pair = $this->customerTokenPair($this->createCustomer());

        $fail = true;

        Event::listen('bagistoapi.customer.api-session.refresh.before', function () use (&$fail) {
            if ($fail) {
                throw new \RuntimeException('Simulated failure while issuing the new pair.');
            }
        });

        $this->postJson('/api/shop/customer/refresh', ['refresh_token' => $pair->refreshToken], $this->storefrontHeaders())
            ->assertStatus(500);

        $fail = false;

        $this->postJson('/api/shop/customer/refresh', ['refresh_token' => $pair->refreshToken], $this->storefrontHeaders())
            ->assertOk();

        $this->assertNull($pair->session->fresh()->revoked_at);
    }

    /**
     * Test a refresh without a refresh token is refused.
     */
    public function test_a_refresh_needs_a_refresh_token(): void
    {
        $this->postJson('/api/shop/customer/refresh', [], $this->storefrontHeaders())->assertStatus(422);
    }

    // ========================================================================
    // Sessions
    // ========================================================================

    /**
     * Test logout ends only the current session.
     */
    public function test_logout_ends_only_this_session(): void
    {
        $customer = $this->createCustomer();

        $current = $this->customerTokenPair($customer);
        $other = $this->customerTokenPair($customer);

        $this->postJson('/api/shop/customer/logout', [], $this->bearer($current->accessToken))->assertJsonPath('success', true);

        $this->assertSame(CustomerApiSession::REVOKED_LOGOUT, $current->session->fresh()->revoked_reason);
        $this->assertNull($other->session->fresh()->revoked_at);
    }

    /**
     * Test a logout sent without a JSON body, as a bare curl POST sends it, still ends the session.
     */
    public function test_logout_needs_no_json_body(): void
    {
        $pair = $this->customerTokenPair($this->createCustomer());

        $this->call('POST', '/api/shop/customer/logout', server: $this->serverHeaders($this->bearer($pair->accessToken)))
            ->assertSuccessful()
            ->assertJsonPath('success', true);

        $this->assertSame(CustomerApiSession::REVOKED_LOGOUT, $pair->session->fresh()->revoked_reason);
    }

    /**
     * Test logout-all ends every session.
     */
    public function test_logout_all_ends_every_session(): void
    {
        $customer = $this->createCustomer();

        $current = $this->customerTokenPair($customer);
        $this->customerTokenPair($customer);

        $this->postJson('/api/shop/customer/logout-all', [], $this->bearer($current->accessToken))
            ->assertOk()
            ->assertJsonPath('revoked', 2);

        $this->assertSame(0, CustomerApiSession::query()->where('customer_id', $customer->id)->whereNull('revoked_at')->count());
    }

    /**
     * Test the session list shows only the customer's live sessions and marks the current one.
     */
    public function test_the_session_list_marks_the_current_session(): void
    {
        $customer = $this->createCustomer();

        $current = $this->customerTokenPair($customer);
        $other = $this->customerTokenPair($customer);
        $this->customerTokenPair($this->createCustomer());

        $response = $this->getJson('/api/shop/customer/sessions', $this->bearer($current->accessToken));

        $response->assertOk()->assertJsonCount(2);

        $sessions = collect($response->json())->keyBy('id');

        $this->assertTrue($sessions[$current->session->id]['isCurrent']);
        $this->assertFalse($sessions[$other->session->id]['isCurrent']);
    }

    /**
     * Test a customer can end one of their sessions but not another customer's.
     */
    public function test_a_customer_revokes_only_their_own_sessions(): void
    {
        $customer = $this->createCustomer();

        $current = $this->customerTokenPair($customer);
        $other = $this->customerTokenPair($customer);
        $foreign = $this->customerTokenPair($this->createCustomer());

        $this->deleteJson('/api/shop/customer/sessions/'.$foreign->session->id, [], $this->bearer($current->accessToken))->assertNotFound();
        $this->assertNull($foreign->session->fresh()->revoked_at);

        $this->deleteJson('/api/shop/customer/sessions/'.$other->session->id, [], $this->bearer($current->accessToken))->assertNoContent();
        $this->assertSame(CustomerApiSession::REVOKED_BY_CUSTOMER, $other->session->fresh()->revoked_reason);
    }

    /**
     * Test the session endpoints need a customer access token.
     */
    public function test_the_session_endpoints_need_a_token(): void
    {
        $this->getJson('/api/shop/customer/sessions', $this->storefrontHeaders())->assertStatus(401);
        $this->postJson('/api/shop/customer/logout-all', [], $this->storefrontHeaders())->assertStatus(401);
    }

    // ========================================================================
    // Automatic Revocation
    // ========================================================================

    /**
     * Test a password change ends the other sessions, and a deactivation ends them all.
     */
    public function test_password_change_and_deactivation_end_sessions(): void
    {
        $customer = $this->createCustomer();

        $first = $this->customerTokenPair($customer);

        $customer->forceFill(['password' => Hash::make('Changed-password-1')])->save();

        $this->assertSame(CustomerApiSession::REVOKED_PASSWORD_CHANGE, $first->session->fresh()->revoked_reason);

        $second = $this->customerTokenPair($customer);

        $customer->forceFill(['status' => 0])->save();

        $this->assertSame(CustomerApiSession::REVOKED_DEACTIVATED, $second->session->fresh()->revoked_reason);
    }

    /**
     * Test a password change keeps the sessions when the store is set not to end them.
     */
    public function test_password_change_keeps_sessions_when_configured(): void
    {
        $this->configure(['security.revoke_sessions_on_password_change' => 0]);

        $customer = $this->createCustomer();

        $pair = $this->customerTokenPair($customer);

        $customer->forceFill(['password' => Hash::make('Changed-password-1')])->save();

        $this->assertNull($pair->session->fresh()->revoked_at);
    }

    // ========================================================================
    // Registration
    // ========================================================================

    /**
     * Test a registration on a store without email verification returns a token pair.
     */
    public function test_registration_returns_a_token_pair(): void
    {
        $this->seedRequiredData();

        $email = 'register-'.uniqid().'@example.com';

        $response = $this->postJson('/api/shop/customers', [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'email' => $email,
            'password' => self::PASSWORD,
            'confirmPassword' => self::PASSWORD,
        ], $this->storefrontHeaders());

        $response->assertSuccessful();

        if ($response->json('accessToken') === null) {
            $this->markTestSkipped('Email verification is on, so registration returns no token.');
        }

        $this->assertSame($response->json('accessToken'), $response->json('token'));
        $this->assertNotNull($response->json('refreshToken'));
        $this->profileWith($response->json('accessToken'))->assertOk();
    }

    /**
     * Create a customer whose password is known.
     */
    protected function customerWithPassword(array $attributes = []): Customer
    {
        return $this->createCustomer(array_merge([
            'email' => 'login-'.uniqid().'@example.com',
            'password' => Hash::make(self::PASSWORD),
            'status' => 1,
            'is_verified' => 1,
        ], $attributes));
    }

    /**
     * Send a REST login.
     */
    protected function login(string $email, array $extra = [], string $password = self::PASSWORD): TestResponse
    {
        return $this->postJson('/api/shop/customer/login', array_merge([
            'email' => $email,
            'password' => $password,
        ], $extra), $this->storefrontHeaders());
    }

    /**
     * Read the customer profile with the given bearer token.
     */
    protected function profileWith(string $token): TestResponse
    {
        return $this->getJson('/api/shop/customer-profile', $this->bearer($token));
    }

    /**
     * Request headers as server variables, for a request sent without the JSON helpers.
     */
    protected function serverHeaders(array $headers): array
    {
        $server = ['HTTP_ACCEPT' => 'application/json'];

        foreach ($headers as $name => $value) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return $server;
    }

    /**
     * The storefront headers with a bearer token.
     */
    protected function bearer(string $token): array
    {
        return array_merge($this->storefrontHeaders(), ['Authorization' => 'Bearer '.$token]);
    }

    /**
     * Forget the channel the previous request resolved, as a new request on a real server starts without one.
     */
    protected function forgetCurrentChannel(): void
    {
        (fn () => $this->currentChannel = null)->call(core());
    }

    /**
     * A second channel that shares the default channel's locales and currencies.
     */
    protected function secondChannel(): Channel
    {
        $default = core()->getDefaultChannel();

        $channel = Channel::factory()->create([
            'code' => 'second'.uniqid(),
            'root_category_id' => $default->root_category_id,
            'default_locale_id' => $default->default_locale_id,
            'base_currency_id' => $default->base_currency_id,
        ]);

        $channel->locales()->sync($default->locales->pluck('id'));
        $channel->currencies()->sync($default->currencies->pluck('id'));

        return $channel;
    }

    /**
     * Save customer authentication settings, keyed below `api.customer_auth`.
     *
     * @param  array<string, int|string>  $values
     */
    protected function configure(array $values): void
    {
        foreach ($values as $field => $value) {
            $code = CustomerApiAuthSettings::CONFIG_PREFIX.'.'.$field;

            CoreConfig::query()->where('code', $code)->delete();

            CoreConfig::query()->create([
                'code' => $code,
                'value' => (string) $value,
            ]);
        }

        $this->forgetCoreConfigCache();
    }
}
