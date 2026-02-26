<?php

namespace Tests\Feature\BagistoApi\GraphQL;

use Tests\Feature\BagistoApi\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

class CustomerOrderCancelTest extends GraphQLTestCase
{
    /**
     * Create test data — customer with cancellable order
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create();

        // Create an order in 'pending' status which can be canceled
        $order = Order::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'pending',
        ]);

        OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'sku'        => 'TEST-SKU-CANCEL-001',
            'type'       => 'simple',
            'name'       => 'Test Product for Cancel',
            'qty_ordered' => 1,
        ]);

        OrderPayment::factory()->create([
            'order_id' => $order->id,
        ]);

        // Create a completed order that cannot be canceled
        $completedOrder = Order::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'completed',
        ]);

        OrderItem::factory()->create([
            'order_id'   => $completedOrder->id,
            'product_id' => $product->id,
            'sku'        => 'TEST-SKU-COMPLETED',
            'type'       => 'simple',
            'name'       => 'Completed Order Product',
        ]);

        OrderPayment::factory()->create([
            'order_id' => $completedOrder->id,
        ]);

        return compact('customer', 'order', 'completedOrder', 'channel', 'product');
    }

    // ── Cancel Order Mutation Tests ────────────────────────────────────

    /**
     * Test: Cancel a pending order successfully
     */
    public function test_cancel_pending_order_success(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation cancelOrder($input: CancelOrderInput!) {
              cancelOrder(input: $input) {
                success
                message
                orderId
                status
              }
            }
        GQL;

        $variables = [
            'input' => [
                'orderId' => $testData['order']->id,
            ],
        ];

        $response = $this->authenticatedGraphQL(
            $testData['customer'],
            $mutation,
            $variables
        );

        $response->assertJson([
            'data' => [
                'cancelOrder' => [
                    'success' => true,
                    'orderId' => $testData['order']->id,
                ],
            ],
        ]);

        // Verify the order status changed to 'canceled'
        $testData['order']->refresh();
        $this->assertEquals('canceled', $testData['order']->status);
    }

    /**
     * Test: Cannot cancel a completed order
     */
    public function test_cannot_cancel_completed_order(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation cancelOrder($input: CancelOrderInput!) {
              cancelOrder(input: $input) {
                success
                message
                orderId
                status
              }
            }
        GQL;

        $variables = [
            'input' => [
                'orderId' => $testData['completedOrder']->id,
            ],
        ];

        $response = $this->authenticatedGraphQL(
            $testData['customer'],
            $mutation,
            $variables
        );

        // Should fail to cancel
        $this->assertEquals(false, $response['data']['cancelOrder']['success']);
    }

    /**
     * Test: Cannot cancel non-existent order
     */
    public function test_cannot_cancel_non_existent_order(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation cancelOrder($input: CancelOrderInput!) {
              cancelOrder(input: $input) {
                success
                message
                orderId
              }
            }
        GQL;

        $variables = [
            'input' => [
                'orderId' => 99999, // Non-existent ID
            ],
        ];

        $response = $this->authenticatedGraphQL(
            $testData['customer'],
            $mutation,
            $variables
        );

        // Should return an error
        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Cannot cancel order of another customer
     */
    public function test_cannot_cancel_other_customers_order(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation cancelOrder($input: CancelOrderInput!) {
              cancelOrder(input: $input) {
                success
                message
                orderId
              }
            }
        GQL;

        $variables = [
            'input' => [
                'orderId' => $testData['order']->id,
            ],
        ];

        $response = $this->authenticatedGraphQL(
            $otherCustomer,
            $mutation,
            $variables
        );

        // Should return an error
        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Missing order ID should return error
     */
    public function test_cancel_without_order_id(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation cancelOrder($input: CancelOrderInput!) {
              cancelOrder(input: $input) {
                success
                message
              }
            }
        GQL;

        $variables = [
            'input' => [],
        ];

        $response = $this->authenticatedGraphQL(
            $testData['customer'],
            $mutation,
            $variables
        );

        // Should return an error for missing orderId
        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Unauthenticated cancel request fails
     */
    public function test_unauthenticated_cancel_fails(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation cancelOrder($input: CancelOrderInput!) {
              cancelOrder(input: $input) {
                success
                message
              }
            }
        GQL;

        $variables = [
            'input' => [
                'orderId' => $testData['order']->id,
            ],
        ];

        // Call without authentication
        $response = $this->graphQL($mutation, $variables);

        // GraphQL returns response as array from postJson
        $data = $response->json();
        $this->assertArrayHasKey('errors', $data);
        $this->assertTrue(
            isset($data['errors'][0]['message']),
            'Expected GraphQL error message to be present'
        );
    }

    /**
     * Test: Cancel order response includes all expected fields
     */
    public function test_cancel_response_includes_all_fields(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation cancelOrder($input: CancelOrderInput!) {
              cancelOrder(input: $input) {
                success
                message
                orderId
                status
              }
            }
        GQL;

        $variables = [
            'input' => [
                'orderId' => $testData['order']->id,
            ],
        ];

        $response = $this->authenticatedGraphQL(
            $testData['customer'],
            $mutation,
            $variables
        );

        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('cancelOrder', $data['data']);
        $this->assertArrayHasKey('success', $data['data']['cancelOrder']);
        $this->assertArrayHasKey('message', $data['data']['cancelOrder']);
        $this->assertArrayHasKey('orderId', $data['data']['cancelOrder']);
        $this->assertArrayHasKey('status', $data['data']['cancelOrder']);
    }

}

