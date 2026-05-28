<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;

/**
 * REST coverage for Wave 3 — fresh draft-cart bootstrap.
 *
 * POST /api/admin/customers/{customerId}/draft-carts
 *
 * Distinct from `POST /api/admin/orders/{id}/reorder` (which seeds the cart
 * from an existing order's items) — this endpoint creates an empty draft cart
 * for a Create-Order session starting from scratch.
 */
class DraftCartTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    public function test_create_draft_cart_requires_auth(): void
    {
        $resp = $this->postJson('/api/admin/customers/1/draft-carts', []);
        expect($resp->getStatusCode())->toBeIn([401, 403]);
    }

    public function test_create_draft_cart_returns_404_for_unknown_customer(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers/999999999/draft-carts', []);
        $resp->assertStatus(404);
    }

    public function test_create_draft_cart_bootstraps_for_customer(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->findOrCreateCustomer();

        $resp = $this->adminPost($admin, '/api/admin/customers/'.$customer->id.'/draft-carts', []);
        $resp->assertStatus(201);

        expect($resp->json('success'))->toBeTrue();
        expect($resp->json('customerId'))->toBe($customer->id);
        expect($resp->json('cartId'))->toBeInt();
        expect($resp->json('cartId'))->toBeGreaterThan(0);

        $cartId = $resp->json('cartId');
        $cartResp = $this->adminGet($admin, '/api/admin/carts/'.$cartId);
        $cartResp->assertOk();
        expect($cartResp->json('isActive'))->toBeFalse();
        expect($cartResp->json('customerId'))->toBe($customer->id);
    }

    public function test_create_draft_cart_with_zero_customer_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers/0/draft-carts', []);
        expect($resp->getStatusCode())->toBeIn([400, 404]);
    }

    public function test_fresh_draft_cart_then_use_cart_keyed_endpoints(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->findOrCreateCustomer();

        $bootstrap = $this->adminPost($admin, '/api/admin/customers/'.$customer->id.'/draft-carts', []);
        $bootstrap->assertStatus(201);
        $cartId = $bootstrap->json('cartId');

        $product = $this->findOrCreateSimpleProduct();

        $addResp = $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/items', [
            'productId' => $product->id,
            'quantity'  => 1,
        ]);

        expect($addResp->getStatusCode())->toBeIn([200, 201]);
    }
}
