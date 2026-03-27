<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

class OrderActionTest extends GraphQLTestCase
{
    /**
     * Create a test order for a customer with a fully saleable product.
     *
     * OrderItem must have type, sku, name, price, weight set — otherwise
     * OrderRepository::cancel() fails in returnQtyToProductInventory() and
     * Cart::addProduct() fails in the reorder flow.
     */
    private function createTestOrder($customer, $status = 'pending'): array
    {
        $productData = $this->createTestProduct();
        $product = $productData['product'];

        $order = Order::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'status'              => $status,
        ]);

        OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'sku'        => $product->sku,
            'name'       => $product->name ?? 'Test Product',
            'type'       => $product->type ?? 'simple',
            'qty_ordered' => 1,
            'price'       => 10,
            'base_price'  => 10,
            'total'       => 10,
            'base_total'  => 10,
            'weight'      => 1,
        ]);

        OrderPayment::factory()->create(['order_id' => $order->id]);

        return ['order' => $order, 'product' => $product];
    }

    public function test_customer_can_cancel_their_own_pending_order(): void
    {
        $customer = $this->createCustomer();
        $data = $this->createTestOrder($customer, 'pending');
        $order = $data['order'];

        $mutation = <<<'GQL'
            mutation CancelOrder($input: createCancelOrderInput!) {
                createCancelOrder(input: $input) {
                    cancelOrder {
                        success
                        message
                        status
                        orderId
                    }
                }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['orderId' => (int) $order->id],
        ]);

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL errors: '.json_encode($json['errors']));
        }

        $response->assertOk()
            ->assertJsonPath('data.createCancelOrder.cancelOrder.success', true)
            ->assertJsonPath('data.createCancelOrder.cancelOrder.status', 'canceled');
    }

    public function test_customer_cannot_cancel_an_order_that_does_not_belong_to_them(): void
    {
        $customer1 = $this->createCustomer();
        $customer2 = $this->createCustomer();
        $data = $this->createTestOrder($customer2);

        $mutation = <<<'GQL'
            mutation CancelOrder($input: createCancelOrderInput!) {
                createCancelOrder(input: $input) {
                    cancelOrder { success }
                }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer1, $mutation, [
            'input' => ['orderId' => (int) $data['order']->id],
        ]);

        $this->assertNotEmpty($response->json('errors'));
    }

    public function test_customer_can_reorder_items_from_a_previous_order(): void
    {
        $customer = $this->createCustomer();
        $data = $this->createTestOrder($customer, 'completed');

        $mutation = <<<'GQL'
            mutation Reorder($input: createReorderOrderInput!) {
                createReorderOrder(input: $input) {
                    reorderOrder {
                        success
                        message
                        orderId
                        itemsAddedCount
                    }
                }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['orderId' => (int) $data['order']->id],
        ]);

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL errors: '.json_encode($json['errors']));
        }

        $reorder = $json['data']['createReorderOrder']['reorderOrder'] ?? null;
        $this->assertNotNull($reorder, 'reorderOrder is null. Response: '.json_encode($json));
        $this->assertTrue($reorder['success'], 'Reorder should succeed. Message: '.($reorder['message'] ?? 'none'));
        $this->assertGreaterThanOrEqual(1, $reorder['itemsAddedCount']);
    }
}
