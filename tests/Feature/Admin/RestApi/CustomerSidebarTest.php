<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Checkout\Models\Cart;
use Webkul\Customer\Models\Customer;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

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

    protected function customRoleAdmin(array $permissions = []): Admin
    {
        $role = Role::create([
            'name' => 'sidebar-test-'.uniqid(),
            'description' => 'sidebar-test',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    protected function activeCartItemId(int $customerId): int
    {
        return (int) Cart::where('customer_id', $customerId)
            ->where('is_active', 1)
            ->first()
            ->items()
            ->whereNull('parent_id')
            ->value('id');
    }

    protected function seedWishlistItem(int $customerId): int
    {
        return (int) DB::table('wishlist_items')->insertGetId([
            'channel_id' => core()->getCurrentChannel()->id,
            'product_id' => $this->findOrCreateSimpleProduct()->id,
            'customer_id' => $customerId,
            'additional' => json_encode([]),
            'moved_to_cart' => 0,
            'shared' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function seedCompareItem(int $customerId): int
    {
        return (int) DB::table('compare_items')->insertGetId([
            'product_id' => $this->findOrCreateSimpleProduct()->id,
            'customer_id' => $customerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_delete_cart_item_removes_it(): void
    {
        $customerId = $this->aCustomerWithActiveCart();
        $itemId = $this->activeCartItemId($customerId);

        $admin = $this->createAdmin();
        $response = $this->deleteJson(
            '/api/admin/customers/'.$customerId.'/cart-items',
            ['itemId' => $itemId],
            $this->adminHeaders($admin),
        );

        $response->assertOk();
        expect($response->json('id'))->toBe($itemId);
        expect(DB::table('cart_items')->where('id', $itemId)->exists())->toBeFalse();
    }

    public function test_delete_cart_item_of_another_customer_returns_404(): void
    {
        $customerId = $this->aCustomerWithActiveCart();
        $itemId = $this->activeCartItemId($customerId);
        $other = Customer::factory()->create();

        $admin = $this->createAdmin();
        $this->deleteJson(
            '/api/admin/customers/'.$other->id.'/cart-items',
            ['itemId' => $itemId],
            $this->adminHeaders($admin),
        )->assertStatus(404);

        expect(DB::table('cart_items')->where('id', $itemId)->exists())->toBeTrue();
    }

    public function test_delete_cart_item_without_item_id_returns_422(): void
    {
        $customerId = $this->aCustomerWithActiveCart();

        $admin = $this->createAdmin();
        $this->deleteJson(
            '/api/admin/customers/'.$customerId.'/cart-items',
            [],
            $this->adminHeaders($admin),
        )->assertStatus(422);
    }

    public function test_delete_cart_item_requires_authentication(): void
    {
        $this->seedRequiredData();

        $this->deleteJson('/api/admin/customers/1/cart-items', ['itemId' => 1])->assertStatus(401);
    }

    public function test_delete_wishlist_item_removes_it(): void
    {
        $this->seedRequiredData();
        $customer = Customer::factory()->create();
        $itemId = $this->seedWishlistItem($customer->id);

        $admin = $this->createAdmin();
        $response = $this->deleteJson(
            '/api/admin/customers/'.$customer->id.'/wishlist-items',
            ['itemId' => $itemId],
            $this->adminHeaders($admin),
        );

        $response->assertOk();
        expect(DB::table('wishlist_items')->where('id', $itemId)->exists())->toBeFalse();
    }

    public function test_delete_wishlist_item_of_another_customer_returns_404(): void
    {
        $this->seedRequiredData();
        $owner = Customer::factory()->create();
        $other = Customer::factory()->create();
        $itemId = $this->seedWishlistItem($owner->id);

        $admin = $this->createAdmin();
        $this->deleteJson(
            '/api/admin/customers/'.$other->id.'/wishlist-items',
            ['itemId' => $itemId],
            $this->adminHeaders($admin),
        )->assertStatus(404);

        expect(DB::table('wishlist_items')->where('id', $itemId)->exists())->toBeTrue();
    }

    public function test_delete_wishlist_item_unknown_item_returns_422(): void
    {
        $this->seedRequiredData();
        $customer = Customer::factory()->create();

        $admin = $this->createAdmin();
        $this->deleteJson(
            '/api/admin/customers/'.$customer->id.'/wishlist-items',
            ['itemId' => 999999999],
            $this->adminHeaders($admin),
        )->assertStatus(422);
    }

    public function test_delete_wishlist_item_without_permission_returns_403(): void
    {
        $this->seedRequiredData();
        $customer = Customer::factory()->create();
        $itemId = $this->seedWishlistItem($customer->id);

        $admin = $this->customRoleAdmin(['customers', 'customers.customers']);
        $this->deleteJson(
            '/api/admin/customers/'.$customer->id.'/wishlist-items',
            ['itemId' => $itemId],
            $this->adminHeaders($admin),
        )->assertStatus(403);

        expect(DB::table('wishlist_items')->where('id', $itemId)->exists())->toBeTrue();
    }

    public function test_delete_compare_item_removes_it(): void
    {
        $this->seedRequiredData();
        $customer = Customer::factory()->create();
        $itemId = $this->seedCompareItem($customer->id);

        $admin = $this->createAdmin();
        $this->deleteJson(
            '/api/admin/customers/'.$customer->id.'/compare-items',
            ['item_id' => $itemId],
            $this->adminHeaders($admin),
        )->assertOk();

        expect(DB::table('compare_items')->where('id', $itemId)->exists())->toBeFalse();
    }

    public function test_delete_compare_item_unknown_customer_returns_404(): void
    {
        $this->seedRequiredData();
        $customer = Customer::factory()->create();
        $itemId = $this->seedCompareItem($customer->id);

        $admin = $this->createAdmin();
        $this->deleteJson(
            '/api/admin/customers/999999999/compare-items',
            ['itemId' => $itemId],
            $this->adminHeaders($admin),
        )->assertStatus(404);
    }

    public function test_delete_compare_item_requires_authentication(): void
    {
        $this->seedRequiredData();

        $this->deleteJson('/api/admin/customers/1/compare-items', ['itemId' => 1])->assertStatus(401);
    }
}
