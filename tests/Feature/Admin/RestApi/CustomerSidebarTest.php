<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Customer\Models\Customer;

/**
 * REST coverage for the Create-Order screen's three right-sidebar panels:
 * the customer's own active cart, wishlist, and recent order items.
 */
class CustomerSidebarTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    protected function aCustomerWithActiveCart(): int
    {
        return $this->bootstrapCustomerWithActiveCart();
    }

    protected function aCustomerWithWishlist(): int
    {
        return $this->bootstrapCustomerWithWishlist();
    }

    protected function aCustomerWithOrders(): int
    {
        return $this->bootstrapCustomerWithOrders();
    }

    public function test_cart_items_requires_authentication(): void
    {
        $this->publicGet('/api/admin/customers/1/cart-items')->assertStatus(401);
    }

    public function test_cart_items_404_for_unknown_customer(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/customers/999999999/cart-items')->assertStatus(404);
    }

    public function test_cart_items_returns_envelope(): void
    {
        $customerId = $this->aCustomerWithActiveCart();

        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/customers/'.$customerId.'/cart-items');

        $response->assertOk();
        expect($response->json())->toHaveKeys(['data', 'meta']);
        expect($response->json('data'))->toBeArray()->not->toBeEmpty();
        expect($response->json('data.0'))->toHaveKeys(['id', 'productId', 'sku', 'type', 'name', 'quantity', 'price']);
    }

    public function test_wishlist_requires_authentication(): void
    {
        $this->publicGet('/api/admin/customers/1/wishlist-items')->assertStatus(401);
    }

    public function test_wishlist_returns_envelope(): void
    {
        $customerId = $this->aCustomerWithWishlist();

        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/customers/'.$customerId.'/wishlist-items');

        $response->assertOk();
        expect($response->json())->toHaveKeys(['data', 'meta']);
        expect($response->json('data'))->toBeArray()->not->toBeEmpty();
        expect($response->json('data.0'))->toHaveKeys(['id', 'productId', 'sku', 'name', 'price', 'productImage']);
    }

    public function test_recent_order_items_requires_authentication(): void
    {
        $this->publicGet('/api/admin/customers/1/recent-order-items')->assertStatus(401);
    }

    public function test_recent_order_items_returns_at_most_five(): void
    {
        $customerId = $this->aCustomerWithOrders();

        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/customers/'.$customerId.'/recent-order-items');

        $response->assertOk();
        expect($response->json())->toHaveKeys(['data', 'meta']);
        expect(count($response->json('data')))->toBeLessThanOrEqual(5);

        if (! empty($response->json('data'))) {
            expect($response->json('data.0'))->toHaveKeys(['id', 'productId', 'sku', 'type', 'name', 'price']);
        }
    }
}
