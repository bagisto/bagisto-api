<?php

namespace Webkul\BagistoApi\Tests\Feature\Rest;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\InvoiceItem;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

class CustomerInvoiceRestTest extends RestApiTestCase
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
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'completed',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'sku'        => 'TEST-INV-SKU-001',
            'type'       => 'simple',
            'name'       => 'Test Invoice Product',
        ]);

        OrderPayment::factory()->create([
            'order_id' => $order->id,
        ]);

        $invoice1 = Invoice::factory()->create([
            'order_id'             => $order->id,
            'state'                => 'paid',
            'total_qty'            => 2,
            'sub_total'            => 100.00,
            'base_sub_total'       => 100.00,
            'grand_total'          => 110.00,
            'base_grand_total'     => 110.00,
            'shipping_amount'      => 5.00,
            'base_shipping_amount' => 5.00,
            'tax_amount'           => 5.00,
            'base_tax_amount'      => 5.00,
            'discount_amount'      => 0.00,
            'base_discount_amount' => 0.00,
            'base_currency_code'   => 'USD',
            'order_currency_code'  => 'USD',
            'increment_id'         => 'INV-REST-001',
        ]);

        InvoiceItem::factory()->create([
            'invoice_id'    => $invoice1->id,
            'order_item_id' => $orderItem->id,
            'name'          => 'Test Invoice Product',
            'sku'           => 'TEST-INV-SKU-001',
            'qty'           => 2,
            'price'         => 50.00,
            'base_price'    => 50.00,
            'total'         => 100.00,
            'base_total'    => 100.00,
        ]);

        $invoice2 = Invoice::factory()->create([
            'order_id'             => $order->id,
            'state'                => 'pending',
            'total_qty'            => 1,
            'sub_total'            => 50.00,
            'base_sub_total'       => 50.00,
            'grand_total'          => 55.00,
            'base_grand_total'     => 55.00,
            'shipping_amount'      => 3.00,
            'base_shipping_amount' => 3.00,
            'tax_amount'           => 2.00,
            'base_tax_amount'      => 2.00,
            'discount_amount'      => 0.00,
            'base_discount_amount' => 0.00,
            'base_currency_code'   => 'USD',
            'order_currency_code'  => 'USD',
            'increment_id'         => 'INV-REST-002',
        ]);

        InvoiceItem::factory()->create([
            'invoice_id'    => $invoice2->id,
            'order_item_id' => $orderItem->id,
            'name'          => 'Test Invoice Product',
            'sku'           => 'TEST-INV-SKU-001',
            'qty'           => 1,
            'price'         => 50.00,
            'base_price'    => 50.00,
            'total'         => 50.00,
            'base_total'    => 50.00,
        ]);

        return compact('customer', 'channel', 'product', 'order', 'orderItem', 'invoice1', 'invoice2');
    }

    // ── Collection ────────────────────────────────────────────

    /**
     * Test: GET /api/shop/customer-invoices returns collection
     */
    public function test_get_customer_invoices_collection(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-invoices');

        $response->assertOk();
        $json = $response->json();

        expect($json)->toBeArray();
        expect(count($json))->toBeGreaterThanOrEqual(2);
    }

    /**
     * Test: GET /api/shop/customer-invoices without auth returns error
     */
    public function test_get_customer_invoices_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/customer-invoices');

        expect(in_array($response->getStatusCode(), [401, 403, 500]))->toBeTrue();
    }

    /**
     * Test: Customer only sees invoices from own orders
     */
    public function test_customer_only_sees_own_invoices(): void
    {
        $testData = $this->createTestData();

        /** Create another customer with their own order and invoice */
        $otherCustomer = $this->createCustomer();
        $channel = Channel::first();

        $otherOrder = Order::factory()->create([
            'customer_id'         => $otherCustomer->id,
            'customer_email'      => $otherCustomer->email,
            'customer_first_name' => $otherCustomer->first_name,
            'customer_last_name'  => $otherCustomer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'completed',
        ]);

        Invoice::factory()->create([
            'order_id'         => $otherOrder->id,
            'state'            => 'paid',
            'grand_total'      => 200.00,
            'base_grand_total' => 200.00,
        ]);

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-invoices');

        $response->assertOk();
        $json = $response->json();

        /** Should only see the 2 invoices belonging to testData customer's orders */
        expect(count($json))->toBe(2);
    }

    /**
     * Test: Customer with no invoices returns empty collection
     */
    public function test_customer_with_no_invoices_returns_empty(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/customer-invoices');

        $response->assertOk();
        $json = $response->json();

        expect($json)->toBeArray();
        expect(count($json))->toBe(0);
    }

    // ── Single Item ───────────────────────────────────────────

    /**
     * Test: GET /api/shop/customer-invoices/{id} returns single invoice
     */
    public function test_get_single_customer_invoice(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-invoices/'.$testData['invoice1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json)->toHaveKey('id');
        expect($json)->toHaveKey('incrementId');
        expect($json)->toHaveKey('state');
        expect($json)->toHaveKey('totalQty');
        expect($json)->toHaveKey('grandTotal');
        expect($json)->toHaveKey('subTotal');
        expect($json)->toHaveKey('shippingAmount');
        expect($json)->toHaveKey('taxAmount');
        expect($json)->toHaveKey('discountAmount');
        expect($json)->toHaveKey('baseCurrencyCode');
        expect($json)->toHaveKey('orderCurrencyCode');
        expect($json)->toHaveKey('createdAt');
        expect($json['id'])->toBe($testData['invoice1']->id);
        expect($json['state'])->toBe('paid');
    }

    /**
     * Test: GET /api/shop/customer-invoices/{id} with invalid id returns 404
     */
    public function test_get_customer_invoice_not_found(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/customer-invoices/999999');

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    /**
     * Test: Cannot access another customer's invoice by ID
     */
    public function test_cannot_access_other_customers_invoice(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $response = $this->authenticatedGet(
            $otherCustomer,
            '/api/shop/customer-invoices/'.$testData['invoice1']->id
        );

        /** Should return 404/500 because the invoice doesn't belong to otherCustomer */
        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    /**
     * Test: Single invoice without auth returns error
     */
    public function test_get_single_invoice_requires_auth(): void
    {
        $testData = $this->createTestData();

        $response = $this->publicGet(
            '/api/shop/customer-invoices/'.$testData['invoice1']->id
        );

        expect(in_array($response->getStatusCode(), [401, 403, 500]))->toBeTrue();
    }

    // ── Response Shape ────────────────────────────────────────

    /**
     * Test: Invoice response includes financial fields
     */
    public function test_invoice_response_includes_financial_fields(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-invoices/'.$testData['invoice1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json)->toHaveKey('grandTotal');
        expect($json)->toHaveKey('baseGrandTotal');
        expect($json)->toHaveKey('subTotal');
        expect($json)->toHaveKey('baseSubTotal');
        expect($json)->toHaveKey('taxAmount');
        expect($json)->toHaveKey('shippingAmount');
        expect($json)->toHaveKey('discountAmount');
    }

    /**
     * Test: Collection returns invoices with correct states
     */
    public function test_collection_returns_invoices_with_correct_states(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-invoices');

        $response->assertOk();
        $json = $response->json();

        $states = array_column($json, 'state');

        expect($states)->toContain('paid');
        expect($states)->toContain('pending');
    }

    // ── PDF Download ──────────────────────────────────────────

    /**
     * Test: PDF download endpoint returns PDF response
     */
    public function test_pdf_download_returns_pdf(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-invoices/'.$testData['invoice1']->id.'/pdf'
        );

        $response->assertOk();

        $contentType = $response->headers->get('Content-Type');
        expect($contentType)->toContain('pdf');
    }

    /**
     * Test: PDF download without auth returns error
     */
    public function test_pdf_download_requires_auth(): void
    {
        $testData = $this->createTestData();

        $response = $this->publicGet(
            '/api/shop/customer-invoices/'.$testData['invoice1']->id.'/pdf'
        );

        expect(in_array($response->getStatusCode(), [401, 403, 500]))->toBeTrue();
    }

    /**
     * Test: PDF download for non-existent invoice returns error
     */
    public function test_pdf_download_invoice_not_found(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/customer-invoices/999999/pdf');

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    /**
     * Test: Cannot download another customer's invoice PDF
     */
    public function test_cannot_download_other_customers_invoice_pdf(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $response = $this->authenticatedGet(
            $otherCustomer,
            '/api/shop/customer-invoices/'.$testData['invoice1']->id.'/pdf'
        );

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }
}
