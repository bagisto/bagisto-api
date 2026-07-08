<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Product\Models\Product;
use Webkul\RMA\Models\RMA;
use Webkul\RMA\Models\RMAItem;
use Webkul\RMA\Models\RMAStatus;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;

class CustomerReturnTest extends RestApiTestCase
{
    private function createReturn($customer): array
    {
        $channel = Channel::first();
        $product = Product::factory()->create();

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'channel_id' => $channel->id,
            'status' => 'completed',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sku' => 'RMA-SKU-1',
            'type' => 'simple',
            'name' => 'Returnable Product',
        ]);

        $status = RMAStatus::firstOrCreate(
            ['id' => 1],
            ['title' => 'Pending', 'status' => 1, 'color' => '#facc15', 'default' => 1]
        );

        $rma = RMA::create([
            'order_id' => $order->id,
            'rma_status_id' => $status->id,
            'information' => 'Item arrived damaged',
            'package_condition' => 'opened',
        ]);

        RMAItem::create([
            'rma_id' => $rma->id,
            'order_item_id' => $orderItem->id,
            'quantity' => 1,
            'resolution' => 'return',
        ]);

        return compact('customer', 'order', 'orderItem', 'rma', 'status');
    }

    public function test_list_returns_own_returns(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $response = $this->authenticatedGet($customer, '/api/shop/returns');

        $response->assertOk();
        $row = $response->json('0');
        expect((int) $row['id'])->toBe($data['rma']->id);
        expect((int) $row['statusId'])->toBe(1);
        expect($row['item']['sku'])->toBe('RMA-SKU-1');
    }

    public function test_view_own_return(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $response = $this->authenticatedGet($customer, '/api/shop/returns/'.$data['rma']->id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($data['rma']->id);
        expect($response->json('information'))->toBe('Item arrived damaged');
        expect($response->json('item.resolution'))->toBe('return');
        expect($response->json('images'))->toBeArray();
    }

    public function test_cannot_view_another_customers_return(): void
    {
        $this->seedRequiredData();
        $owner = $this->createCustomer();
        $data = $this->createReturn($owner);

        $other = $this->createCustomer();

        $response = $this->authenticatedGet($other, '/api/shop/returns/'.$data['rma']->id);

        expect($response->getStatusCode())->toBeIn([403, 404]);
    }

    public function test_requires_authentication(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/returns');

        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    private function seedEligibleOrderItem($customer): array
    {
        $channel = Channel::first();
        $product = Product::factory()->create();

        DB::table('product_flat')->insert([
            'product_id' => $product->id,
            'sku' => 'ELIGIBLE-1',
            'name' => 'Eligible Product',
            'url_key' => 'eligible-1-'.$product->id,
            'status' => 1,
            'visible_individually' => 1,
            'locale' => app()->getLocale(),
            'channel' => $channel->code,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'channel_id' => $channel->id,
            'status' => 'completed',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sku' => 'ELIGIBLE-1',
            'type' => 'simple',
            'name' => 'Eligible Product',
            'qty_ordered' => 2,
            'qty_invoiced' => 2,
            'qty_refunded' => 0,
            'qty_canceled' => 0,
            'rma_return_period' => 30,
        ]);

        return compact('order', 'orderItem', 'product');
    }

    public function test_create_return(): void
    {
        $this->seedRequiredData();
        RMAStatus::firstOrCreate(['id' => 1], ['title' => 'Pending', 'status' => 1, 'default' => 1]);
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/returns', [
            'order_id' => $seed['order']->id,
            'order_item_id' => $seed['orderItem']->id,
            'rma_qty' => 1,
            'resolution_type' => 'return',
            'rma_reason_id' => 1,
            'information' => 'Damaged',
            'agreement' => true,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) $response->json('statusId'))->toBe(1);
        expect($response->json('item.resolution'))->toBe('return');
        $this->assertDatabaseHas('rma', ['order_id' => $seed['order']->id, 'rma_status_id' => 1]);
    }

    public function test_create_rejects_qty_over_returnable(): void
    {
        $this->seedRequiredData();
        RMAStatus::firstOrCreate(['id' => 1], ['title' => 'Pending', 'status' => 1, 'default' => 1]);
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/returns', [
            'order_id' => $seed['order']->id,
            'order_item_id' => $seed['orderItem']->id,
            'rma_qty' => 999,
            'resolution_type' => 'return',
            'rma_reason_id' => 1,
            'agreement' => true,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422]);
        $this->assertDatabaseMissing('rma', ['order_id' => $seed['order']->id]);
    }

    public function test_create_requires_agreement(): void
    {
        $this->seedRequiredData();
        RMAStatus::firstOrCreate(['id' => 1], ['title' => 'Pending', 'status' => 1, 'default' => 1]);
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/returns', [
            'order_id' => $seed['order']->id,
            'order_item_id' => $seed['orderItem']->id,
            'rma_qty' => 1,
            'resolution_type' => 'return',
            'rma_reason_id' => 1,
            'agreement' => false,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422]);
    }

    public function test_returnable_items(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);

        $response = $this->authenticatedGet($customer, '/api/shop/returnable-items?order_id='.$seed['order']->id);

        $response->assertOk();
        $row = $response->json('0');
        expect((int) $row['orderItemId'])->toBe($seed['orderItem']->id);
        expect((int) $row['forReturnQuantity'])->toBe(2);
    }

    public function test_return_reasons(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/return-reasons?resolution_type=return');

        $response->assertOk();
        expect($response->json())->toBeArray();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    public function test_cancel_return(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/returns/'.$data['rma']->id.'/cancel', []);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) $response->json('statusId'))->toBe(9);
        $this->assertDatabaseHas('rma', ['id' => $data['rma']->id, 'rma_status_id' => 9]);
    }

    public function test_close_return(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/returns/'.$data['rma']->id.'/close', []);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) $response->json('statusId'))->toBe(6);
    }

    public function test_cancel_another_customers_return_is_blocked(): void
    {
        $this->seedRequiredData();
        $owner = $this->createCustomer();
        $data = $this->createReturn($owner);
        $other = $this->createCustomer();

        $response = $this->authenticatedPost($other, '/api/shop/returns/'.$data['rma']->id.'/cancel', []);

        expect($response->getStatusCode())->toBeIn([403, 404]);
        $this->assertDatabaseMissing('rma', ['id' => $data['rma']->id, 'rma_status_id' => 9]);
    }

    public function test_send_and_list_messages(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $send = $this->authenticatedPost($customer, '/api/shop/return-messages', [
            'return_id' => $data['rma']->id,
            'message' => 'Any update?',
        ]);

        expect($send->getStatusCode())->toBeIn([200, 201]);
        expect($send->json('message'))->toBe('Any update?');
        expect($send->json('isAdmin'))->toBeFalse();

        $list = $this->authenticatedGet($customer, '/api/shop/return-messages?return_id='.$data['rma']->id);

        $list->assertOk();
        expect(count($list->json()))->toBeGreaterThan(0);
        expect($list->json('0.message'))->toBe('Any update?');
    }

    public function test_messages_of_another_customer_blocked(): void
    {
        $this->seedRequiredData();
        $owner = $this->createCustomer();
        $data = $this->createReturn($owner);
        $other = $this->createCustomer();

        $response = $this->authenticatedGet($other, '/api/shop/return-messages?return_id='.$data['rma']->id);

        expect($response->getStatusCode())->toBeIn([403, 404]);
    }
}
