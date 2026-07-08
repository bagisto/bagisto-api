<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\InvoiceItem;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

class CustomerInvoiceTest extends GraphQLTestCase
{
    /**
     * Create test data — customer with order and invoices
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
            'status' => 'completed',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sku' => 'TEST-INV-SKU-001',
            'type' => 'simple',
            'name' => 'Test Invoice Product',
        ]);

        OrderPayment::factory()->create([
            'order_id' => $order->id,
        ]);

        /** Create first invoice (paid) */
        $invoice1 = Invoice::factory()->create([
            'order_id' => $order->id,
            'state' => 'paid',
            'total_qty' => 2,
            'sub_total' => 100.00,
            'base_sub_total' => 100.00,
            'grand_total' => 110.00,
            'base_grand_total' => 110.00,
            'shipping_amount' => 5.00,
            'base_shipping_amount' => 5.00,
            'tax_amount' => 5.00,
            'base_tax_amount' => 5.00,
            'discount_amount' => 0.00,
            'base_discount_amount' => 0.00,
            'base_currency_code' => 'USD',
            'order_currency_code' => 'USD',
            'increment_id' => 'INV-001',
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice1->id,
            'order_item_id' => $orderItem->id,
            'name' => 'Test Invoice Product',
            'sku' => 'TEST-INV-SKU-001',
            'qty' => 2,
            'price' => 50.00,
            'base_price' => 50.00,
            'total' => 100.00,
            'base_total' => 100.00,
        ]);

        /** Create second invoice (pending) */
        $invoice2 = Invoice::factory()->create([
            'order_id' => $order->id,
            'state' => 'pending',
            'total_qty' => 1,
            'sub_total' => 50.00,
            'base_sub_total' => 50.00,
            'grand_total' => 55.00,
            'base_grand_total' => 55.00,
            'shipping_amount' => 3.00,
            'base_shipping_amount' => 3.00,
            'tax_amount' => 2.00,
            'base_tax_amount' => 2.00,
            'discount_amount' => 0.00,
            'base_discount_amount' => 0.00,
            'base_currency_code' => 'USD',
            'order_currency_code' => 'USD',
            'increment_id' => 'INV-002',
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice2->id,
            'order_item_id' => $orderItem->id,
            'name' => 'Test Invoice Product',
            'sku' => 'TEST-INV-SKU-001',
            'qty' => 1,
            'price' => 50.00,
            'base_price' => 50.00,
            'total' => 50.00,
            'base_total' => 50.00,
        ]);

        return compact('customer', 'channel', 'product', 'order', 'orderItem', 'invoice1', 'invoice2');
    }

    // ── Collection Queries ────────────────────────────────────

    /**
     * Test: Query all customer invoices collection
     */
    public function test_get_customer_invoices_collection(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerInvoices {
              customerInvoices(first: 10) {
                edges {
                  cursor
                  node {
                    _id
                    incrementId
                    state
                    totalQty
                    grandTotal
                    baseGrandTotal
                    subTotal
                    baseSubTotal
                    shippingAmount
                    taxAmount
                    discountAmount
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
        $data = $response->json('data.customerInvoices');

        expect($data['totalCount'])->toBeGreaterThanOrEqual(2);
        expect($data['edges'])->not()->toBeEmpty();
    }

    /**
     * Test: Unauthenticated request returns error
     */
    public function test_get_customer_invoices_requires_authentication(): void
    {
        $query = <<<'GQL'
            query getCustomerInvoices {
              customerInvoices(first: 5) {
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
     * Test: Customer only sees invoices from their own orders
     */
    public function test_customer_only_sees_own_invoices(): void
    {
        $testData = $this->createTestData();

        /** Create another customer with their own order and invoice */
        $otherCustomer = $this->createCustomer();
        $channel = Channel::first();

        $otherOrder = Order::factory()->create([
            'customer_id' => $otherCustomer->id,
            'customer_email' => $otherCustomer->email,
            'customer_first_name' => $otherCustomer->first_name,
            'customer_last_name' => $otherCustomer->last_name,
            'channel_id' => $channel->id,
            'status' => 'completed',
        ]);

        Invoice::factory()->create([
            'order_id' => $otherOrder->id,
            'state' => 'paid',
            'grand_total' => 200.00,
            'base_grand_total' => 200.00,
        ]);

        $query = <<<'GQL'
            query getCustomerInvoices {
              customerInvoices(first: 50) {
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
        $data = $response->json('data.customerInvoices');

        /** Should only see the 2 invoices belonging to testData customer */
        expect($data['totalCount'])->toBe(2);
    }

    /**
     * Test: Filter invoices by orderId
     */
    public function test_filter_invoices_by_order_id(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerInvoices($orderId: Int) {
              customerInvoices(first: 10, orderId: $orderId) {
                edges {
                  node {
                    _id
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query, [
            'orderId' => $testData['order']->id,
        ]);

        $response->assertOk();
        $data = $response->json('data.customerInvoices');

        expect($data['totalCount'])->toBe(2);
    }

    /**
     * Test: Filter invoices by state
     */
    public function test_filter_invoices_by_state(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerInvoices($state: String) {
              customerInvoices(first: 10, state: $state) {
                edges {
                  node {
                    _id
                    state
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query, [
            'state' => 'paid',
        ]);

        $response->assertOk();
        $data = $response->json('data.customerInvoices');

        expect($data['totalCount'])->toBe(1);

        $node = $data['edges'][0]['node'];
        expect($node['state'])->toBe('paid');
    }

    /**
     * Test: Filter by pending state
     */
    public function test_filter_invoices_by_pending_state(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerInvoices($state: String) {
              customerInvoices(first: 10, state: $state) {
                edges {
                  node {
                    _id
                    state
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query, [
            'state' => 'pending',
        ]);

        $response->assertOk();
        $data = $response->json('data.customerInvoices');

        expect($data['totalCount'])->toBe(1);

        $node = $data['edges'][0]['node'];
        expect($node['state'])->toBe('pending');
    }

    // ── Single Item Query ─────────────────────────────────────

    /**
     * Test: Query single customer invoice by ID
     */
    public function test_get_customer_invoice_by_id(): void
    {
        $testData = $this->createTestData();
        $invoiceId = "/api/shop/customer-invoices/{$testData['invoice1']->id}";

        $query = <<<GQL
            query getCustomerInvoice {
              customerInvoice(id: "{$invoiceId}") {
                _id
                incrementId
                state
                totalQty
                grandTotal
                baseGrandTotal
                subTotal
                baseSubTotal
                shippingAmount
                baseShippingAmount
                taxAmount
                baseTaxAmount
                discountAmount
                baseDiscountAmount
                baseCurrencyCode
                orderCurrencyCode
                createdAt
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerInvoice');

        expect($data['_id'])->toBe($testData['invoice1']->id);
        expect($data['state'])->toBe('paid');
        expect($data['incrementId'])->toBe('INV-001');
    }

    /**
     * Test: Query invoice returns correct financial data
     */
    public function test_invoice_returns_financial_data(): void
    {
        $testData = $this->createTestData();
        $invoiceId = "/api/shop/customer-invoices/{$testData['invoice1']->id}";

        $query = <<<GQL
            query getCustomerInvoice {
              customerInvoice(id: "{$invoiceId}") {
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

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerInvoice');

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
     * Test: Query non-existent invoice returns error
     */
    public function test_get_nonexistent_invoice_returns_error(): void
    {
        $this->seedRequiredData();

        $query = <<<'GQL'
            query getCustomerInvoice {
              customerInvoice(id: "/api/shop/customer-invoices/99999") {
                _id
                state
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
            query getCustomerInvoices {
              customerInvoices(first: 1) {
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
        $data = $response->json('data.customerInvoices');

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
            query getCustomerInvoices {
              customerInvoices(first: 1) {
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

        $firstPageData = $response->json('data.customerInvoices');
        $endCursor = $firstPageData['pageInfo']['endCursor'];
        $firstInvoiceId = $firstPageData['edges'][0]['node']['_id'];

        /** Second page */
        $query2 = <<<GQL
            query getCustomerInvoices {
              customerInvoices(first: 1, after: "{$endCursor}") {
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

        $secondPageData = $response2->json('data.customerInvoices');
        $secondInvoiceId = $secondPageData['edges'][0]['node']['_id'] ?? null;

        /** Second page should have a different invoice */
        expect($secondInvoiceId)->not()->toBe($firstInvoiceId);
    }

    // ── Schema Introspection ──────────────────────────────────

    /**
     * Test: CustomerInvoice type has expected fields in schema
     */
    public function test_customer_invoice_schema_has_expected_fields(): void
    {
        $query = <<<'GQL'
            {
              __type(name: "CustomerInvoice") {
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
            ->and($type['name'])->toBe('CustomerInvoice');

        $fieldNames = array_column($type['fields'], 'name');

        expect($fieldNames)
            ->toContain('_id')
            ->toContain('incrementId')
            ->toContain('state')
            ->toContain('totalQty')
            ->toContain('grandTotal')
            ->toContain('subTotal')
            ->toContain('shippingAmount')
            ->toContain('taxAmount')
            ->toContain('discountAmount')
            ->toContain('baseCurrencyCode')
            ->toContain('orderCurrencyCode')
            ->toContain('createdAt');
    }
}
