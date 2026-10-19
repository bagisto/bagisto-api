<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

class CustomerOrderCancelTest extends GraphQLTestCase
{
    // ── Cancel Order Mutation Tests ────────────────────────────────────

    /**
     * Test: Cancel a pending order successfully.
     */
    public function test_cancel_pending_order_success(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation CancelOrder($input: createCancelOrderInput!) {
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
                'createCancelOrder' => [
                    'cancelOrder' => [
                        'success' => true,
                        'orderId' => $testData['order']->id,
                    ],
                ],
            ],
        ]);

        $testData['order']->refresh();
        $this->assertEquals('canceled', $testData['order']->status);
    }

    /**
     * Test: Cannot cancel a completed order.
     */
    public function test_cannot_cancel_completed_order(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation CancelOrder($input: createCancelOrderInput!) {
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

        $data = $response->json();
        $this->assertEquals(false, $data['data']['createCancelOrder']['cancelOrder']['success']);
    }

    /**
     * Test: Cannot cancel non-existent order.
     */
    public function test_cannot_cancel_non_existent_order(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation CancelOrder($input: createCancelOrderInput!) {
              createCancelOrder(input: $input) {
                cancelOrder {
                  success
                  message
                  orderId
                }
              }
            }
        GQL;

        $variables = [
            'input' => [
                'orderId' => 99999,
            ],
        ];

        $response = $this->authenticatedGraphQL(
            $testData['customer'],
            $mutation,
            $variables
        );

        $this->assertArrayHasKey('errors', $response->json());
    }

    /**
     * Test: Cannot cancel order of another customer.
     */
    public function test_cannot_cancel_other_customers_order(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation CancelOrder($input: createCancelOrderInput!) {
              createCancelOrder(input: $input) {
                cancelOrder {
                  success
                  message
                  orderId
                }
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

        $this->assertArrayHasKey('errors', $response->json());
    }

    /**
     * Test: Missing order ID should return error.
     */
    public function test_cancel_without_order_id(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation CancelOrder($input: createCancelOrderInput!) {
              createCancelOrder(input: $input) {
                cancelOrder {
                  success
                  message
                }
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

        $this->assertArrayHasKey('errors', $response->json());
    }

    /**
     * Test: Unauthenticated cancel request fails.
     */
    public function test_unauthenticated_cancel_fails(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation CancelOrder($input: createCancelOrderInput!) {
              createCancelOrder(input: $input) {
                cancelOrder {
                  success
                  message
                }
              }
            }
        GQL;

        $variables = [
            'input' => [
                'orderId' => $testData['order']->id,
            ],
        ];

        $response = $this->graphQL($mutation, $variables);

        $data = $response->json();
        $this->assertArrayHasKey('errors', $data);
        $this->assertTrue(
            isset($data['errors'][0]['message']),
            'Expected GraphQL error message to be present'
        );
    }

    /**
     * Test: Cancel order response includes all expected fields.
     */
    public function test_cancel_response_includes_all_fields(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation CancelOrder($input: createCancelOrderInput!) {
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
        $this->assertArrayHasKey('createCancelOrder', $data['data']);
        $this->assertArrayHasKey('cancelOrder', $data['data']['createCancelOrder']);
        $this->assertArrayHasKey('success', $data['data']['createCancelOrder']['cancelOrder']);
        $this->assertArrayHasKey('message', $data['data']['createCancelOrder']['cancelOrder']);
        $this->assertArrayHasKey('orderId', $data['data']['createCancelOrder']['cancelOrder']);
        $this->assertArrayHasKey('status', $data['data']['createCancelOrder']['cancelOrder']);
    }

    /**
     * Create test data — customer with cancellable order.
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create();

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'channel_id' => $channel->id,
            'status' => 'pending',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sku' => 'TEST-SKU-CANCEL-001',
            'type' => 'simple',
            'name' => 'Test Product for Cancel',
            'qty_ordered' => 1,
        ]);

        OrderPayment::factory()->create([
            'order_id' => $order->id,
        ]);

        $completedOrder = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'channel_id' => $channel->id,
            'status' => 'completed',
        ]);

        OrderItem::factory()->create([
            'order_id' => $completedOrder->id,
            'product_id' => $product->id,
            'sku' => 'TEST-SKU-COMPLETED',
            'type' => 'simple',
            'name' => 'Completed Order Product',
            'qty_ordered' => 1,
            'qty_invoiced' => 1,
        ]);

        OrderPayment::factory()->create([
            'order_id' => $completedOrder->id,
        ]);

        return compact('customer', 'order', 'completedOrder', 'channel', 'product');
    }
}
