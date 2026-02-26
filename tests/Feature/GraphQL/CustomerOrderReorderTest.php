<?php

namespace Tests\Feature\BagistoApi\GraphQL;

use Tests\Feature\BagistoApi\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

class CustomerOrderReorderTest extends GraphQLTestCase
{
    /**
     * Create test data — customer with completed order
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $channel = Channel::first();
        
        // Create multiple products for testing
        $product1 = Product::factory()->create(['sku' => 'REORDER-PROD-1']);
        $product2 = Product::factory()->create(['sku' => 'REORDER-PROD-2']);

        // Create a completed order with multiple items
        $completedOrder = Order::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'completed',
        ]);

        OrderItem::factory()->create([
            'order_id'      => $completedOrder->id,
            'product_id'    => $product1->id,
            'sku'           => 'REORDER-PROD-1',
            'type'          => 'simple',
            'name'          => 'Reorder Product 1',
            'qty_ordered'   => 2,
        ]);

        OrderItem::factory()->create([
            'order_id'      => $completedOrder->id,
            'product_id'    => $product2->id,
            'sku'           => 'REORDER-PROD-2',
            'type'          => 'simple',
            'name'          => 'Reorder Product 2',
            'qty_ordered'   => 1,
        ]);

        OrderPayment::factory()->create([
            'order_id' => $completedOrder->id,
        ]);

        // Create a pending order (also reorderable)
        $pendingOrder = Order::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'pending',
        ]);

        OrderItem::factory()->create([
            'order_id'      => $pendingOrder->id,
            'product_id'    => $product1->id,
            'sku'           => 'REORDER-PROD-1',
            'type'          => 'simple',
            'name'          => 'Reorder Product 1',
            'qty_ordered'   => 1,
        ]);

        OrderPayment::factory()->create([
            'order_id' => $pendingOrder->id,
        ]);

        return compact('customer', 'completedOrder', 'pendingOrder', 'product1', 'product2', 'channel');
    }

    // ── Reorder Mutation Tests ────────────────────────────────────

    /**
     * Test: Reorder a completed order with available products
     */
    public function test_reorder_completed_order_success(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation reorderOrder($input: ReorderInput!) {
              reorderOrder(input: $input) {
                success
                message
                orderId
                itemsAddedCount
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

        $response->assertJson([
            'data' => [
                'reorderOrder' => [
                    'success' => true,
                    'orderId' => $testData['completedOrder']->id,
                ],
            ],
        ]);

        // Verify items were added to cart
        $this->assertGreaterThan(0, $response['data']['reorderOrder']['itemsAddedCount']);
    }

    /**
     * Test: Reorder returns correct item count
     */
    public function test_reorder_returns_correct_item_count(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation reorderOrder($input: ReorderInput!) {
              reorderOrder(input: $input) {
                success
                message
                orderId
                itemsAddedCount
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

        // The completed order has 2 items
        $itemsAddedCount = $response['data']['reorderOrder']['itemsAddedCount'];
        $this->assertEquals(2, $itemsAddedCount);
    }

    /**
     * Test: Cannot reorder non-existent order
     */
    public function test_cannot_reorder_non_existent_order(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation reorderOrder($input: ReorderInput!) {
              reorderOrder(input: $input) {
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
     * Test: Cannot reorder order of another customer
     */
    public function test_cannot_reorder_other_customers_order(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation reorderOrder($input: ReorderInput!) {
              reorderOrder(input: $input) {
                success
                message
                orderId
              }
            }
        GQL;

        $variables = [
            'input' => [
                'orderId' => $testData['completedOrder']->id,
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
    public function test_reorder_without_order_id(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation reorderOrder($input: ReorderInput!) {
              reorderOrder(input: $input) {
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
     * Test: Unauthenticated reorder request fails
     */
    public function test_unauthenticated_reorder_fails(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation reorderOrder($input: ReorderInput!) {
              reorderOrder(input: $input) {
                success
                message
              }
            }
        GQL;

        $variables = [
            'input' => [
                'orderId' => $testData['completedOrder']->id,
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
     * Test: Reorder response includes all expected fields
     */
    public function test_reorder_response_includes_all_fields(): void
    {
        $testData = $this->createTestData();
    }

    /**
     * Test: Reorder a pending order
     */
    public function test_reorder_pending_order_success(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation reorderOrder($input: ReorderInput!) {
              reorderOrder(input: $input) {
                success
                message
                orderId
                itemsAddedCount
              }
            }
        GQL;

        $variables = [
            'input' => [
                'orderId' => $testData['pendingOrder']->id,
            ],
        ];

        $response = $this->authenticatedGraphQL(
            $testData['customer'],
            $mutation,
            $variables
        );

        $response->assertJson([
            'data' => [
                'reorderOrder' => [
                    'success' => true,
                    'orderId' => $testData['pendingOrder']->id,
                ],
            ],
        ]);

        // Should have added 1 item
        $this->assertEquals(1, $response['data']['reorderOrder']['itemsAddedCount']);
    }
}

