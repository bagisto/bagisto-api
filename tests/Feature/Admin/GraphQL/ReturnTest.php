<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

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

        $query = <<<'GQL'
            query {
              adminReturns {
                edges {
                  node {
                    _id
                    orderId
                    customerName
                    statusId
                    statusTitle
                    createdAt
                  }
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $node = collect($response->json('data.adminReturns.edges'))
            ->pluck('node')
            ->firstWhere('_id', $data['rma']->id);
        expect($node)->not->toBeNull();
        expect((int) $node['orderId'])->toBe($data['order']->id);
        expect($node['customerName'])->toBe('Jane Doe');
    }

    public function test_view_request(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $query = <<<GQL
            query {
              adminReturn(id: "/api/admin/rma/requests/{$data['rma']->id}") {
                _id
                information
                statusTitle
                item
                images
                availableStatuses
                canReopen
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $node = $response->json('data.adminReturn');
        expect((int) $node['_id'])->toBe($data['rma']->id);
        expect($node['information'])->toBe('Damaged on arrival');
        expect($node['item']['sku'])->toBe('ADMIN-RMA-1');
    }

    public function test_requires_authentication(): void
    {
        $query = 'query { adminReturns { edges { node { _id } } } }';

        $response = $this->adminGraphQL($query);

        expect($response->json('errors'))->not->toBeNull();
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

        $mutation = <<<GQL
            mutation {
              createAdminReturn(input: {
                orderId: {$seed['order']->id},
                orderItemId: {$seed['orderItem']->id},
                rmaQty: 1,
                resolutionType: "return",
                rmaReasonId: 1,
                information: "Defective"
              }) {
                adminReturn { _id statusId item }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [], $admin);

        $response->assertOk();
        $node = $response->json('data.createAdminReturn.adminReturn');
        expect($node['_id'])->not->toBeNull();
        expect((int) $node['statusId'])->toBe(1);
        expect($node['item']['resolution'])->toBe('return');
    }

    public function test_returnable_items_and_reasons(): void
    {
        $admin = $this->createAdmin();
        $seed = $this->seedEligibleItem();

        $items = $this->adminGraphQL(
            "query { adminReturnableItems(orderId: {$seed['order']->id}) { orderItemId forReturnQuantity } }",
            [],
            $admin
        );
        $items->assertOk();
        expect($items->json('data.adminReturnableItems.0.orderItemId'))->not->toBeNull();

        $reasons = $this->adminGraphQL(
            'query { adminReturnReasons(resolutionType: "return") { id title } }',
            [],
            $admin
        );
        $reasons->assertOk();
        expect(count($reasons->json('data.adminReturnReasons')))->toBeGreaterThan(0);
    }

    public function test_update_status_and_reopen(): void
    {
        RMAStatus::firstOrCreate(['id' => 2], ['title' => 'Approved', 'status' => 1]);
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $update = <<<GQL
            mutation {
              updateStatusAdminReturn(input: {id: "/api/admin/rma/requests/{$data['rma']->id}", rmaStatusId: 2}) {
                adminReturn { _id statusId }
              }
            }
        GQL;

        $updateResponse = $this->adminGraphQL($update, [], $admin);
        $updateResponse->assertOk();
        expect((int) $updateResponse->json('data.updateStatusAdminReturn.adminReturn.statusId'))->toBe(2);

        $data['rma']->update(['rma_status_id' => 9]);

        $reopen = <<<GQL
            mutation {
              reopenAdminReturn(input: {id: "/api/admin/rma/requests/{$data['rma']->id}"}) {
                adminReturn { _id statusId }
              }
            }
        GQL;

        $reopenResponse = $this->adminGraphQL($reopen, [], $admin);
        $reopenResponse->assertOk();
        expect((int) $reopenResponse->json('data.reopenAdminReturn.adminReturn.statusId'))->toBe(1);
    }

    public function test_send_and_list_messages(): void
    {
        $admin = $this->createAdmin();
        $data = $this->createReturn();

        $send = <<<GQL
            mutation {
              createAdminReturnMessage(input: {returnId: {$data['rma']->id}, message: "Package received."}) {
                adminReturnMessage { _id message isAdmin }
              }
            }
        GQL;

        $sendResponse = $this->adminGraphQL($send, [], $admin);
        $sendResponse->assertOk();
        $msg = $sendResponse->json('data.createAdminReturnMessage.adminReturnMessage');
        expect($msg['message'])->toBe('Package received.');
        expect($msg['isAdmin'])->toBeTrue();

        $list = $this->adminGraphQL(
            "query { adminReturnMessages(returnId: {$data['rma']->id}) { _id message isAdmin } }",
            [],
            $admin
        );
        $list->assertOk();
        expect(count($list->json('data.adminReturnMessages')))->toBeGreaterThan(0);
    }
}
