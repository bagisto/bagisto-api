<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Checkout\Models\Cart;
use Webkul\Customer\Models\Customer;

/**
 * GraphQL coverage for the Create-Order screen's three sidebar panels.
 */
class CustomerSidebarTest extends AdminApiTestCase
{
    public function test_cart_items_query(): void
    {
        $customerId = Cart::query()->whereNotNull('customer_id')->where('is_active', 1)
            ->whereHas('items', fn ($q) => $q->whereNull('parent_id'))->value('customer_id');

        if ($customerId === null) {
            $this->markTestSkipped('No customer with an active cart.');
        }

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

    public function test_wishlist_items_query(): void
    {
        $customerId = Customer::whereHas('wishlist_items')->value('id');

        if ($customerId === null) {
            $this->markTestSkipped('No customer with wishlist items.');
        }

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

    public function test_recent_order_items_query(): void
    {
        $customerId = Customer::whereHas('orders')->value('id');

        if ($customerId === null) {
            $this->markTestSkipped('No customer with orders.');
        }

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

    public function test_sidebar_queries_require_authentication(): void
    {
        $query = <<<'GQL'
            query { adminCustomerCartItems(customerId: 1) { totalCount } }
        GQL;

        expect($this->adminGraphQL($query)->json('errors'))->not->toBeNull();
    }
}
