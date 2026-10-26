<?php

namespace Webkul\BagistoApi\Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtVerifier;
use Webkul\BagistoApi\Models\CustomerApiRefreshToken;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthSettings;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiTokenService;
use Webkul\BagistoApi\Services\CustomerAuth\InvalidCustomerRefreshTokenException;
use Webkul\BagistoApi\Tests\BagistoApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\CraftsAdminJwts;
use Webkul\Core\Models\CoreConfig;
use Webkul\Customer\Models\Customer;

class CustomerApiTokenServiceTest extends BagistoApiTestCase
{
    use CraftsAdminJwts;

    // ========================================================================
    // Issuing
    // ========================================================================

    /**
     * Test a login starts a session on the channel and returns a verifiable token pair.
     */
    public function test_issue_starts_a_session_and_returns_a_token_pair(): void
    {
        Event::fake(['bagistoapi.customer.api-session.create.before', 'bagistoapi.customer.api-session.create.after']);

        $customer = $this->createCustomer();

        $pair = $this->service()->issue($customer, $this->channelId(), [
            'name' => 'iPhone',
            'client_type' => 'mobile',
            'ip_address' => '203.0.113.5',
            'user_agent' => 'Shop/1.0',
            'channel' => 'graphql',
        ]);

        $session = $pair->session;

        $this->assertSame($customer->id, $session->customer_id);
        $this->assertSame($this->channelId(), $session->channel_id);
        $this->assertSame('mobile', $session->client_type);
        $this->assertSame('graphql', $session->last_channel);
        $this->assertSame(900, $pair->expiresIn());
        $this->assertMatchesRegularExpression('/^\d+\|[0-9a-f]{64}$/', $pair->refreshToken);

        $claims = app(JwtVerifier::class)->verifyCustomerAccessToken($pair->accessToken);

        $this->assertSame($customer->id, $claims['sub']);
        $this->assertSame($session->id, $claims['sid']);

        $this->assertSame(
            hash('sha256', explode('|', $pair->refreshToken)[1]),
            CustomerApiRefreshToken::query()->whereKey($pair->refreshTokenId)->value('token'),
        );

        Event::assertDispatched('bagistoapi.customer.api-session.create.before');
        Event::assertDispatched('bagistoapi.customer.api-session.create.after');
    }

    /**
     * Test an unknown client type is stored as other.
     */
    public function test_an_unknown_client_type_is_stored_as_other(): void
    {
        $pair = $this->service()->issue($this->createCustomer(), $this->channelId(), ['client_type' => 'toaster']);

        $this->assertSame('other', $pair->session->client_type);
    }

    /**
     * Test the configured lifetimes set the token and session expiries.
     */
    public function test_the_configured_lifetimes_are_used(): void
    {
        $this->configure([
            'tokens.access_token_ttl' => 30,
            'tokens.refresh_token_ttl' => 7,
            'tokens.session_lifetime' => 20,
        ]);

        $this->freezeSecond();

        $pair = $this->service()->issue($this->createCustomer(), $this->channelId());

        $this->assertSame(1800, $pair->expiresIn());
        $this->assertSame(now()->addDays(7)->timestamp, $pair->refreshTokenExpiresAt->timestamp);
        $this->assertSame(now()->addDays(20)->timestamp, $pair->session->expires_at->timestamp);
    }

    /**
     * Test the oldest sessions are ended once a customer holds more than the configured limit.
     */
    public function test_the_session_limit_ends_the_oldest_sessions(): void
    {
        $this->configure(['tokens.max_sessions' => 2]);

        $customer = $this->createCustomer();

        $first = $this->service()->issue($customer, $this->channelId())->session;
        $second = $this->service()->issue($customer, $this->channelId())->session;
        $third = $this->service()->issue($customer, $this->channelId())->session;

        $this->assertSame(CustomerApiSession::REVOKED_SESSION_LIMIT, $first->fresh()->revoked_reason);
        $this->assertNull($second->fresh()->revoked_at);
        $this->assertNull($third->fresh()->revoked_at);
    }

    /**
     * Test no session is ended when the limit is 0.
     */
    public function test_a_session_limit_of_zero_ends_nothing(): void
    {
        $customer = $this->createCustomer();

        foreach (range(1, 3) as $attempt) {
            $this->service()->issue($customer, $this->channelId());
        }

        $this->assertSame(0, CustomerApiSession::query()->where('customer_id', $customer->id)->whereNotNull('revoked_at')->count());
    }

    // ========================================================================
    // Refresh
    // ========================================================================

    /**
     * Test a refresh rotates both tokens inside the same session.
     */
    public function test_a_refresh_rotates_both_tokens(): void
    {
        $pair = $this->service()->issue($this->createCustomer(), $this->channelId());

        $next = $this->service()->rotate($pair->refreshToken, ['channel' => 'rest']);

        $this->assertSame($pair->session->id, $next->session->id);
        $this->assertNotSame($pair->refreshToken, $next->refreshToken);
        $this->assertNotSame($pair->accessToken, $next->accessToken);
        $this->assertSame('rest', $next->session->last_channel);

        $used = CustomerApiRefreshToken::query()->find($pair->refreshTokenId);

        $this->assertNotNull($used->used_at);
        $this->assertSame($next->refreshTokenId, (int) $used->replaced_by_id);
    }

    /**
     * Test a refresh token presented twice revokes the whole session.
     */
    public function test_a_reused_refresh_token_revokes_the_session(): void
    {
        $pair = $this->service()->issue($this->createCustomer(), $this->channelId());

        $next = $this->service()->rotate($pair->refreshToken);

        try {
            $this->service()->rotate($pair->refreshToken);

            $this->fail('A reused refresh token was accepted.');
        } catch (InvalidCustomerRefreshTokenException $exception) {
            $this->assertTrue($exception->wasReused());
            $this->assertSame('refresh_token_invalid', $exception->errorCode());
        }

        $this->assertSame(CustomerApiSession::REVOKED_REFRESH_REUSE, $pair->session->fresh()->revoked_reason);

        $this->assertInvalidRefresh($next->refreshToken);
    }

    /**
     * Test malformed, unknown and tampered refresh tokens are refused.
     */
    public function test_malformed_and_unknown_refresh_tokens_are_refused(): void
    {
        $pair = $this->service()->issue($this->createCustomer(), $this->channelId());

        [$id] = explode('|', $pair->refreshToken);

        foreach (['garbage', '12|', 'x|abc', '999999999|'.str_repeat('a', 64), $id.'|'.str_repeat('b', 64)] as $value) {
            $this->assertInvalidRefresh($value);
        }
    }

    /**
     * Test a refresh is refused once the session is revoked, idle past its refresh lifetime or past its absolute lifetime.
     */
    public function test_a_refresh_is_refused_for_an_ended_session(): void
    {
        $revoked = $this->service()->issue($this->createCustomer(), $this->channelId());
        $this->service()->revoke($revoked->session, CustomerApiSession::REVOKED_LOGOUT);
        $this->assertInvalidRefresh($revoked->refreshToken);

        $idle = $this->service()->issue($this->createCustomer(), $this->channelId());
        $this->travel(31)->days();
        $this->assertInvalidRefresh($idle->refreshToken);
    }

    /**
     * Test a refresh is refused once the customer is deactivated.
     */
    public function test_a_refresh_is_refused_for_an_inactive_customer(): void
    {
        $customer = $this->createCustomer();

        $pair = $this->service()->issue($customer, $this->channelId());

        Customer::query()->whereKey($customer->id)->update(['status' => 0]);

        $this->assertInvalidRefresh($pair->refreshToken);
        $this->assertFalse($this->service()->isSessionUsable($pair->session->fresh()));
    }

    // ========================================================================
    // Revocation
    // ========================================================================

    /**
     * Test revoking every session keeps the one asked for.
     */
    public function test_revoke_all_keeps_the_given_session(): void
    {
        $customer = $this->createCustomer();

        $kept = $this->service()->issue($customer, $this->channelId())->session;
        $other = $this->service()->issue($customer, $this->channelId())->session;

        $this->assertSame(1, $this->service()->revokeAllForCustomer($customer->id, CustomerApiSession::REVOKED_PASSWORD_CHANGE, $kept->id));

        $this->assertNull($kept->fresh()->revoked_at);
        $this->assertSame(CustomerApiSession::REVOKED_PASSWORD_CHANGE, $other->fresh()->revoked_reason);
    }

    /**
     * Test deleting a customer deletes their sessions and refresh tokens.
     */
    public function test_deleting_a_customer_deletes_their_sessions(): void
    {
        $customer = $this->createCustomer();

        $pair = $this->service()->issue($customer, $this->channelId());

        $this->setForeignKeyChecks(true);

        $customer->delete();

        $this->assertDatabaseMissing('customer_api_sessions', ['id' => $pair->session->id]);
        $this->assertDatabaseMissing('customer_api_refresh_tokens', ['id' => $pair->refreshTokenId]);
    }

    // ========================================================================
    // Pruning
    // ========================================================================

    /**
     * Test the prune deletes expired refresh tokens and sessions ended before the retention.
     */
    public function test_prune_deletes_expired_tokens_and_old_sessions(): void
    {
        $this->configure(['retention.session_prune_days' => 5]);

        $customer = $this->createCustomer();

        $old = $this->service()->issue($customer, $this->channelId());
        $this->service()->revoke($old->session, CustomerApiSession::REVOKED_LOGOUT);

        $this->travel(6)->days();

        $live = $this->service()->issue($customer, $this->channelId());

        $this->artisan('bagisto-api:prune-customer-tokens')->assertSuccessful();

        $this->assertDatabaseMissing('customer_api_sessions', ['id' => $old->session->id]);
        $this->assertDatabaseHas('customer_api_sessions', ['id' => $live->session->id]);
        $this->assertDatabaseHas('customer_api_refresh_tokens', ['id' => $live->refreshTokenId]);
    }

    /**
     * Test the customer token prune is scheduled hourly.
     */
    public function test_the_prune_is_scheduled(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn ($event) => str_contains((string) $event->command, 'bagisto-api:prune-customer-tokens'));

        $this->assertCount(1, $events);
        $this->assertSame('0 * * * *', $events->first()->expression);
    }

    // ========================================================================
    // Settings
    // ========================================================================

    /**
     * Test every setting has its default, and a value outside its range is clamped.
     */
    public function test_settings_default_and_clamp(): void
    {
        $settings = app(CustomerApiAuthSettings::class);

        $this->assertSame(15, $settings->accessTokenTtl());
        $this->assertSame(30, $settings->refreshTokenTtl());
        $this->assertSame(90, $settings->sessionLifetime());
        $this->assertSame(0, $settings->maxSessions());
        $this->assertSame(5, $settings->loginMaxAttempts());
        $this->assertSame(60, $settings->loginLockoutSeconds());
        $this->assertSame(10, $settings->refreshRequestsPerMinute());
        $this->assertSame(120, $settings->requestsPerMinute());
        $this->assertTrue($settings->revokeSessionsOnPasswordChange());
        $this->assertSame(30, $settings->pruneAfterDays());

        $this->configure([
            'tokens.access_token_ttl' => 999,
            'tokens.max_sessions' => 500,
            'login_protection.login_max_attempts' => 1,
            'login_protection.refresh_requests_per_minute' => 0,
            'rate_limits.requests_per_minute' => 900000,
            'security.revoke_sessions_on_password_change' => 0,
        ]);

        $this->assertSame(60, $settings->accessTokenTtl());
        $this->assertSame(100, $settings->maxSessions());
        $this->assertSame(3, $settings->loginMaxAttempts());
        $this->assertSame(1, $settings->refreshRequestsPerMinute());
        $this->assertSame(100000, $settings->requestsPerMinute());
        $this->assertFalse($settings->revokeSessionsOnPasswordChange());
    }

    /**
     * Sign with the test key pair under a fixed issuer.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://store.test']);

        $this->useFixtureJwtKeys();
    }

    /**
     * The customer token service.
     */
    protected function service(): CustomerApiTokenService
    {
        return app(CustomerApiTokenService::class);
    }

    /**
     * The id of the default channel.
     */
    protected function channelId(): int
    {
        return (int) core()->getDefaultChannel()->id;
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

    /**
     * Assert a refresh token is refused as invalid.
     */
    protected function assertInvalidRefresh(string $refreshToken): void
    {
        try {
            $this->service()->rotate($refreshToken);
        } catch (InvalidCustomerRefreshTokenException $exception) {
            $this->assertSame('refresh_token_invalid', $exception->errorCode());
            $this->assertSame(401, $exception->getStatusCode());

            return;
        }

        $this->fail('The refresh token was accepted.');
    }
}
