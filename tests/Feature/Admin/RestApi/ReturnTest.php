<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\RMA\Models\RMA;
use Webkul\RMA\Models\RMAItem;
use Webkul\RMA\Models\RMAStatus;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;

class ReturnTest extends AdminApiTestCase
{
    private function createReturn(): array
    {
        $channel = Channel::first();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $order = Order::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => 'Jane',
            'customer_last_name'  => 'Doe',
            'channel_id'          => $channel->id,
            'status'              => 'completed',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'sku'        => 'ADMIN-RMA-1',
            'type'       => 'simple',
            'name'       => 'Returnable Product',
        ]);

        $status = RMAStatus::firstOrCreate(['id' => 1], ['title' => 'Pending', 'status' => 1, 'color' => '#f00', 'default' => 1]);

        $rma = RMA::create([
            'order_id'          => $order->id,
            'rma_status_id'     => $status->id,
            'information'       => 'Damaged on arrival',
            'package_condition' => 'opened',
        ]);

        RMAItem::create([
            'rma_id'        => $rma->id,
            'order_item_id' => $orderItem->id,
            'quantity'      => 1,
            'resolution'    => 'return',
        ]);

        return compact('order', 'orderItem', 'rma', 'status');
    }

    public function test_list_requests(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $response = $this->adminGet($admin, '/api/admin/rma/requests');

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('id', $data['rma']->id);
        expect($row)->not->toBeNull();
        expect((int) $row['orderId'])->toBe($data['order']->id);
        expect($row['customerName'])->toBe('Jane Doe');
    }

    public function test_view_request(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $response = $this->adminGet($admin, '/api/admin/rma/requests/'.$data['rma']->id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($data['rma']->id);
        expect($response->json('information'))->toBe('Damaged on arrival');
        expect($response->json('item.sku'))->toBe('ADMIN-RMA-1');
        expect($response->json('availableStatuses'))->toBeArray();
    }

    public function test_view_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/rma/requests/999999');

        expect($response->getStatusCode())->toBe(404);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->get('/api/admin/rma/requests', ['Accept' => 'application/json']);

        expect($response->getStatusCode())->toBe(401);
    }

    private function seedEligibleItem(): array
    {
        $channel = Channel::first();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        \Illuminate\Support\Facades\DB::table('product_flat')->insert([
            'product_id'           => $product->id,
            'sku'                  => 'ADMIN-EL-1',
            'name'                 => 'Eligible',
            'url_key'              => 'admin-el-'.$product->id,
            'status'               => 1,
            'visible_individually' => 1,
            'locale'               => app()->getLocale(),
            'channel'              => $channel->code,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'channel_id'  => $channel->id,
            'status'      => 'completed',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id'          => $order->id,
            'product_id'        => $product->id,
            'sku'               => 'ADMIN-EL-1',
            'type'              => 'simple',
            'name'              => 'Eligible',
            'qty_ordered'       => 2,
            'qty_invoiced'      => 2,
            'qty_refunded'      => 0,
            'qty_canceled'      => 0,
            'rma_return_period' => 30,
        ]);

        return compact('order', 'orderItem');
    }

    public function test_create_request(): void
    {
        RMAStatus::firstOrCreate(['id' => 1], ['title' => 'Pending', 'status' => 1, 'default' => 1]);
        $admin = $this->createAdmin();
        $seed = $this->seedEligibleItem();

        $response = $this->adminPost($admin, '/api/admin/rma/requests', [
            'order_id'        => $seed['order']->id,
            'order_item_id'   => $seed['orderItem']->id,
            'rma_qty'         => 1,
            'resolution_type' => 'return',
            'rma_reason_id'   => 1,
            'information'     => 'Defective',
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) $response->json('statusId'))->toBe(1);
        expect($response->json('item.resolution'))->toBe('return');
        $this->assertDatabaseHas('rma', ['order_id' => $seed['order']->id, 'rma_status_id' => 1]);
    }

    public function test_create_rejects_qty_over_returnable(): void
    {
        RMAStatus::firstOrCreate(['id' => 1], ['title' => 'Pending', 'status' => 1, 'default' => 1]);
        $admin = $this->createAdmin();
        $seed = $this->seedEligibleItem();

        $response = $this->adminPost($admin, '/api/admin/rma/requests', [
            'order_id'        => $seed['order']->id,
            'order_item_id'   => $seed['orderItem']->id,
            'rma_qty'         => 999,
            'resolution_type' => 'return',
            'rma_reason_id'   => 1,
        ]);

        expect($response->getStatusCode())->toBe(422);
        $this->assertDatabaseMissing('rma', ['order_id' => $seed['order']->id]);
    }

    public function test_returnable_items(): void
    {
        $admin = $this->createAdmin();
        $seed = $this->seedEligibleItem();

        $response = $this->adminGet($admin, '/api/admin/rma/requests/order-items?order_id='.$seed['order']->id);

        $response->assertOk();
        $row = collect($response->json())->firstWhere('orderItemId', $seed['orderItem']->id);
        expect($row)->not->toBeNull();
        expect((int) $row['forReturnQuantity'])->toBe(2);
    }

    public function test_reasons(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/rma/requests/resolution-reasons?resolution_type=return');

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    public function test_update_status_plain(): void
    {
        RMAStatus::firstOrCreate(['id' => 2], ['title' => 'Approved', 'status' => 1]);
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $response = $this->adminPost($admin, '/api/admin/rma/requests/'.$data['rma']->id.'/update-status', [
            'rma_status_id' => 2,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) $response->json('statusId'))->toBe(2);
        $this->assertDatabaseHas('rma', ['id' => $data['rma']->id, 'rma_status_id' => 2]);
        $this->assertDatabaseHas('rma_messages', ['rma_id' => $data['rma']->id, 'is_admin' => 1]);
    }

    public function test_update_status_invalid(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $response = $this->adminPost($admin, '/api/admin/rma/requests/'.$data['rma']->id.'/update-status', [
            'rma_status_id' => 999999,
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_received_package_without_refundable_order_fails(): void
    {
        RMAStatus::firstOrCreate(['id' => 5], ['title' => 'Received', 'status' => 1]);
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $response = $this->adminPost($admin, '/api/admin/rma/requests/'.$data['rma']->id.'/update-status', [
            'rma_status_id' => 5,
        ]);

        expect($response->getStatusCode())->toBe(422);
        $this->assertDatabaseMissing('rma', ['id' => $data['rma']->id, 'rma_status_id' => 5]);
    }

    public function test_reopen(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();
        $data['rma']->update(['rma_status_id' => 9]);

        $response = $this->adminPost($admin, '/api/admin/rma/requests/'.$data['rma']->id.'/reopen', []);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) $response->json('statusId'))->toBe(1);
    }

    public function test_send_and_list_messages(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $send = $this->adminPost($admin, '/api/admin/rma/messages', [
            'return_id' => $data['rma']->id,
            'message'   => 'Package received.',
        ]);

        expect($send->getStatusCode())->toBeIn([200, 201]);
        expect($send->json('message'))->toBe('Package received.');
        expect($send->json('isAdmin'))->toBeTrue();

        $list = $this->adminGet($admin, '/api/admin/rma/messages?return_id='.$data['rma']->id);

        $list->assertOk();
        expect(count($list->json()))->toBeGreaterThan(0);
        expect($list->json('0.message'))->toBe('Package received.');
    }
}
