<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Checkout\Models\Cart;
use Webkul\Customer\Models\Customer;

/**
 * GraphQL coverage for the Create-Order screen's three sidebar panels.
 */
class CustomerSidebarTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

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

    public function test_sidebar_queries_require_authentication(): void
    {
        $query = <<<'GQL'
            query { adminCustomerCartItems(customerId: 1) { totalCount } }
        GQL;

        expect($this->adminGraphQL($query)->json('errors'))->not->toBeNull();
    }

    protected function deleteMutation(string $field): string
    {
        return 'mutation remove($input: '.$field.'Input!) { '.$field.'(input: $input) { '
            .lcfirst(substr($field, 6)).' { _id } } }';
    }

    public function test_remove_cart_item_mutation(): void
    {
        $customerId = $this->bootstrapCustomerWithActiveCart();
        $itemId = (int) Cart::where('customer_id', $customerId)
            ->where('is_active', 1)
            ->first()
            ->items()
            ->whereNull('parent_id')
            ->value('id');

        $admin = $this->createAdmin();
        $iri = '/api/admin/customers/'.$customerId.'/cart-items/'.$itemId;

        $response = $this->adminGraphQL($this->deleteMutation('removeAdminCustomerCartItem'), [
            'input' => ['id' => $iri],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.removeAdminCustomerCartItem.adminCustomerCartItem._id'))->toBe($itemId);
        expect(DB::table('cart_items')->where('id', $itemId)->exists())->toBeFalse();
    }

    public function test_remove_wishlist_item_mutation(): void
    {
        $this->seedRequiredData();
        $customer = Customer::factory()->create();
        $itemId = (int) DB::table('wishlist_items')->insertGetId([
            'channel_id' => core()->getCurrentChannel()->id,
            'product_id' => $this->findOrCreateSimpleProduct()->id,
            'customer_id' => $customer->id,
            'additional' => json_encode([]),
            'moved_to_cart' => 0,
            'shared' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin = $this->createAdmin();

        $response = $this->adminGraphQL($this->deleteMutation('removeAdminCustomerWishlistItem'), [
            'input' => ['id' => '/api/admin/customers/'.$customer->id.'/wishlist-items/'.$itemId],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect(DB::table('wishlist_items')->where('id', $itemId)->exists())->toBeFalse();
    }

    public function test_delete_compare_item_mutation_rejects_other_customer(): void
    {
        $this->seedRequiredData();
        $owner = Customer::factory()->create();
        $other = Customer::factory()->create();
        $itemId = (int) DB::table('compare_items')->insertGetId([
            'product_id' => $this->findOrCreateSimpleProduct()->id,
            'customer_id' => $owner->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin = $this->createAdmin();

        $response = $this->adminGraphQL($this->deleteMutation('removeAdminCustomerCompareItem'), [
            'input' => ['id' => '/api/admin/customers/'.$other->id.'/compare-items/'.$itemId],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
        expect(DB::table('compare_items')->where('id', $itemId)->exists())->toBeTrue();
    }

    public function test_delete_compare_item_mutation(): void
    {
        $this->seedRequiredData();
        $customer = Customer::factory()->create();
        $itemId = (int) DB::table('compare_items')->insertGetId([
            'product_id' => $this->findOrCreateSimpleProduct()->id,
            'customer_id' => $customer->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin = $this->createAdmin();

        $response = $this->adminGraphQL($this->deleteMutation('removeAdminCustomerCompareItem'), [
            'input' => ['id' => '/api/admin/customers/'.$customer->id.'/compare-items/'.$itemId],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect(DB::table('compare_items')->where('id', $itemId)->exists())->toBeFalse();
    }

    public function test_delete_mutation_requires_authentication(): void
    {
        $this->seedRequiredData();

        $response = $this->adminGraphQL($this->deleteMutation('removeAdminCustomerCompareItem'), [
            'input' => ['id' => '/api/admin/customers/1/compare-items/1'],
        ]);

        expect($response->json('errors'))->not->toBeNull();
    }
}
