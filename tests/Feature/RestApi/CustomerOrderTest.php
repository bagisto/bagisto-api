<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

class CustomerOrderTest extends RestApiTestCase
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

    // ── List trim — `CustomerOrderListDto` ────────────────────

    /**
     * Test: list response excludes relation IRIs (items / addresses / payment / shipments)
     * and admin-only fields (-invoiced, -refunded, customerType, cartId, etc).
     */
    public function test_list_response_excludes_relation_iris_and_admin_fields(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-orders');

        $response->assertOk();
        $first = $response->json(0);

        foreach (['items', 'addresses', 'payment', 'shipments'] as $relation) {
            expect($first)->not()->toHaveKey($relation);
        }

        foreach ([
            'grandTotalInvoiced', 'grandTotalRefunded',
            'subTotalInvoiced', 'subTotalRefunded',
            'discountInvoiced', 'discountRefunded',
            'taxAmountInvoiced', 'taxAmountRefunded',
            'shippingInvoiced', 'shippingRefunded',
            'subTotalInclTax', 'shippingAmountInclTax',
            'customerType', 'channelType',
            'cartId', 'appliedCartRuleIds',
            'customerFullName', 'isGift', 'shippingDescription', 'isGuest',
        ] as $adminField) {
            expect($first)->not()->toHaveKey($adminField);
        }
    }

    /**
     * Test: list response shape matches the GraphQL `customerOrders` field set exactly.
     */
    public function test_list_response_matches_graphql_shape(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-orders');

        $response->assertOk();
        $first = $response->json(0);

        $expectedKeys = [
            'id', 'incrementId', 'status', 'channelName',
            'customerEmail', 'customerFirstName', 'customerLastName',
            'shippingMethod', 'shippingTitle', 'couponCode',
            'totalItemCount', 'totalQtyOrdered',
            'grandTotal', 'baseGrandTotal',
            'subTotal', 'baseSubTotal',
            'taxAmount', 'shippingAmount', 'discountAmount',
            'baseCurrencyCode', 'orderCurrencyCode',
            'createdAt', 'updatedAt',
        ];

        foreach ($expectedKeys as $key) {
            expect($first)->toHaveKey($key);
        }

        expect(count($first))->toBe(count($expectedKeys));
    }

    // ── Detail embed — `CustomerOrderDetailDto` ───────────────

    /**
     * Test: detail response embeds items as objects (not IRI strings).
     */
    public function test_detail_response_embeds_items_inline(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-orders/'.$testData['order1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json['items'])->toBeArray();
        expect($json['items'])->not()->toBeEmpty();
        expect($json['items'][0])->toBeArray();
        expect($json['items'][0])->toHaveKey('id');
        expect($json['items'][0])->toHaveKey('sku');
        expect($json['items'][0])->toHaveKey('name');
        expect($json['items'][0])->toHaveKey('qtyOrdered');
        expect($json['items'][0])->toHaveKey('price');
        expect($json['items'][0])->toHaveKey('total');
        expect($json['items'][0]['sku'])->toBe('TEST-SKU-001');
    }

    /**
     * Test: detail response embeds addresses as objects.
     */
    public function test_detail_response_embeds_addresses_inline(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-orders/'.$testData['order1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json['addresses'])->toBeArray();

        if (! empty($json['addresses'])) {
            expect($json['addresses'][0])->toBeArray();
            expect($json['addresses'][0])->toHaveKey('id');
            expect($json['addresses'][0])->toHaveKey('addressType');
        }
    }

    /**
     * Test: detail response embeds payment as an object (not a string IRI).
     */
    public function test_detail_response_embeds_payment_inline(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-orders/'.$testData['order1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json)->toHaveKey('payment');
        expect($json['payment'])->toBeArray();
        expect($json['payment'])->toHaveKey('id');
        expect($json['payment'])->toHaveKey('method');
        expect($json['payment'])->toHaveKey('methodTitle');
    }

    /**
     * Test: detail response embeds shipments as an array of objects (with nested items).
     */
    public function test_detail_response_embeds_shipments_inline(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-orders/'.$testData['order1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json)->toHaveKey('shipments');
        expect($json['shipments'])->toBeArray();

        if (! empty($json['shipments'])) {
            expect($json['shipments'][0])->toBeArray();
            expect($json['shipments'][0])->toHaveKey('id');
            expect($json['shipments'][0])->toHaveKey('items');
            expect($json['shipments'][0]['items'])->toBeArray();
        }
    }

    /**
     * Test: detail response keeps every order-level field the legacy shape exposed.
     */
    public function test_detail_response_keeps_all_order_fields(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-orders/'.$testData['order1']->id
        );

        $response->assertOk();
        $json = $response->json();

        foreach ([
            'grandTotalInvoiced', 'grandTotalRefunded',
            'subTotalInvoiced', 'discountAmount', 'taxAmount',
            'shippingAmount', 'subTotalInclTax', 'shippingAmountInclTax',
            'customerId', 'channelId',
        ] as $field) {
            expect($json)->toHaveKey($field);
        }
    }
}
