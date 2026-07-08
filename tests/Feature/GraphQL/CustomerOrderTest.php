<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

class CustomerOrderTest extends GraphQLTestCase
{
    /**
     * Create test data — customer with orders
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create();

        $order1 = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'channel_id' => $channel->id,
            'status' => 'pending',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'sku' => 'TEST-SKU-001',
            'type' => 'simple',
            'name' => 'Test Product One',
        ]);

        OrderPayment::factory()->create([
            'order_id' => $order1->id,
        ]);

        $order2 = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'channel_id' => $channel->id,
            'status' => 'completed',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'sku' => 'TEST-SKU-002',
            'type' => 'simple',
            'name' => 'Test Product Two',
        ]);

        OrderPayment::factory()->create([
            'order_id' => $order2->id,
        ]);

        return compact('customer', 'channel', 'product', 'order1', 'order2');
    }

    // ── Collection Queries ────────────────────────────────────

    /**
     * Test: Query all customer orders collection
     */
    public function test_get_customer_orders_collection(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerOrders {
              customerOrders(first: 10) {
                edges {
                  cursor
                  node {
                    _id
                    incrementId
                    status
                    customerEmail
                    customerFirstName
                    customerLastName
                    grandTotal
                    subTotal
                    shippingMethod
                    shippingTitle
                    totalItemCount
                    totalQtyOrdered
                    baseCurrencyCode
                    orderCurrencyCode
                    createdAt
                  }
                }
                pageInfo {
                  endCursor
                  startCursor
                  hasNextPage
                  hasPreviousPage
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerOrders');

        expect($data['totalCount'])->toBeGreaterThanOrEqual(2);
        expect($data['edges'])->not()->toBeEmpty();
    }

    /**
     * Test: Unauthenticated request returns error
     */
    public function test_get_customer_orders_requires_authentication(): void
    {
        $query = <<<'GQL'
            query getCustomerOrders {
              customerOrders(first: 5) {
                edges {
                  node {
                    _id
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $errors = $response->json('errors');

        expect($errors)->not()->toBeEmpty();
    }

    /**
     * Test: Customer only sees their own orders
     */
    public function test_customer_only_sees_own_orders(): void
    {
        $testData = $this->createTestData();

        /** Create another customer with their own order */
        $otherCustomer = $this->createCustomer();
        $channel = Channel::first();

        Order::factory()->create([
            'customer_id' => $otherCustomer->id,
            'customer_email' => $otherCustomer->email,
            'customer_first_name' => $otherCustomer->first_name,
            'customer_last_name' => $otherCustomer->last_name,
            'channel_id' => $channel->id,
            'status' => 'pending',
        ]);

        $query = <<<'GQL'
            query getCustomerOrders {
              customerOrders(first: 50) {
                edges {
                  node {
                    _id
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerOrders');

        /** Should only see the 2 orders belonging to testData customer */
        expect($data['totalCount'])->toBe(2);
    }

    /**
     * Test: Filter orders by status
     */
    public function test_filter_orders_by_status(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerOrders($status: String) {
              customerOrders(first: 10, status: $status) {
                edges {
                  node {
                    _id
                    status
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query, [
            'status' => 'pending',
        ]);

        $response->assertOk();
        $data = $response->json('data.customerOrders');

        expect($data['totalCount'])->toBe(1);

        $node = $data['edges'][0]['node'];
        expect($node['status'])->toBe('pending');
    }

    /**
     * Test: Filter by completed status
     */
    public function test_filter_orders_by_completed_status(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerOrders($status: String) {
              customerOrders(first: 10, status: $status) {
                edges {
                  node {
                    _id
                    status
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query, [
            'status' => 'completed',
        ]);

        $response->assertOk();
        $data = $response->json('data.customerOrders');

        expect($data['totalCount'])->toBe(1);

        $node = $data['edges'][0]['node'];
        expect($node['status'])->toBe('completed');
    }

    // ── Single Item Query ─────────────────────────────────────

    /**
     * Test: Query single customer order by ID
     */
    public function test_get_customer_order_by_id(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order1']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                incrementId
                status
                customerEmail
                customerFirstName
                customerLastName
                grandTotal
                subTotal
                shippingMethod
                shippingTitle
                baseCurrencyCode
                orderCurrencyCode
                totalItemCount
                totalQtyOrdered
                createdAt
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        expect($data['_id'])->toBe($testData['order1']->id);
        expect($data['status'])->toBe('pending');
        expect($data['customerEmail'])->toBe($testData['customer']->email);
        expect($data['customerFirstName'])->toBe($testData['customer']->first_name);
        expect($data['customerLastName'])->toBe($testData['customer']->last_name);
    }

    /**
     * Test: Query single customer order by numeric ID
     */
    public function test_get_customer_order_by_numeric_id(): void
    {
        $testData = $this->createTestData();
        $orderId = (string) $testData['order1']->id;

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                status
                customerEmail
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        expect($data['_id'])->toBe($testData['order1']->id);
        expect($data['status'])->toBe('pending');
        expect($data['customerEmail'])->toBe($testData['customer']->email);
    }

    /**
     * Test: Invalid customer order ID format returns validation error
     */
    public function test_get_customer_order_with_invalid_id_format_returns_error(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerOrder {
              customerOrder(id: "invalid-id") {
                _id
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();

        $errors = $response->json('errors');
        $message = $errors[0]['message'] ?? '';

        expect($errors)->not()->toBeEmpty();
        expect($message)->toContain('Invalid ID format');
    }

    /**
     * Test: Query order returns correct financial data
     */
    public function test_order_returns_financial_data(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order1']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                grandTotal
                baseGrandTotal
                subTotal
                baseSubTotal
                taxAmount
                shippingAmount
                discountAmount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        expect($data)->toHaveKeys([
            '_id',
            'grandTotal',
            'baseGrandTotal',
            'subTotal',
            'baseSubTotal',
            'taxAmount',
            'shippingAmount',
            'discountAmount',
        ]);
    }

    /**
     * Test: Query non-existent order returns error
     */
    public function test_get_nonexistent_order_returns_error(): void
    {
        $this->seedRequiredData();

        $query = <<<'GQL'
            query getCustomerOrder {
              customerOrder(id: "/api/shop/customer-orders/99999") {
                _id
                status
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $errors = $response->json('errors');

        expect($errors)->not()->toBeEmpty();
    }

    // ── Pagination ────────────────────────────────────────────

    /**
     * Test: Pagination with first parameter
     */
    public function test_pagination_with_first_parameter(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerOrders {
              customerOrders(first: 1) {
                edges {
                  cursor
                  node {
                    _id
                  }
                }
                pageInfo {
                  endCursor
                  hasNextPage
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerOrders');

        expect(count($data['edges']))->toBe(1);
        expect($data['totalCount'])->toBeGreaterThanOrEqual(2);
    }

    /**
     * Test: Forward pagination with after cursor
     */
    public function test_pagination_with_after_cursor(): void
    {
        $testData = $this->createTestData();

        /** First page */
        $query = <<<'GQL'
            query getCustomerOrders {
              customerOrders(first: 1) {
                edges {
                  cursor
                  node {
                    _id
                  }
                }
                pageInfo {
                  endCursor
                  hasNextPage
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        $firstPageData = $response->json('data.customerOrders');
        $endCursor = $firstPageData['pageInfo']['endCursor'];
        $firstOrderId = $firstPageData['edges'][0]['node']['_id'];

        /** Second page */
        $query2 = <<<GQL
            query getCustomerOrders {
              customerOrders(first: 1, after: "{$endCursor}") {
                edges {
                  node {
                    _id
                  }
                }
              }
            }
        GQL;

        $response2 = $this->authenticatedGraphQL($testData['customer'], $query2);
        $response2->assertOk();

        $secondPageData = $response2->json('data.customerOrders');
        $secondOrderId = $secondPageData['edges'][0]['node']['_id'] ?? null;

        /** Second page should have a different order */
        expect($secondOrderId)->not()->toBe($firstOrderId);
    }

    // ── Schema Introspection ──────────────────────────────────

    /**
     * Test: CustomerOrder type has expected fields in schema
     */
    public function test_customer_order_schema_has_expected_fields(): void
    {
        $query = <<<'GQL'
            {
              __type(name: "CustomerOrder") {
                name
                fields {
                  name
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $type = $response->json('data.__type');

        expect($type)->not->toBeNull()
            ->and($type['name'])->toBe('CustomerOrder');

        $fieldNames = array_column($type['fields'], 'name');

        expect($fieldNames)
            ->toContain('_id')
            ->toContain('incrementId')
            ->toContain('status')
            ->toContain('customerEmail')
            ->toContain('customerFirstName')
            ->toContain('customerLastName')
            ->toContain('grandTotal')
            ->toContain('subTotal')
            ->toContain('shippingMethod')
            ->toContain('shippingTitle')
            ->toContain('totalItemCount')
            ->toContain('totalQtyOrdered')
            ->toContain('baseCurrencyCode')
            ->toContain('orderCurrencyCode')
            ->toContain('createdAt');
    }
}
