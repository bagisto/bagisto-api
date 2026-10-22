<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Str;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\RMA\Models\RMA;
use Webkul\RMA\Models\RMAItem;
use Webkul\RMA\Models\RMAStatus;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class ReturnableOrderTest extends AdminApiTestCase
{
    /**
     * The permissions of a role that may open return requests.
     */
    protected const CREATE_PERMISSIONS = ['sales', 'sales.rma', 'sales.rma.requests', 'sales.rma.requests.create'];

    /**
     * Test an order with an item inside its return window is listed.
     */
    public function test_lists_an_eligible_order(): void
    {
        $admin = $this->createAdmin();
        $seed = $this->seedOrder();

        $response = $this->adminGet($admin, '/api/admin/rma/requests/orders?id='.$seed['order']->id);

        $response->assertOk();
        expect($response->json('meta'))->toBeArray();
        $row = collect($response->json('data'))->firstWhere('id', $seed['order']->id);
        expect($row)->not->toBeNull();
        expect($row['incrementId'])->toBe($seed['order']->increment_id);
        expect($row['customerName'])->toBe('Jane Doe');
        expect($row['status'])->toBe('completed');
        expect($row['isGuest'])->toBeFalse();
        expect((int) $row['totalQtyOrdered'])->toBe(2);
        expect((int) $row['totalRmaQty'])->toBe(0);
        expect((int) $row['returnableQty'])->toBe(2);
    }

    /**
     * Test the quantity already put on a return is subtracted.
     */
    public function test_partly_returned_order_shows_the_remaining_quantity(): void
    {
        $admin = $this->createAdmin();
        $seed = $this->seedOrder();
        $this->putOnReturn($seed['order'], $seed['orderItem'], 1);

        $response = $this->adminGet($admin, '/api/admin/rma/requests/orders?id='.$seed['order']->id);

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('id', $seed['order']->id);
        expect((int) $row['totalRmaQty'])->toBe(1);
        expect((int) $row['returnableQty'])->toBe(1);
    }

    /**
     * Test a fully returned order is left out.
     */
    public function test_fully_returned_order_is_left_out(): void
    {
        $admin = $this->createAdmin();
        $seed = $this->seedOrder();
        $this->putOnReturn($seed['order'], $seed['orderItem'], 2);

        $response = $this->adminGet($admin, '/api/admin/rma/requests/orders?id='.$seed['order']->id);

        $response->assertOk();
        expect($response->json('data'))->toBe([]);
    }

    /**
     * Test a canceled order is left out.
     */
    public function test_canceled_order_is_left_out(): void
    {
        $admin = $this->createAdmin();
        $seed = $this->seedOrder(['status' => 'canceled']);

        $response = $this->adminGet($admin, '/api/admin/rma/requests/orders?id='.$seed['order']->id);

        $response->assertOk();
        expect($response->json('data'))->toBe([]);
    }

    /**
     * Test an order whose items carry no return period is left out.
     */
    public function test_order_without_a_return_period_is_left_out(): void
    {
        $admin = $this->createAdmin();
        $seed = $this->seedOrder([], ['rma_return_period' => null]);

        $response = $this->adminGet($admin, '/api/admin/rma/requests/orders?id='.$seed['order']->id);

        $response->assertOk();
        expect($response->json('data'))->toBe([]);
    }

    /**
     * Test the increment id and customer name filters.
     */
    public function test_filters_by_increment_id_and_customer_name(): void
    {
        $admin = $this->createAdmin();
        $seed = $this->seedOrder();

        $byIncrement = $this->adminGet($admin, '/api/admin/rma/requests/orders?per_page=50&increment_id='.$seed['order']->increment_id);

        $byIncrement->assertOk();
        expect(collect($byIncrement->json('data'))->pluck('id')->all())->toContain($seed['order']->id);

        $byName = $this->adminGet($admin, '/api/admin/rma/requests/orders?id='.$seed['order']->id.'&customer_name=nobody-'.Str::random(8));

        $byName->assertOk();
        expect($byName->json('data'))->toBe([]);
    }

    /**
     * Test the order picker requires authentication.
     */
    public function test_requires_authentication(): void
    {
        $this->get('/api/admin/rma/requests/orders', ['Accept' => 'application/json'])->assertStatus(401);
    }

    /**
     * Test a role holding exactly the create key may list the orders.
     */
    public function test_role_with_the_create_key_can_list(): void
    {
        $admin = $this->customRoleAdmin(self::CREATE_PERMISSIONS);
        $seed = $this->seedOrder();

        $response = $this->adminGet($admin, '/api/admin/rma/requests/orders?id='.$seed['order']->id);

        $response->assertOk();
        expect(collect($response->json('data'))->pluck('id')->all())->toContain($seed['order']->id);
    }

    /**
     * Test a role that may view returns but not open them is forbidden.
     */
    public function test_role_without_the_create_key_is_forbidden(): void
    {
        $admin = $this->customRoleAdmin(['sales', 'sales.rma', 'sales.rma.requests']);

        $this->adminGet($admin, '/api/admin/rma/requests/orders')->assertStatus(403);
    }

    /**
     * Test an admin whose role is narrowed to fewer permissions is forbidden on the next request.
     */
    public function test_role_narrowed_to_fewer_permissions_is_forbidden(): void
    {
        $admin = $this->customRoleAdmin(self::CREATE_PERMISSIONS);
        $token = $this->tokenForNarrowedRole($admin, ['sales', 'sales.rma', 'sales.rma.requests']);

        $this->adminGet($admin, '/api/admin/rma/requests/orders', $token)->assertStatus(403);
    }

    /**
     * Seed a completed order with one item inside its return window.
     */
    protected function seedOrder(array $orderOverrides = [], array $itemOverrides = []): array
    {
        $this->seedRequiredData();

        $channel = Channel::first();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $order = Order::factory()->create(array_merge([
            'increment_id' => 'RMAPICK'.strtoupper(Str::random(10)),
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => 'Jane',
            'customer_last_name' => 'Doe',
            'channel_id' => $channel->id,
            'status' => 'completed',
        ], $orderOverrides));

        $orderItem = OrderItem::factory()->create(array_merge([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sku' => 'ADMIN-RMA-PICK-1',
            'type' => 'simple',
            'name' => 'Returnable Product',
            'qty_ordered' => 2,
            'qty_invoiced' => 2,
            'qty_refunded' => 0,
            'qty_canceled' => 0,
            'rma_return_period' => 30,
        ], $itemOverrides));

        return compact('order', 'orderItem');
    }

    /**
     * Put a quantity of an order item on a return request.
     */
    protected function putOnReturn(Order $order, OrderItem $orderItem, int $quantity): void
    {
        $status = RMAStatus::firstOrCreate(['id' => 1], [
            'title' => 'Pending',
            'status' => 1,
            'color' => '#f00',
            'default' => 1,
        ]);

        $rma = RMA::create([
            'order_id' => $order->id,
            'rma_status_id' => $status->id,
        ]);

        RMAItem::create([
            'rma_id' => $rma->id,
            'order_item_id' => $orderItem->id,
            'quantity' => $quantity,
            'resolution' => 'return',
        ]);
    }

    /**
     * Create an admin whose role holds only the given permissions.
     */
    protected function customRoleAdmin(array $permissions): Admin
    {
        $role = Role::create([
            'name' => 'rma-pick-'.uniqid(),
            'description' => 'rma-pick-test',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    /**
     * Give the admin a new role with exactly the given permissions and return a fresh access token.
     */
    protected function tokenForNarrowedRole(Admin $admin, array $permissions): string
    {
        $role = Role::create([
            'name' => 'narrowed-'.Str::random(6),
            'description' => 'narrowed',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        $admin->forceFill(['role_id' => $role->id])->save();

        return $this->adminAccessToken($admin->fresh());
    }
}
