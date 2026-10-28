<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\Refund;
use Webkul\Sales\Models\RefundItem;

class CustomerRefundTest extends GraphQLTestCase
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
     * Test: the collection lists the customer's refunds with multi-word fields and items.
     */
    public function test_get_customer_refunds_collection(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerRefunds {
              customerRefunds(first: 10) {
                totalCount
                edges {
                  cursor
                  node {
                    _id
                    orderId
                    orderIncrementId
                    state
                    orderCurrencyCode
                    grandTotal
                    baseGrandTotal
                    subTotalInclTax
                    adjustmentRefund
                    adjustmentFee
                    formattedGrandTotal
                    createdAt
                    items {
                      edges {
                        node {
                          _id
                          sku
                          name
                          qty
                          price
                          total
                          taxAmount
                          formattedPrice
                          formattedTotal
                        }
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.customerRefunds');

        expect($data['totalCount'])->toBe(2);

        $node = $data['edges'][0]['node'];

        expect($node['_id'])->toBe($testData['refund2']->id);
        expect($node['orderId'])->toBe($testData['order']->id);
        expect($node['orderIncrementId'])->toBe((string) $testData['order']->increment_id);
        expect((float) $node['grandTotal'])->toBe(25.0);
        expect($node['formattedGrandTotal'])->not()->toBeNull();
        expect($node['items']['edges'])->toHaveCount(1);
        expect($node['items']['edges'][0]['node']['sku'])->toBe('REF-SKU-002');
        expect($node['items']['edges'][0]['node']['formattedPrice'])->not()->toBeNull();
    }

    /**
     * Test: orderId scopes the collection to one order.
     */
    public function test_filter_refunds_by_order_id(): void
    {
        $testData = $this->createTestData();

        $otherOrder = $this->createOrderFor($testData['customer']);
        $this->createRefund($otherOrder, 'REF-SKU-003', 40.00);

        $query = <<<'GQL'
            query getCustomerRefunds($orderId: Int) {
              customerRefunds(first: 10, orderId: $orderId) {
                totalCount
                edges {
                  node {
                    _id
                    orderId
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query, ['orderId' => $otherOrder->id]);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.customerRefunds.totalCount'))->toBe(1);
        expect($response->json('data.customerRefunds.edges.0.node.orderId'))->toBe($otherOrder->id);
    }

    /**
     * Test: another customer's refunds are never listed.
     */
    public function test_customer_only_sees_own_refunds(): void
    {
        $testData = $this->createTestData();

        $otherCustomer = $this->createCustomer();
        $this->createRefund($this->createOrderFor($otherCustomer), 'REF-SKU-OTHER', 99.00);

        $query = <<<'GQL'
            query getCustomerRefunds {
              customerRefunds(first: 10) {
                edges {
                  node {
                    _id
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $ids = collect($response->json('data.customerRefunds.edges'))->pluck('node._id')->all();

        expect($ids)->toEqualCanonicalizing([$testData['refund1']->id, $testData['refund2']->id]);
    }

    /**
     * Test: a guest cannot list refunds.
     */
    public function test_get_customer_refunds_requires_authentication(): void
    {
        $this->seedRequiredData();

        $query = <<<'GQL'
            query getCustomerRefunds {
              customerRefunds(first: 5) {
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

        expect($response->json('errors'))->not()->toBeEmpty();
    }

    /**
     * Test: the single refund query returns totals and items.
     */
    public function test_get_customer_refund_by_id(): void
    {
        $testData = $this->createTestData();
        $refundId = "/api/shop/customer-refunds/{$testData['refund1']->id}";

        $query = <<<GQL
            query getCustomerRefund {
              customerRefund(id: "{$refundId}") {
                _id
                orderIncrementId
                state
                grandTotal
                baseGrandTotal
                formattedGrandTotal
                items {
                  edges {
                    node {
                      sku
                      qty
                      formattedTotal
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.customerRefund');

        expect($data['_id'])->toBe($testData['refund1']->id);
        expect($data['state'])->toBe('refunded');
        expect((float) $data['grandTotal'])->toBe(60.0);
        expect($data['items']['edges'][0]['node']['sku'])->toBe('REF-SKU-001');
    }

    /**
     * Test: another customer's refund resolves to an error.
     */
    public function test_other_customer_cannot_get_customer_refund_by_id(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();
        $refundId = "/api/shop/customer-refunds/{$testData['refund1']->id}";

        $query = <<<GQL
            query getCustomerRefund {
              customerRefund(id: "{$refundId}") {
                _id
                grandTotal
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($otherCustomer, $query);

        $response->assertOk();

        expect($response->json('errors'))->not()->toBeEmpty();
        expect($response->json('data.customerRefund'))->toBeNull();
    }

    /**
     * Test: a guest cannot read a refund by ID.
     */
    public function test_guest_cannot_get_customer_refund_by_id(): void
    {
        $testData = $this->createTestData();
        $refundId = "/api/shop/customer-refunds/{$testData['refund1']->id}";

        $query = <<<GQL
            query getCustomerRefund {
              customerRefund(id: "{$refundId}") {
                _id
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();

        expect($response->json('errors'))->not()->toBeEmpty();
        expect($response->json('data.customerRefund'))->toBeNull();
    }
}
