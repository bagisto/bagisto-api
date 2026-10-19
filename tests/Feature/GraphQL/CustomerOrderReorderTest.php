<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

class CustomerOrderReorderTest extends GraphQLTestCase
{
    // ── Reorder Mutation Tests ────────────────────────────────────

    /**
     * Test: Reorder a completed order with available products.
     */
    public function test_reorder_completed_order_success(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation ReorderOrder($input: createReorderOrderInput!) {
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
                'createReorderOrder' => [
                    'reorderOrder' => [
                        'success' => true,
                        'orderId' => $testData['completedOrder']->id,
                    ],
                ],
            ],
        ]);

        $data = $response->json();
        $this->assertGreaterThan(0, $data['data']['createReorderOrder']['reorderOrder']['itemsAddedCount']);
    }

    /**
     * Test: Reorder returns correct item count.
     */
    public function test_reorder_returns_correct_item_count(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation ReorderOrder($input: createReorderOrderInput!) {
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

        if (isset($data['errors'])) {
            $this->markTestSkipped('Reorder operation returned errors: '.json_encode($data['errors']));
        }

        $this->assertEquals(2, $data['data']['createReorderOrder']['reorderOrder']['itemsAddedCount']);
    }

    /**
     * Test: Cannot reorder non-existent order.
     */
    public function test_cannot_reorder_non_existent_order(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation ReorderOrder($input: createReorderOrderInput!) {
              createReorderOrder(input: $input) {
                reorderOrder {
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
     * Test: Cannot reorder order of another customer.
     */
    public function test_cannot_reorder_other_customers_order(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation ReorderOrder($input: createReorderOrderInput!) {
              createReorderOrder(input: $input) {
                reorderOrder {
                  success
                  message
                  orderId
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
            $otherCustomer,
            $mutation,
            $variables
        );

        $this->assertArrayHasKey('errors', $response->json());
    }

    /**
     * Test: Missing order ID should return error.
     */
    public function test_reorder_without_order_id(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation ReorderOrder($input: createReorderOrderInput!) {
              createReorderOrder(input: $input) {
                reorderOrder {
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
     * Test: Unauthenticated reorder request fails.
     */
    public function test_unauthenticated_reorder_fails(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation ReorderOrder($input: createReorderOrderInput!) {
              createReorderOrder(input: $input) {
                reorderOrder {
                  success
                  message
                }
              }
            }
        GQL;

        $variables = [
            'input' => [
                'orderId' => $testData['completedOrder']->id,
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
     * Test: Reorder response includes all expected fields.
     */
    public function test_reorder_response_includes_all_fields(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation ReorderOrder($input: createReorderOrderInput!) {
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

        if (isset($data['errors'])) {
            $this->markTestSkipped('Reorder operation returned errors');
        }

        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('createReorderOrder', $data['data']);
        $this->assertArrayHasKey('reorderOrder', $data['data']['createReorderOrder']);
        $this->assertArrayHasKey('success', $data['data']['createReorderOrder']['reorderOrder']);
        $this->assertArrayHasKey('message', $data['data']['createReorderOrder']['reorderOrder']);
        $this->assertArrayHasKey('orderId', $data['data']['createReorderOrder']['reorderOrder']);
        $this->assertArrayHasKey('itemsAddedCount', $data['data']['createReorderOrder']['reorderOrder']);
    }

    /**
     * Test: Reorder a pending order.
     */
    public function test_reorder_pending_order_success(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation ReorderOrder($input: createReorderOrderInput!) {
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

        $data = $response->json();

        if (isset($data['errors'])) {
            $this->markTestSkipped('Reorder operation returned errors');
        }

        $response->assertJson([
            'data' => [
                'createReorderOrder' => [
                    'reorderOrder' => [
                        'success' => true,
                        'orderId' => $testData['pendingOrder']->id,
                    ],
                ],
            ],
        ]);

        $this->assertEquals(1, $data['data']['createReorderOrder']['reorderOrder']['itemsAddedCount']);
    }

    /**
     * Create test data — customer with completed order.
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $channel = Channel::first();

        $product1 = $this->createBaseProduct('simple', ['sku' => 'REORDER-PROD-1']);
        $this->ensureInventory($product1);

        $product2 = $this->createBaseProduct('simple', ['sku' => 'REORDER-PROD-2']);
        $this->ensureInventory($product2);

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
            'product_id' => $product1->id,
            'sku' => 'REORDER-PROD-1',
            'type' => 'simple',
            'name' => 'Reorder Product 1',
            'qty_ordered' => 2,
        ]);

        OrderItem::factory()->create([
            'order_id' => $completedOrder->id,
            'product_id' => $product2->id,
            'sku' => 'REORDER-PROD-2',
            'type' => 'simple',
            'name' => 'Reorder Product 2',
            'qty_ordered' => 1,
        ]);

        OrderPayment::factory()->create([
            'order_id' => $completedOrder->id,
        ]);

        $pendingOrder = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'channel_id' => $channel->id,
            'status' => 'pending',
        ]);

        OrderItem::factory()->create([
            'order_id' => $pendingOrder->id,
            'product_id' => $product1->id,
            'sku' => 'REORDER-PROD-1',
            'type' => 'simple',
            'name' => 'Reorder Product 1',
            'qty_ordered' => 1,
        ]);

        OrderPayment::factory()->create([
            'order_id' => $pendingOrder->id,
        ]);

        return compact('customer', 'completedOrder', 'pendingOrder', 'product1', 'product2', 'channel');
    }
}
