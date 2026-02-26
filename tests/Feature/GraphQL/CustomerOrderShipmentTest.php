<?php

namespace Tests\Feature\BagistoApi\GraphQL;

use Tests\Feature\BagistoApi\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderAddress;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;
use Webkul\Sales\Models\Shipment;
use Webkul\Sales\Models\ShipmentItem;

class CustomerOrderShipmentTest extends GraphQLTestCase
{
    /**
     * Create test data — customer with order and shipments
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $channel  = Channel::first();
        $product  = Product::factory()->create();

        $order = Order::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'completed',
            'shipping_title'      => 'Flat Rate - Flat Rate',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'sku'        => 'TEST-SHIP-SKU-001',
            'type'       => 'simple',
            'name'       => 'Test Shipment Product',
            'qty_ordered' => 3,
        ]);

        OrderPayment::factory()->create([
            'order_id' => $order->id,
            'method'   => 'money_transfer',
            'method_title' => 'Money Transfer',
        ]);

        /** Create shipping address */
        $shippingAddress = OrderAddress::factory()->create([
            'order_id'    => $order->id,
            'address_type' => 'shipping',
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'email'       => $customer->email,
            'phone'       => '+1-555-0123',
            'address'      => '123 Main St',
            'city'        => 'Springfield',
            'state'       => 'IL',
            'country'     => 'US',
            'postcode'    => '62701',
        ]);

        /** Create first shipment (partial) */
        $shipment1 = Shipment::factory()->create([
            'order_id'              => $order->id,
            'order_address_id'      => $shippingAddress->id,
            'customer_id'           => $customer->id,
            'customer_type'         => Customer::class,
            'status'                => 'shipped',
            'total_qty'             => 2,
            'total_weight'          => 10.5,
            'carrier_code'          => 'flat_rate',
            'carrier_title'         => 'Flat Rate',
            'track_number'          => 'TRACK123456789',
            'email_sent'            => true,
        ]);

        ShipmentItem::create([
            'shipment_id'   => $shipment1->id,
            'order_item_id' => $orderItem->id,
            'name'          => 'Test Shipment Product',
            'sku'           => 'TEST-SHIP-SKU-001',
            'qty'           => 2,
            'weight'        => 10.5,
        ]);

        /** Create second shipment (remainder) */
        $shipment2 = Shipment::factory()->create([
            'order_id'              => $order->id,
            'order_address_id'      => $shippingAddress->id,
            'customer_id'           => $customer->id,
            'customer_type'         => Customer::class,
            'status'                => 'pending',
            'total_qty'             => 1,
            'total_weight'          => 5.25,
            'carrier_code'          => 'flat_rate',
            'carrier_title'         => 'Flat Rate',
            'track_number'          => null,
            'email_sent'            => false,
        ]);

        ShipmentItem::create([
            'shipment_id'   => $shipment2->id,
            'order_item_id' => $orderItem->id,
            'name'          => 'Test Shipment Product',
            'sku'           => 'TEST-SHIP-SKU-001',
            'qty'           => 1,
            'weight'        => 5.25,
        ]);

        return compact(
            'customer',
            'channel',
            'product',
            'order',
            'orderItem',
            'shippingAddress',
            'shipment1',
            'shipment2'
        );
    }

    // ── Shipments in Order Query ──────────────────────────────

    /**
     * Test: Query order with shipments collection
     */
    public function test_query_order_includes_shipments(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                incrementId
                shipments {
                  edges {
                    node {
                      _id
                      status
                      totalQty
                      carrierTitle
                      trackNumber
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');
        $errors = $response->json('errors');

        // For debugging: print any errors
        if ($errors) {
            dump($errors);
        }

        expect($data['incrementId'])->toBeTruthy();
        expect($data['shipments']['edges'])->toHaveCount(2);

        $edges = $data['shipments']['edges'];
        
        /** First shipment should be shipped */
        expect($edges[0]['node']['status'])->toBe('shipped');
        expect($edges[0]['node']['totalQty'])->toBe(2);
        expect($edges[0]['node']['carrierTitle'])->toBe('Flat Rate');
        expect($edges[0]['node']['trackNumber'])->toBe('TRACK123456789');

        /** Second shipment should be pending */
        expect($edges[1]['node']['status'])->toBe('pending');
        expect($edges[1]['node']['totalQty'])->toBe(1);
        expect($edges[1]['node']['trackNumber'])->toBeNull();
    }

    /**
     * Test: Query order shipments includes items
     */
    public function test_query_shipments_includes_items(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shipments {
                  _id
                  status
                  items {
                    _id
                    sku
                    name
                    qty
                    weight
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        expect($data['shipments'])->toHaveCount(2);

        /** First shipment items */
        expect($data['shipments'][0]['items'])->toHaveCount(1);
        $item1 = $data['shipments'][0]['items'][0];
        expect($item1['sku'])->toBe('TEST-SHIP-SKU-001');
        expect($item1['name'])->toBe('Test Shipment Product');
        expect($item1['qty'])->toBe(2);
        expect($item1['weight'])->toBe(10.5);

        /** Second shipment items */
        expect($data['shipments'][1]['items'])->toHaveCount(1);
        $item2 = $data['shipments'][1]['items'][0];
        expect($item2['sku'])->toBe('TEST-SHIP-SKU-001');
        expect($item2['qty'])->toBe(1);
        expect($item2['weight'])->toBe(5.25);
    }

    /**
     * Test: Query shipments includes shipping address
     */
    public function test_query_shipments_includes_shipping_address(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shipments {
                  _id
                  shippingAddress {
                    _id
                    firstName
                    lastName
                    email
                    street
                    city
                    state
                    postcode
                    phone
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        expect($data['shipments'])->toHaveCount(2);

        /** First shipment address */
        $address = $data['shipments'][0]['shippingAddress'];
        if ($address) {
            expect($address['firstName'])->toBe('John');
            expect($address['lastName'])->toBe('Doe');
            expect($address['city'])->toBe('Springfield');
            expect($address['phone'])->toBe('+1-555-0123');
        }
    }

    /**
     * Test: Query shipments includes payment method
     */
    public function test_query_shipments_includes_payment_method(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shipments {
                  _id
                  paymentMethodTitle
                  shippingMethodTitle
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        expect($data['shipments'])->toHaveCount(2);

        /** Both shipments should have payment and shipping method */
        expect($data['shipments'][0]['paymentMethodTitle'])->toBe('Money Transfer');
        expect($data['shipments'][0]['shippingMethodTitle'])->toBe('Flat Rate - Flat Rate');
        expect($data['shipments'][1]['paymentMethodTitle'])->toBe('Money Transfer');
        expect($data['shipments'][1]['shippingMethodTitle'])->toBe('Flat Rate - Flat Rate');
    }

    /**
     * Test: Query shipment computed fields (shippingNumber)
     */
    public function test_query_shipment_computed_fields(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shipments {
                  _id
                  shippingNumber
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        expect($data['shipments'])->toHaveCount(2);

        /** Shipping numbers should be formatted as #ID */
        $shipment1 = $data['shipments'][0];
        expect($shipment1['shippingNumber'])->toMatch('/^#\d+$/');
    }

    /**
     * Test: Query all shipment fields
     */
    public function test_query_all_shipment_fields(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shipments {
                  _id
                  status
                  totalQty
                  totalWeight
                  carrierCode
                  carrierTitle
                  trackNumber
                  emailSent
                  shippingNumber
                  paymentMethodTitle
                  shippingMethodTitle
                  createdAt
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        expect($data['shipments'])->toHaveCount(2);

        $shipment = $data['shipments'][0];
        expect($shipment)->toHaveKeys([
            '_id',
            'status',
            'totalQty',
            'totalWeight',
            'carrierCode',
            'carrierTitle',
            'trackNumber',
            'emailSent',
            'shippingNumber',
            'paymentMethodTitle',
            'shippingMethodTitle',
            'createdAt',
        ]);
    }

    // ── Access Control ────────────────────────────────────────

    /**
     * Test: Customer only sees their own shipments
     */
    public function test_customer_only_sees_own_shipments(): void
    {
        $testData = $this->createTestData();

        /** Create another customer with their own order/shipment */
        $otherCustomer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create();

        $otherOrder = Order::factory()->create([
            'customer_id'         => $otherCustomer->id,
            'customer_email'      => $otherCustomer->email,
            'customer_first_name' => $otherCustomer->first_name,
            'customer_last_name'  => $otherCustomer->last_name,
            'channel_id'          => $channel->id,
            'status'              => 'completed',
        ]);

        $otherOrderItem = OrderItem::factory()->create([
            'order_id'   => $otherOrder->id,
            'product_id' => $product->id,
            'sku'        => 'OTHER-SKU',
            'type'       => 'simple',
            'name'       => 'Other Product',
        ]);

        $otherAddress = OrderAddress::factory()->create([
            'order_id'    => $otherOrder->id,
            'address_type' => 'shipping',
        ]);

        Shipment::factory()->create([
            'order_id'         => $otherOrder->id,
            'order_address_id' => $otherAddress->id,
            'customer_id'      => $otherCustomer->id,
            'customer_type'    => Customer::class,
            'status'           => 'shipped',
            'total_qty'        => 1,
        ]);

        ShipmentItem::create([
            'shipment_id'   => Shipment::where('order_id', $otherOrder->id)->first()->id,
            'order_item_id' => $otherOrderItem->id,
        ]);

        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shipments {
                  _id
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        /** Should only see 2 shipments from own order */
        expect($data['shipments'])->toHaveCount(2);
    }

    /**
     * Test: Unauthenticated request cannot access shipments
     */
    public function test_unauthenticated_cannot_access_shipments(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shipments {
                  _id
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $errors = $response->json('errors');

        expect($errors)->not()->toBeEmpty();
    }

    // ── Shipment Status Filtering ─────────────────────────────

    /**
     * Test: Filter shipments by status (archived)
     */
    public function test_order_with_shipped_and_pending_shipments(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shipments {
                  _id
                  status
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        expect($data['shipments'])->toHaveCount(2);

        $statuses = array_column($data['shipments'], 'status');
        expect($statuses)->toContain('shipped');
        expect($statuses)->toContain('pending');
    }

    // ── Shipment Item Details ────────────────────────────────

    /**
     * Test: Query shipment items with full details
     */
    public function test_query_shipment_items_full_details(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shipments {
                  _id
                  items {
                    _id
                    sku
                    name
                    qty
                    weight
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        expect($data['shipments'])->toHaveCount(2);

        /** Each shipment has items */
        foreach ($data['shipments'] as $shipment) {
            expect($shipment['items'])->not()->toBeEmpty();

            foreach ($shipment['items'] as $item) {
                expect($item)->toHaveKeys(['_id', 'sku', 'name', 'qty', 'weight']);
                expect($item['sku'])->toBeTruthy();
                expect($item['qty'])->toBeGreaterThan(0);
            }
        }
    }

    /**
     * Test: Shipment item preserves order item details
     */
    public function test_shipment_item_preserves_order_item_details(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shipments {
                  items {
                    sku
                    name
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        /** Verify SKU and name match original order item */
        $shipment = $data['shipments'][0];
        $item = $shipment['items'][0];

        expect($item['sku'])->toBe('TEST-SHIP-SKU-001');
        expect($item['name'])->toBe('Test Shipment Product');
    }

    // ── Tracking Information ──────────────────────────────────

    /**
     * Test: Shipment with tracking number
     */
    public function test_shipment_with_tracking_number(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shipments(filter: {status: ["shipped"]}) {
                  _id
                  trackNumber
                  carrierCode
                  carrierTitle
                  emailSent
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        expect($data['shipments'])->not()->toBeEmpty();

        $shipment = $data['shipments'][0];
        expect($shipment['trackNumber'])->toBe('TRACK123456789');
        expect($shipment['carriercode'])->toBe('flat_rate');
        expect($shipment['emailSent'])->toBeTrue();
    }

    /**
     * Test: Shipment without tracking number (pending)
     */
    public function test_shipment_without_tracking_number(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shipments {
                  _id
                  status
                  trackNumber
                  emailSent
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        $pendingShipment = collect($data['shipments'])
            ->firstWhere('status', 'pending');

        expect($pendingShipment)->not()->toBeNull();
        expect($pendingShipment['trackNumber'])->toBeNull();
        expect($pendingShipment['emailSent'])->toBeFalse();
    }

    // ── Schema Introspection ──────────────────────────────────

    /**
     * Test: CustomerOrderShipment type has expected fields in schema
     */
    public function test_customer_order_shipment_schema_has_expected_fields(): void
    {
        $query = <<<'GQL'
            {
              __type(name: "CustomerOrderShipment") {
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
            ->and($type['name'])->toBe('CustomerOrderShipment');

        $fieldNames = array_column($type['fields'], 'name');

        expect($fieldNames)
            ->toContain('_id')
            ->toContain('status')
            ->toContain('totalQty')
            ->toContain('totalWeight')
            ->toContain('carrierCode')
            ->toContain('carrierTitle')
            ->toContain('trackNumber')
            ->toContain('emailSent')
            ->toContain('shippingNumber')
            ->toContain('items')
            ->toContain('shippingAddress')
            ->toContain('createdAt');
    }

    /**
     * Test: CustomerOrderShipmentItem type has expected fields in schema
     */
    public function test_customer_order_shipment_item_schema_has_expected_fields(): void
    {
        $query = <<<'GQL'
            {
              __type(name: "CustomerOrderShipmentItem") {
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
            ->and($type['name'])->toBe('CustomerOrderShipmentItem');

        $fieldNames = array_column($type['fields'], 'name');

        expect($fieldNames)
            ->toContain('_id')
            ->toContain('sku')
            ->toContain('name')
            ->toContain('qty')
            ->toContain('weight');
    }

    // ── Weight and Quantity ───────────────────────────────────

    /**
     * Test: Shipment total qty and weight calculations
     */
    public function test_shipment_total_qty_and_weight(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shipments {
                  _id
                  totalQty
                  totalWeight
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        expect($data['shipments'])->toHaveCount(2);

        /** First shipment */
        expect($data['shipments'][0]['totalQty'])->toBe(2);
        expect($data['shipments'][0]['totalWeight'])->toBe(10.5);

        /** Second shipment */
        expect($data['shipments'][1]['totalQty'])->toBe(1);
        expect($data['shipments'][1]['totalWeight'])->toBe(5.25);
    }

    /**
     * Test: Individual item qty and weight
     */
    public function test_shipment_item_qty_and_weight(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                shipments {
                  items {
                    qty
                    weight
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.customerOrder');

        $items = collect($data['shipments'])
            ->pluck('items')
            ->flatten(1);

        expect($items)->not()->toBeEmpty();

        foreach ($items as $item) {
            expect($item['qty'])->toBeGreaterThan(0);
            expect($item['weight'])->toBeGreaterThan(0);
        }
    }
}
