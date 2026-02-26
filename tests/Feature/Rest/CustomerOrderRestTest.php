<?php

namespace Tests\Feature\BagistoApi\Rest;

use Tests\Feature\BagistoApi\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

class CustomerOrderRestTest extends RestApiTestCase
{
    /**
     * Create test data — customer with orders
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $channel  = Channel::first();
        $product  = Product::factory()->create();

        $order1 = Order::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'pending',
        ]);

        OrderItem::factory()->create([
            'order_id'   => $order1->id,
            'product_id' => $product->id,
            'sku'        => 'TEST-SKU-001',
            'type'       => 'simple',
            'name'       => 'Test Product One',
        ]);

        OrderPayment::factory()->create([
            'order_id' => $order1->id,
        ]);

        $order2 = Order::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'completed',
        ]);

        OrderItem::factory()->create([
            'order_id'   => $order2->id,
            'product_id' => $product->id,
            'sku'        => 'TEST-SKU-002',
            'type'       => 'simple',
            'name'       => 'Test Product Two',
        ]);

        OrderPayment::factory()->create([
            'order_id' => $order2->id,
        ]);

        return compact('customer', 'channel', 'product', 'order1', 'order2');
    }

    // ── Collection ────────────────────────────────────────────

    /**
     * Test: GET /api/shop/customer-orders returns collection
     */
    public function test_get_customer_orders_collection(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-orders');

        $response->assertOk();
        $json = $response->json();

        expect($json)->toBeArray();
        expect(count($json))->toBeGreaterThanOrEqual(2);
    }

    /**
     * Test: GET /api/shop/customer-orders without auth returns error
     */
    public function test_get_customer_orders_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/customer-orders');

        expect(in_array($response->getStatusCode(), [401, 403, 500]))->toBeTrue();
    }

    /**
     * Test: Customer only sees own orders
     */
    public function test_customer_only_sees_own_orders(): void
    {
        $testData = $this->createTestData();

        /** Create another customer with their own order */
        $otherCustomer = $this->createCustomer();
        $channel = Channel::first();

        Order::factory()->create([
            'customer_id'         => $otherCustomer->id,
            'customer_email'      => $otherCustomer->email,
            'customer_first_name' => $otherCustomer->first_name,
            'customer_last_name'  => $otherCustomer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'pending',
        ]);

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-orders');

        $response->assertOk();
        $json = $response->json();

        /** Should only see the 2 orders belonging to testData customer */
        expect(count($json))->toBe(2);
    }

    /**
     * Test: Customer with no orders returns empty collection
     */
    public function test_customer_with_no_orders_returns_empty(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/customer-orders');

        $response->assertOk();
        $json = $response->json();

        expect($json)->toBeArray();
        expect(count($json))->toBe(0);
    }

    // ── Single Item ───────────────────────────────────────────

    /**
     * Test: GET /api/shop/customer-orders/{id} returns single order
     */
    public function test_get_single_customer_order(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-orders/'.$testData['order1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json)->toHaveKey('id');
        expect($json)->toHaveKey('incrementId');
        expect($json)->toHaveKey('status');
        expect($json)->toHaveKey('customerEmail');
        expect($json)->toHaveKey('customerFirstName');
        expect($json)->toHaveKey('customerLastName');
        expect($json)->toHaveKey('grandTotal');
        expect($json)->toHaveKey('subTotal');
        expect($json)->toHaveKey('shippingMethod');
        expect($json)->toHaveKey('shippingTitle');
        expect($json)->toHaveKey('baseCurrencyCode');
        expect($json)->toHaveKey('orderCurrencyCode');
        expect($json)->toHaveKey('totalItemCount');
        expect($json)->toHaveKey('totalQtyOrdered');
        expect($json)->toHaveKey('createdAt');
        expect($json['id'])->toBe($testData['order1']->id);
        expect($json['status'])->toBe('pending');
        expect($json['customerEmail'])->toBe($testData['customer']->email);
    }

    /**
     * Test: GET /api/shop/customer-orders/{id} with invalid id returns 404
     */
    public function test_get_customer_order_not_found(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/customer-orders/999999');

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    /**
     * Test: Cannot access another customer's order by ID
     */
    public function test_cannot_access_other_customers_order(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $response = $this->authenticatedGet(
            $otherCustomer,
            '/api/shop/customer-orders/'.$testData['order1']->id
        );

        /** Should return 404/500 because the order doesn't belong to otherCustomer */
        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    /**
     * Test: Single order without auth returns error
     */
    public function test_get_single_order_requires_auth(): void
    {
        $testData = $this->createTestData();

        $response = $this->publicGet(
            '/api/shop/customer-orders/'.$testData['order1']->id
        );

        expect(in_array($response->getStatusCode(), [401, 403, 500]))->toBeTrue();
    }

    // ── Response Shape ────────────────────────────────────────

    /**
     * Test: Order response includes financial fields
     */
    public function test_order_response_includes_financial_fields(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-orders/'.$testData['order1']->id
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
     * Test: Collection returns orders with correct statuses
     */
    public function test_collection_returns_orders_with_correct_statuses(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-orders');

        $response->assertOk();
        $json = $response->json();

        $statuses = array_column($json, 'status');

        expect($statuses)->toContain('pending');
        expect($statuses)->toContain('completed');
    }
}
