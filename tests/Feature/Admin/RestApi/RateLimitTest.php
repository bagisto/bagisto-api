<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class RateLimitTest extends AdminApiTestCase
{
    protected function cappedToken(?int $perMinute = null, ?int $perDay = null): string
    {
        $admin = $this->createAdmin();
        $plain = Str::random(40);

        $row = AdminPersonalAccessToken::create([
            'admin_id' => $admin->id,
            'name' => 'rate-limit-test-'.Str::random(6),
            'token' => hash('sha256', $plain),
            'token_preview' => substr($plain, 0, 8),
            'permission_type' => AdminPersonalAccessToken::PERMISSION_TYPE_ALL,
            'abilities' => [],
            'rate_limit_per_minute' => $perMinute,
            'rate_limit_per_day' => $perDay,
            'expires_at' => now()->addDay(),
            'status' => AdminPersonalAccessToken::STATUS_ACTIVE,
            'created_by_admin_id' => $admin->id,
        ]);

        return $row->id.'|'.$plain;
    }

    protected function getWithToken(string $token, string $uri = '/api/admin/get')
    {
        return $this->get($uri, [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ]);
    }

    public function test_per_minute_cap_is_enforced(): void
    {
        $token = $this->cappedToken(perMinute: 3);

        for ($i = 0; $i < 3; $i++) {
            $this->getWithToken($token)->assertOk();
        }

        $response = $this->getWithToken($token);

        $response->assertStatus(429);
        $response->assertJson(['error' => 'rate_limit_exceeded']);
        $this->assertNotNull($response->headers->get('Retry-After'));
    }

    public function test_per_day_cap_is_enforced(): void
    {
        $token = $this->cappedToken(perDay: 2);

        $this->getWithToken($token)->assertOk();
        $this->getWithToken($token)->assertOk();

        $this->getWithToken($token)->assertStatus(429);
    }

    public function test_token_without_caps_is_not_throttled(): void
    {
        $token = $this->cappedToken();

        for ($i = 0; $i < 12; $i++) {
            $this->getWithToken($token)->assertOk();
        }
    }

    public function test_each_token_gets_its_own_bucket(): void
    {
        $first = $this->cappedToken(perMinute: 2);
        $second = $this->cappedToken(perMinute: 2);

        $this->getWithToken($first)->assertOk();
        $this->getWithToken($first)->assertOk();
        $this->getWithToken($first)->assertStatus(429);

        $this->getWithToken($second)->assertOk();
    }

    public function test_shop_routes_are_not_throttled_by_the_admin_limiter(): void
    {
        $token = $this->cappedToken(perMinute: 2);

        $this->getWithToken($token)->assertOk();
        $this->getWithToken($token)->assertOk();
        $this->getWithToken($token)->assertStatus(429);

        $this->get('/api/shop/channels', ['Accept' => 'application/json'])->assertOk();
    }
}
