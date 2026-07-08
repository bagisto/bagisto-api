<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Product\Models\Product;
use Webkul\RMA\Models\RMA;
use Webkul\RMA\Models\RMAItem;
use Webkul\RMA\Models\RMAStatus;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;

class CustomerReturnTest extends GraphQLTestCase
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

        $query = <<<'GQL'
            query {
              customerReturns {
                edges {
                  node {
                    _id
                    orderId
                    orderIncrementId
                    statusId
                    statusTitle
                    statusColor
                    messagesCount
                    item
                    createdAt
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $query);

        $response->assertOk();
        $node = $response->json('data.customerReturns.edges.0.node');
        expect((int) $node['_id'])->toBe($data['rma']->id);
        expect((int) $node['statusId'])->toBe(1);
        expect($node['statusTitle'])->toBe($data['status']->title);
        expect($node['item']['sku'])->toBe('RMA-SKU-1');
    }

    public function test_view_own_return(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $query = <<<GQL
            query {
              customerReturn(id: "/api/shop/returns/{$data['rma']->id}") {
                _id
                orderIncrementId
                statusTitle
                information
                packageCondition
                canClose
                canReopen
                isExpired
                item
                images
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $query);

        $response->assertOk();
        $node = $response->json('data.customerReturn');
        expect((int) $node['_id'])->toBe($data['rma']->id);
        expect($node['information'])->toBe('Item arrived damaged');
        expect($node['item']['resolution'])->toBe('return');
    }

    public function test_cannot_view_another_customers_return(): void
    {
        $this->seedRequiredData();
        $owner = $this->createCustomer();
        $data = $this->createReturn($owner);

        $other = $this->createCustomer();

        $query = <<<GQL
            query {
              customerReturn(id: "/api/shop/returns/{$data['rma']->id}") {
                _id
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($other, $query);

        $response->assertOk();
        expect($response->json('data.customerReturn'))->toBeNull();
        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_requires_authentication(): void
    {
        $this->seedRequiredData();

        $query = 'query { customerReturns { edges { node { _id } } } }';

        $response = $this->graphQL($query);

        expect($response->json('errors'))->not->toBeNull();
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

        $mutation = <<<'GQL'
            mutation CreateReturn($input: createCustomerReturnInput!) {
              createCustomerReturn(input: $input) {
                customerReturn {
                  _id
                  statusId
                  item
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [
                'orderId' => $seed['order']->id,
                'orderItemId' => $seed['orderItem']->id,
                'rmaQty' => 1,
                'resolutionType' => 'return',
                'rmaReasonId' => 1,
                'information' => 'Damaged',
                'agreement' => true,
            ],
        ]);

        $response->assertOk();
        $node = $response->json('data.createCustomerReturn.customerReturn');
        expect($node['_id'])->not->toBeNull();
        expect((int) $node['statusId'])->toBe(1);
        expect($node['item']['resolution'])->toBe('return');

        $this->assertDatabaseHas('rma', ['order_id' => $seed['order']->id, 'rma_status_id' => 1]);
    }

    public function test_create_rejects_qty_over_returnable(): void
    {
        $this->seedRequiredData();
        RMAStatus::firstOrCreate(['id' => 1], ['title' => 'Pending', 'status' => 1, 'default' => 1]);
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);

        $mutation = <<<'GQL'
            mutation CreateReturn($input: createCustomerReturnInput!) {
              createCustomerReturn(input: $input) {
                customerReturn { _id }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [
                'orderId' => $seed['order']->id,
                'orderItemId' => $seed['orderItem']->id,
                'rmaQty' => 999,
                'resolutionType' => 'return',
                'rmaReasonId' => 1,
                'agreement' => true,
            ],
        ]);

        expect($response->json('errors'))->not->toBeNull();
        $this->assertDatabaseMissing('rma', ['order_id' => $seed['order']->id]);
    }

    public function test_returnable_items_and_reasons(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $seed = $this->seedEligibleOrderItem($customer);

        $query = <<<GQL
            query {
              returnableItems(orderId: {$seed['order']->id}) {
                orderItemId
                sku
                forReturnQuantity
                currentQuantity
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $query);
        $response->assertOk();
        $node = $response->json('data.returnableItems.0');
        expect((int) $node['orderItemId'])->toBe($seed['orderItem']->id);
        expect((int) $node['forReturnQuantity'])->toBe(2);
    }

    public function test_return_reasons(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $query = 'query { returnReasons(resolutionType: "return") { id title } }';

        $response = $this->authenticatedGraphQL($customer, $query);
        $response->assertOk();
        expect($response->json('data.returnReasons'))->toBeArray();
        expect(count($response->json('data.returnReasons')))->toBeGreaterThan(0);
    }

    private function actionMutation(string $name, $customer, int $rmaId)
    {
        $mutation = <<<GQL
            mutation {
              {$name}CustomerReturn(input: {id: "/api/shop/returns/{$rmaId}"}) {
                customerReturn { _id statusId }
              }
            }
        GQL;

        return $this->authenticatedGraphQL($customer, $mutation);
    }

    public function test_cancel_return(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $response = $this->actionMutation('cancel', $customer, $data['rma']->id);

        $response->assertOk();
        expect((int) $response->json('data.cancelCustomerReturn.customerReturn.statusId'))->toBe(9);
        $this->assertDatabaseHas('rma', ['id' => $data['rma']->id, 'rma_status_id' => 9]);
    }

    public function test_close_return(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $response = $this->actionMutation('close', $customer, $data['rma']->id);

        $response->assertOk();
        expect((int) $response->json('data.closeCustomerReturn.customerReturn.statusId'))->toBe(6);
    }

    public function test_reopen_canceled_return(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);
        $data['rma']->update(['rma_status_id' => 9]);

        $response = $this->actionMutation('reopen', $customer, $data['rma']->id);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect((int) $response->json('data.reopenCustomerReturn.customerReturn.statusId'))->toBe(1);
    }

    public function test_send_and_list_messages(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $data = $this->createReturn($customer);

        $send = <<<GQL
            mutation {
              createCustomerReturnMessage(input: {returnId: {$data['rma']->id}, message: "Any update?"}) {
                customerReturnMessage { _id message isAdmin }
              }
            }
        GQL;

        $sendResponse = $this->authenticatedGraphQL($customer, $send);
        $sendResponse->assertOk();
        $msg = $sendResponse->json('data.createCustomerReturnMessage.customerReturnMessage');
        expect($msg['message'])->toBe('Any update?');
        expect($msg['isAdmin'])->toBeFalse();

        $list = <<<GQL
            query {
              customerReturnMessages(returnId: {$data['rma']->id}) {
                _id
                message
                isAdmin
                createdAt
              }
            }
        GQL;

        $listResponse = $this->authenticatedGraphQL($customer, $list);
        $listResponse->assertOk();
        expect(count($listResponse->json('data.customerReturnMessages')))->toBeGreaterThan(0);
    }
}
