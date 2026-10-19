<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;

class CustomerSidebarTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /**
     * Test cart items query.
     */
    public function test_cart_items_query(): void
    {
        $customerId = $this->bootstrapCustomerWithActiveCart();

        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query items($customerId: Int!) {
              adminCustomerCartItems(customerId: $customerId) {
                totalCount
                edges { node { id productId sku type name quantity price } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['customerId' => $customerId], $admin);

        $response->assertOk();
        expect($response->json('data.adminCustomerCartItems.totalCount'))->toBeInt()->toBeGreaterThan(0);
    }

    /**
     * Test wishlist items query.
     */
    public function test_wishlist_items_query(): void
    {
        $customerId = $this->bootstrapCustomerWithWishlist();

        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query items($customerId: Int!) {
              adminCustomerWishlistItems(customerId: $customerId) {
                totalCount
                edges { node { id productId sku name price } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['customerId' => $customerId], $admin);

        $response->assertOk();
        expect($response->json('data.adminCustomerWishlistItems.totalCount'))->toBeInt()->toBeGreaterThan(0);
    }

    /**
     * Test recent order items query.
     */
    public function test_recent_order_items_query(): void
    {
        $customerId = $this->bootstrapCustomerWithOrders();

        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query items($customerId: Int!) {
              adminCustomerRecentOrderItems(customerId: $customerId) {
                totalCount
                edges { node { id productId sku type name price } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['customerId' => $customerId], $admin);

        $response->assertOk();
        expect($response->json('data.adminCustomerRecentOrderItems.totalCount'))->toBeInt();
    }

    /**
     * Test sidebar queries require authentication.
     */
    public function test_sidebar_queries_require_authentication(): void
    {
        $query = <<<'GQL'
            query { adminCustomerCartItems(customerId: 1) { totalCount } }
        GQL;

        expect($this->adminGraphQL($query)->json('errors'))->not->toBeNull();
    }
}
