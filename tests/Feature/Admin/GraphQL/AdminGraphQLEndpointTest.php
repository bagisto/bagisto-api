<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Contracts\Http\Kernel;
use Webkul\BagistoApi\Http\Middleware\VerifyGraphQLStorefrontKey;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * Regression tests for the dedicated `POST /api/admin/graphql` endpoint.
 *
 * Verifies the admin GraphQL endpoint authenticates via admin Bearer (no
 * storefront key required) and that the shop `/api/graphql` endpoint does
 * NOT accept admin Bearer tokens as a back door.
 */
class AdminGraphQLEndpointTest extends AdminApiTestCase
{
    private const ADMIN_GQL = '/api/admin/graphql';

    private const SHOP_GQL = '/api/graphql';

    private const QUERY = 'query { readAdminProfile { id email } }';

    public function test_admin_graphql_endpoint_accepts_valid_admin_bearer(): void
    {
        $admin = $this->createAdmin();
        $headers = $this->adminHeaders($admin);

        $response = $this->postJson(self::ADMIN_GQL, ['query' => self::QUERY], $headers);

        $response->assertOk();
        expect($response->json('data.readAdminProfile.id'))->toContain((string) $admin->id);
        expect($response->json('data.readAdminProfile.email'))->toBe($admin->email);
    }

    public function test_admin_graphql_endpoint_rejects_request_without_auth(): void
    {
        $response = $this->postJson(self::ADMIN_GQL, ['query' => self::QUERY]);

        $response->assertStatus(401);
    }

    public function test_admin_graphql_endpoint_rejects_invalid_bearer(): void
    {
        $response = $this->postJson(self::ADMIN_GQL, ['query' => self::QUERY], [
            'Authorization' => 'Bearer 99999|not-a-real-token-value-xxxxxxxxxxxxxxxxxxxxx',
        ]);

        $response->assertStatus(401);
    }

    public function test_shop_graphql_endpoint_rejects_admin_bearer_no_back_door(): void
    {
        $admin = $this->createAdmin();
        $headers = $this->adminHeaders($admin);

        $this->app->make(Kernel::class);
        app()->forgetInstance(VerifyGraphQLStorefrontKey::class);

        $response = $this->withMiddleware(VerifyGraphQLStorefrontKey::class)
            ->postJson(self::SHOP_GQL, ['query' => self::QUERY], $headers);

        $isUnauthorized = in_array($response->getStatusCode(), [400, 401, 403], true);
        $hasAdminPayload = ! empty($response->json('data.readAdminProfile.id'));

        expect($isUnauthorized || ! $hasAdminPayload)->toBeTrue(
            'Shop /api/graphql must not return admin data when called with only an admin Bearer.'
        );
    }

    public function test_shop_graphql_endpoint_still_accepts_storefront_key(): void
    {
        $response = $this->postJson(self::SHOP_GQL, ['query' => '{ __typename }'], [
            'X-STOREFRONT-KEY' => 'pk_test_12345',
        ]);

        $response->assertOk();
        expect($response->json('data.__typename'))->toBe('Query');
    }
}
