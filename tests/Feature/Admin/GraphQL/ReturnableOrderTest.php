<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Str;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class ReturnableOrderTest extends AdminApiTestCase
{
    /**
     * Test an eligible order is listed with its multi-word fields.
     */
    public function test_lists_an_eligible_order(): void
    {
        $admin = $this->createAdmin();
        $order = $this->seedOrder();

        $response = $this->adminGraphQL($this->query($order->id), [], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        $node = collect($response->json('data.adminReturnableOrders.edges'))
            ->pluck('node')
            ->firstWhere('_id', $order->id);
        expect($node)->not->toBeNull();
        expect($node['incrementId'])->toBe($order->increment_id);
        expect($node['customerName'])->toBe('Jane Doe');
        expect((int) $node['returnableQty'])->toBe(2);
        expect($node['orderCurrencyCode'])->toBe('USD');
    }

    /**
     * Test the order picker requires authentication.
     */
    public function test_requires_authentication(): void
    {
        $response = $this->adminGraphQL('query { adminReturnableOrders { edges { node { _id } } } }');

        expect($response->json('errors'))->not->toBeNull();
    }

    /**
     * Test a role holding exactly the create key may list the orders.
     */
    public function test_role_with_the_create_key_can_list(): void
    {
        $admin = $this->customRoleAdmin(['sales', 'sales.rma', 'sales.rma.requests', 'sales.rma.requests.create']);
        $order = $this->seedOrder();

        $response = $this->adminGraphQL($this->query($order->id), [], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect(collect($response->json('data.adminReturnableOrders.edges'))->pluck('node._id')->all())->toContain($order->id);
    }

    /**
     * Test a role without the create key is refused.
     */
    public function test_role_without_the_create_key_is_refused(): void
    {
        $admin = $this->customRoleAdmin(['sales', 'sales.rma', 'sales.rma.requests']);
        $order = $this->seedOrder();

        $response = $this->adminGraphQL($this->query($order->id), [], $admin);

        expect($response->json('errors'))->not->toBeNull();
        expect($response->json('data.adminReturnableOrders'))->toBeNull();
    }

    /**
     * Build the order picker query for one order.
     */
    protected function query(int $orderId): string
    {
        return <<<GQL
            query {
              adminReturnableOrders(id: {$orderId}) {
                edges {
                  node {
                    _id
                    incrementId
                    customerName
                    status
                    isGuest
                    grandTotal
                    orderCurrencyCode
                    methodTitle
                    totalQtyOrdered
                    totalRmaQty
                    returnableQty
                    createdAt
                  }
                }
              }
            }
        GQL;
    }

    /**
     * Seed a completed order with one item inside its return window.
     */
    protected function seedOrder(): Order
    {
        $this->seedRequiredData();

        $channel = Channel::first();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $order = Order::factory()->create([
            'increment_id' => 'RMAPICK'.strtoupper(Str::random(10)),
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => 'Jane',
            'customer_last_name' => 'Doe',
            'channel_id' => $channel->id,
            'status' => 'completed',
        ]);

        OrderItem::factory()->create([
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
        ]);

        return $order;
    }

    /**
     * Create an admin whose role holds only the given permissions.
     */
    protected function customRoleAdmin(array $permissions): Admin
    {
        $role = Role::create([
            'name' => 'rma-pick-gql-'.uniqid(),
            'description' => 'rma-pick-test',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }
}
