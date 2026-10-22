<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\CoreConfig;

class RateLimitTest extends AdminApiTestCase
{
    /**
     * Test the per-admin limit is enforced for a login session.
     */
    public function test_per_admin_limit_is_enforced(): void
    {
        $this->limitSessionRequestsPerMinute(3);

        $token = $this->adminAccessToken($this->createAdmin());

        for ($i = 0; $i < 3; $i++) {
            $this->getWithToken($token)->assertOk();
        }

        $response = $this->getWithToken($token);

        $response->assertStatus(429);
        $response->assertJson(['error' => 'rate_limit_exceeded']);
        $this->assertNotNull($response->headers->get('Retry-After'));
    }

    /**
     * Test two sessions of the same admin share one bucket.
     */
    public function test_sessions_of_one_admin_share_a_bucket(): void
    {
        $this->limitSessionRequestsPerMinute(2);

        $admin = $this->createAdmin();
        $first = $this->adminAccessToken($admin);
        $second = $this->adminAccessToken($admin);

        $this->getWithToken($first)->assertOk();
        $this->getWithToken($second)->assertOk();

        $this->getWithToken($first)->assertStatus(429);
    }

    /**
     * Test each admin gets their own bucket.
     */
    public function test_each_admin_gets_their_own_bucket(): void
    {
        $this->limitSessionRequestsPerMinute(2);

        $first = $this->adminAccessToken($this->createAdmin());
        $second = $this->adminAccessToken($this->createAdmin());

        $this->getWithToken($first)->assertOk();
        $this->getWithToken($first)->assertOk();
        $this->getWithToken($first)->assertStatus(429);

        $this->getWithToken($second)->assertOk();
    }

    /**
     * Test shop routes are not throttled by the admin limiter.
     */
    public function test_shop_routes_are_not_throttled_by_the_admin_limiter(): void
    {
        $this->limitSessionRequestsPerMinute(2);

        $token = $this->adminAccessToken($this->createAdmin());

        $this->getWithToken($token)->assertOk();
        $this->getWithToken($token)->assertOk();
        $this->getWithToken($token)->assertStatus(429);

        $this->get('/api/shop/channels', ['Accept' => 'application/json'])->assertOk();
    }

    /**
     * Set the configured number of requests per minute for each signed-in admin.
     */
    protected function limitSessionRequestsPerMinute(int $limit): void
    {
        $code = 'api.admin_auth.rate_limits.session_requests_per_minute';

        CoreConfig::query()->where('code', $code)->delete();

        CoreConfig::query()->create([
            'code' => $code,
            'value' => (string) $limit,
        ]);

        $this->forgetCoreConfigCache();
    }

    /**
     * Send an admin GET request with the given Bearer token.
     */
    protected function getWithToken(string $token, string $uri = '/api/admin/get')
    {
        return $this->get($uri, [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ]);
    }
}
