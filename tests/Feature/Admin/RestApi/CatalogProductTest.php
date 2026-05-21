<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class CatalogProductTest extends AdminApiTestCase
{
    public function test_listing_returns_envelope_for_authenticated_admin(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/products');

        $response->assertOk();
        $body = $response->json();
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('meta', $body);
        $this->assertIsArray($body['data']);
        $this->assertSame(1, $body['meta']['currentPage']);
        $this->assertSame(10, $body['meta']['perPage']);
    }

    public function test_listing_requires_admin_token(): void
    {
        $response = $this->getJson('/api/admin/catalog/products');
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_listing_rejects_revoked_token(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        \Laravel\Sanctum\PersonalAccessToken::findToken($token)->delete();

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->getJson('/api/admin/catalog/products');
        $this->assertSame(401, $response->getStatusCode());
    }
}
