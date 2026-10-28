<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\Refund;
use Webkul\Sales\Models\RefundItem;

class CustomerRefundTest extends RestApiTestCase
{
    /**
     * A customer with one order carrying two refunds.
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();

        $order = $this->createOrderFor($customer);

        $refund1 = $this->createRefund($order, 'REF-SKU-001', 60.00);

        $refund2 = $this->createRefund($order, 'REF-SKU-002', 25.00);

        return compact('customer', 'order', 'refund1', 'refund2');
    }

    /**
     * Create a completed order owned by the given customer.
     */
    private function createOrderFor(Customer $customer): Order
    {
        return Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'channel_id' => Channel::first()->id,
            'status' => 'closed',
            'order_currency_code' => 'USD',
        ]);
    }

    /**
     * Create a refund with one refunded item against the given order.
     */
    private function createRefund(Order $order, string $sku, float $amount): Refund
    {
        $product = Product::factory()->create();

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sku' => $sku,
            'type' => 'simple',
            'name' => 'Refunded Product '.$sku,
        ]);

        $refund = Refund::create([
            'order_id' => $order->id,
            'state' => 'refunded',
            'total_qty' => 2,
            'base_currency_code' => 'USD',
            'channel_currency_code' => 'USD',
            'order_currency_code' => 'USD',
            'sub_total' => $amount - 10,
            'base_sub_total' => $amount - 10,
            'sub_total_incl_tax' => $amount - 10,
            'base_sub_total_incl_tax' => $amount - 10,
            'shipping_amount' => 5.00,
            'base_shipping_amount' => 5.00,
            'tax_amount' => 5.00,
            'base_tax_amount' => 5.00,
            'discount_amount' => 0.00,
            'base_discount_amount' => 0.00,
            'adjustment_refund' => 2.00,
            'base_adjustment_refund' => 2.00,
            'adjustment_fee' => 2.00,
            'base_adjustment_fee' => 2.00,
            'grand_total' => $amount,
            'base_grand_total' => $amount,
        ]);

        RefundItem::create([
            'refund_id' => $refund->id,
            'order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'name' => $orderItem->name,
            'sku' => $sku,
            'qty' => 2,
            'price' => ($amount - 10) / 2,
            'base_price' => ($amount - 10) / 2,
            'total' => $amount - 10,
            'base_total' => $amount - 10,
            'tax_amount' => 5.00,
            'base_tax_amount' => 5.00,
            'discount_amount' => 0.00,
            'base_discount_amount' => 0.00,
        ]);

        return $refund;
    }

    /**
     * Test: the collection lists the customer's refunds with their items.
     */
    public function test_get_customer_refunds_collection(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-refunds');

        $response->assertOk();
        $json = $response->json();

        expect($json)->toBeArray();
        expect(count($json))->toBe(2);
        expect($json[0]['id'])->toBe($testData['refund2']->id);
        expect($json[0]['items'])->toHaveCount(1);
        expect($json[0]['items'][0]['sku'])->toBe('REF-SKU-002');
    }

    /**
     * Test: ?order_id= scopes the collection to one order.
     */
    public function test_filter_refunds_by_order_id(): void
    {
        $testData = $this->createTestData();

        $otherOrder = $this->createOrderFor($testData['customer']);
        $this->createRefund($otherOrder, 'REF-SKU-003', 40.00);

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-refunds?order_id='.$otherOrder->id
        );

        $response->assertOk();
        $json = $response->json();

        expect(count($json))->toBe(1);
        expect($json[0]['orderId'])->toBe($otherOrder->id);
    }

    /**
     * Test: per_page limits the page size.
     */
    public function test_per_page_limits_collection(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-refunds?per_page=1');

        $response->assertOk();

        expect(count($response->json()))->toBe(1);
    }

    /**
     * Test: another customer's refunds are never listed.
     */
    public function test_customer_only_sees_own_refunds(): void
    {
        $testData = $this->createTestData();

        $otherCustomer = $this->createCustomer();
        $this->createRefund($this->createOrderFor($otherCustomer), 'REF-SKU-OTHER', 99.00);

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-refunds');

        $response->assertOk();

        expect(collect($response->json())->pluck('id')->all())
            ->toEqualCanonicalizing([$testData['refund1']->id, $testData['refund2']->id]);
    }

    /**
     * Test: the collection requires a customer token.
     */
    public function test_get_customer_refunds_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/customer-refunds');

        expect($response->getStatusCode())->toBeIn([401, 403]);
    }

    /**
     * Test: the detail returns totals, formatted values and items.
     */
    public function test_get_single_customer_refund(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-refunds/'.$testData['refund1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json['id'])->toBe($testData['refund1']->id);
        expect($json['orderId'])->toBe($testData['order']->id);
        expect($json['orderIncrementId'])->toBe((string) $testData['order']->increment_id);
        expect($json['state'])->toBe('refunded');
        expect($json['orderCurrencyCode'])->toBe('USD');
        expect((float) $json['grandTotal'])->toBe(60.0);
        expect((float) $json['adjustmentRefund'])->toBe(2.0);
        expect((float) $json['adjustmentFee'])->toBe(2.0);
        expect($json['formattedGrandTotal'])->not()->toBeNull();
        expect($json)->toHaveKeys([
            'baseGrandTotal', 'subTotal', 'subTotalInclTax', 'taxAmount', 'discountAmount',
            'shippingAmount', 'baseCurrencyCode', 'createdAt',
        ]);

        expect($json['items'])->toHaveCount(1);
        expect($json['items'][0]['sku'])->toBe('REF-SKU-001');
        expect($json['items'][0]['qty'])->toBe(2);
        expect((float) $json['items'][0]['total'])->toBe(50.0);
        expect($json['items'][0]['formattedPrice'])->not()->toBeNull();
    }

    /**
     * Test: another customer's refund answers 404.
     */
    public function test_cannot_access_other_customers_refund(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $response = $this->authenticatedGet(
            $otherCustomer,
            '/api/shop/customer-refunds/'.$testData['refund1']->id
        );

        $response->assertNotFound();
    }

    /**
     * Test: an unknown refund answers 404.
     */
    public function test_get_customer_refund_not_found(): void
    {
        $this->seedRequiredData();

        $response = $this->authenticatedGet($this->createCustomer(), '/api/shop/customer-refunds/999999');

        $response->assertNotFound();
    }

    /**
     * Test: the detail requires a customer token.
     */
    public function test_get_single_refund_requires_auth(): void
    {
        $testData = $this->createTestData();

        $response = $this->publicGet('/api/shop/customer-refunds/'.$testData['refund1']->id);

        expect($response->getStatusCode())->toBeIn([401, 403]);
    }
}
