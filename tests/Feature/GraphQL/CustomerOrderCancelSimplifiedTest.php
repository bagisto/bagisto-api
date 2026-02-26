<?php

namespace Tests\Feature\BagistoApi\GraphQL;

use Tests\Feature\BagistoApi\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

/**
 * Tests for customer order cancellation via GraphQL
 */
class CustomerOrderCancelSimplifiedTest extends GraphQLTestCase
{
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create();

        // Create a pending order that can be canceled
        $order = Order::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'pending',
        ]);

        OrderItem::factory()->create([
            'order_id'    => $order->id,
            'product_id'  => $product->id,
            'qty_ordered' => 1,
        ]);

        OrderPayment::factory()->create(['order_id' => $order->id]);

        return compact('customer', 'order', 'channel', 'product');
    }

    /**
     * Test: Cancel a pending order successfully
     */
    public function test_cancel_pending_order_success(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation CancelOrder($input: CancelOrderInput!) {
                createCancelOrder(input: $input) {
                    cancelOrder {
                        success
                        message
                        orderId
                        status
                    }
                }
            }
        GQL;

        $response = $this->authenticatedGraphQL(
            $testData['customer'],
            $mutation,
            ['input' => ['orderId' => $testData['order']->id]]
        );

        $response->assertJson([
            'data' => [
                'createCancelOrder' => [
                    'cancelOrder' => [
                        'success' => true,
                        'orderId' => $testData['order']->id,
                    ],
                ],
            ],
        ]);

        // Verify order was canceled
        $testData['order']->refresh();
        $this->assertEquals('canceled', $testData['order']->status);
    }

    /**
     * Test: Cannot cancel non-existent order
     */
    public function test_cannot_cancel_non_existent_order(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation CancelOrder($input: CancelOrderInput!) {
                createCancelOrder(input: $input) {
                    cancelOrder {
                        success
                        message
                    }
                }
            }
        GQL;

        $response = $this->authenticatedGraphQL(
            $testData['customer'],
            $mutation,
            ['input' => ['orderId' => 99999]]
        );

        // Should have errors
        $this->assertArrayHasKey('errors', $response->json());
    }

    /**
     * Test: Cannot cancel another customer's order
     */
    public function test_cannot_cancel_other_customers_order(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation CancelOrder($input: CancelOrderInput!) {
                createCancelOrder(input: $input) {
                    cancelOrder {
                        success
                    }
                }
            }
        GQL;

        $response = $this->authenticatedGraphQL(
            $otherCustomer,
            $mutation,
            ['input' => ['orderId' => $testData['order']->id]]
        );

        // Should have errors
        $this->assertArrayHasKey('errors', $response->json());
    }

    /**
     * Test: Unauthenticated cancel request fails
     */
    public function test_unauthenticated_cancel_fails(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $product = Product::factory()->create();
        $channel = Channel::first();

        $order = Order::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'pending',
        ]);

        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id]);
        OrderPayment::factory()->create(['order_id' => $order->id]);

        $mutation = <<<'GQL'
            mutation CancelOrder($input: CancelOrderInput!) {
                createCancelOrder(input: $input) {
                    cancelOrder {
                        success
                    }
                }
            }
        GQL;

        $response = $this->graphQL($mutation, ['input' => ['orderId' => $order->id]]);

        // Should have authentication errors
        $this->assertArrayHasKey('errors', $response->json());
    }

    /**
     * Test: Missing order ID parameter returns error
     */
    public function test_missing_order_id_parameter(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation CancelOrder($input: CancelOrderInput!) {
                createCancelOrder(input: $input) {
                    cancelOrder {
                        success
                    }
                }
            }
        GQL;

        $response = $this->authenticatedGraphQL(
            $customer,
            $mutation,
            ['input' => []]
        );

        // Should have validation errors
        $this->assertArrayHasKey('errors', $response->json());
    }
}
