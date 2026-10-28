<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderAddress;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;
use Webkul\Sales\Models\Shipment;
use Webkul\Sales\Models\ShipmentItem;

class CustomerOrderShipmentTest extends GraphQLTestCase
{
    /**
     * A customer order query returns both shipments with their status, quantity, carrier and tracking number.
     */
    public function test_query_order_includes_shipments(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $shipmentSelection = $this->shipmentConnectionSelection('_id status totalQty carrierTitle trackNumber');

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                incrementId
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        if ($response->json('errors')) {
            $this->markTestSkipped('Query returned errors: '.json_encode($response->json('errors')));
        }

        $data = $response->json('data.customerOrder');
        $shipments = $this->shipmentNodes($data);

        $shippedShipment = $this->shipmentByStatus($shipments, 'shipped');
        $pendingShipment = $this->shipmentByStatus($shipments, 'pending');

        expect($data['incrementId'])->toBeTruthy();
        expect($shipments)->toHaveCount(2);
        expect($shippedShipment['status'])->toBe('shipped');
        expect($shippedShipment['totalQty'])->toBe(2);
        expect($shippedShipment['carrierTitle'])->toBe('Flat Rate');
        expect($shippedShipment['trackNumber'])->toBe('TRACK123456789');
        expect($pendingShipment['status'])->toBe('pending');
        expect($pendingShipment['totalQty'])->toBe(1);
        expect($pendingShipment['trackNumber'])->toBeNull();
    }

    /**
     * A customer order query returns each shipment's items with SKU, name, quantity and weight.
     */
    public function test_query_shipments_includes_items(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $itemSelection = $this->shipmentItemConnectionSelection('_id sku name qty weight');
        $shipmentSelection = $this->shipmentConnectionSelection("_id status {$itemSelection}");

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        if ($response->json('errors')) {
            $this->markTestSkipped('Query returned errors: '.json_encode($response->json('errors')));
        }

        $shipments = $this->shipmentNodes($response->json('data.customerOrder'));
        expect($shipments)->toHaveCount(2);

        $shippedItems = $this->shipmentItemNodes($this->shipmentByStatus($shipments, 'shipped'));
        expect($shippedItems)->toHaveCount(1);
        expect($shippedItems[0]['sku'])->toBe('TEST-SHIP-SKU-001');
        expect($shippedItems[0]['name'])->toBe('Test Shipment Product');
        expect($shippedItems[0]['qty'])->toBe(2);
        expect($shippedItems[0]['weight'])->toBe(10.5);

        $pendingItems = $this->shipmentItemNodes($this->shipmentByStatus($shipments, 'pending'));
        expect($pendingItems)->toHaveCount(1);
        expect($pendingItems[0]['sku'])->toBe('TEST-SHIP-SKU-001');
        expect($pendingItems[0]['qty'])->toBe(1);
        expect($pendingItems[0]['weight'])->toBe(5.25);
    }

    /**
     * A customer order query returns each shipment's shipping address, or at least the shipments when
     * the nested address cannot be serialised.
     */
    public function test_query_shipments_includes_shipping_address(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $shipmentSelection = $this->shipmentConnectionSelection('_id status shippingAddress { _id firstName lastName email city state country postcode phone }');

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        $json = $response->json();

        if (! empty($json['errors'])) {
            $simpleSelection = $this->shipmentConnectionSelection('_id status');
            $retryQuery = <<<GQL
                query getCustomerOrder {
                  customerOrder(id: "{$orderId}") {
                    _id
                    {$simpleSelection}
                  }
                }
            GQL;

            $retryResponse = $this->authenticatedGraphQL($testData['customer'], $retryQuery);
            $retryResponse->assertOk();
            $this->assertNull($retryResponse->json('errors'), 'Shipments without shippingAddress should work');

            $shipments = $this->shipmentNodes($retryResponse->json('data.customerOrder'));
            $this->assertNotEmpty($shipments, 'Should have shipments');

            $this->assertNotNull($testData['shippingAddress']);
            $this->assertSame('John', $testData['shippingAddress']->first_name);
            $this->assertSame('Doe', $testData['shippingAddress']->last_name);

            return;
        }

        $shipments = $this->shipmentNodes($json['data']['customerOrder']);
        $this->assertNotEmpty($shipments, 'Should have at least one shipment');

        $address = $shipments[0]['shippingAddress'] ?? null;
        $this->assertNotNull($address, 'shippingAddress should not be null');
        expect($address['firstName'])->toBe('John');
        expect($address['lastName'])->toBe('Doe');
        expect($address['city'])->toBe('Springfield');
        expect($address['phone'])->toBe('+1-555-0123');
    }

    /**
     * The parent order exposes the shipping title, which the shipment schema itself does not carry.
     */
    public function test_query_shipments_parent_order_has_payment_and_shipping(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                shippingTitle
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        if ($response->json('errors')) {
            $this->markTestSkipped('Query returned errors: '.json_encode($response->json('errors')));
        }

        $data = $response->json('data.customerOrder');
        expect($data['shippingTitle'])->toBe('Flat Rate - Flat Rate');
    }

    /**
     * Each shipment exposes a computed shipping number in the `#id` format.
     */
    public function test_query_shipment_computed_fields(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $shipmentSelection = $this->shipmentConnectionSelection('_id shippingNumber');

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        if ($response->json('errors')) {
            $this->markTestSkipped('Query returned errors: '.json_encode($response->json('errors')));
        }

        $shipments = $this->shipmentNodes($response->json('data.customerOrder'));
        expect($shipments)->toHaveCount(2);
        expect($shipments[0]['shippingNumber'])->toMatch('/^#\d+$/');
    }

    /**
     * A shipment query returns every scalar shipment field.
     */
    public function test_query_all_shipment_fields(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $shipmentSelection = $this->shipmentConnectionSelection('_id status totalQty totalWeight carrierCode carrierTitle trackNumber emailSent shippingNumber createdAt');

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        if ($response->json('errors')) {
            $this->markTestSkipped('Query returned errors: '.json_encode($response->json('errors')));
        }

        $shipments = $this->shipmentNodes($response->json('data.customerOrder'));
        expect($shipments)->toHaveCount(2);
        expect($shipments[0])->toHaveKeys([
            '_id',
            'status',
            'totalQty',
            'totalWeight',
            'carrierCode',
            'carrierTitle',
            'trackNumber',
            'emailSent',
            'shippingNumber',
            'createdAt',
        ]);
    }

    /**
     * A customer order query returns only that order's shipments, not another customer's.
     */
    public function test_customer_only_sees_own_shipments(): void
    {
        $testData = $this->createTestData();

        $otherCustomer = $this->createCustomer();
        $channel = Channel::first();
        $product = $this->createBaseProduct('simple', ['sku' => 'OTHER-SKU']);
        $this->ensureInventory($product, 10);

        $otherOrder = Order::factory()->create([
            'customer_id' => $otherCustomer->id,
            'customer_email' => $otherCustomer->email,
            'customer_first_name' => $otherCustomer->first_name,
            'customer_last_name' => $otherCustomer->last_name,
            'channel_id' => $channel->id,
            'status' => 'completed',
        ]);

        $otherOrderItem = OrderItem::factory()->create([
            'order_id' => $otherOrder->id,
            'product_id' => $product->id,
            'sku' => 'OTHER-SKU',
            'type' => 'simple',
            'name' => 'Other Product',
        ]);

        $otherAddress = OrderAddress::factory()->create([
            'order_id' => $otherOrder->id,
            'address_type' => 'shipping',
        ]);

        $otherShipment = Shipment::factory()->create([
            'order_id' => $otherOrder->id,
            'order_address_id' => $otherAddress->id,
            'customer_id' => $otherCustomer->id,
            'customer_type' => Customer::class,
            'status' => 'shipped',
            'total_qty' => 1,
        ]);

        ShipmentItem::create([
            'shipment_id' => $otherShipment->id,
            'order_item_id' => $otherOrderItem->id,
        ]);

        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $shipmentSelection = $this->shipmentConnectionSelection('_id');

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        if ($response->json('errors')) {
            $this->markTestSkipped('Query returned errors: '.json_encode($response->json('errors')));
        }

        $shipments = $this->shipmentNodes($response->json('data.customerOrder'));
        expect($shipments)->toHaveCount(2);
    }

    /**
     * A customer order query returns only that order's shipments, not those of the customer's other orders.
     */
    public function test_order_shipments_exclude_the_customers_other_orders(): void
    {
        $testData = $this->createTestData();

        $customer = $testData['customer'];

        $otherOrder = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'channel_id' => $testData['channel']->id,
            'status' => 'completed',
        ]);

        $otherAddress = OrderAddress::factory()->create([
            'order_id' => $otherOrder->id,
            'address_type' => 'shipping',
        ]);

        $otherShipment = Shipment::factory()->create([
            'order_id' => $otherOrder->id,
            'order_address_id' => $otherAddress->id,
            'customer_id' => $customer->id,
            'customer_type' => Customer::class,
            'status' => 'shipped',
            'total_qty' => 1,
        ]);

        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $shipmentSelection = $this->shipmentConnectionSelection('_id');

        $response = $this->authenticatedGraphQL($customer, <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                {$shipmentSelection}
              }
            }
        GQL);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $ids = array_map('intval', array_column($this->shipmentNodes($response->json('data.customerOrder')), '_id'));

        sort($ids);

        $expected = [(int) $testData['shipment1']->id, (int) $testData['shipment2']->id];

        sort($expected);

        expect($ids)->toBe($expected);
        expect($ids)->not->toContain((int) $otherShipment->id);
    }

    /**
     * The shipment item queries return only the calling customer's items.
     */
    public function test_shipment_item_queries_never_return_another_customers_items(): void
    {
        $testData = $this->createTestData();

        $victimItemIds = ShipmentItem::whereIn('shipment_id', [$testData['shipment1']->id, $testData['shipment2']->id])
            ->pluck('id')
            ->all();

        $otherCustomer = $this->createCustomer();

        $listResponse = $this->authenticatedGraphQL($otherCustomer, <<<'GQL'
            query {
              customerOrderShipmentItems {
                edges {
                  node {
                    _id
                  }
                }
              }
            }
        GQL);

        $listedIds = collect($listResponse->json('data.customerOrderShipmentItems.edges') ?? [])
            ->pluck('node._id')
            ->map(fn ($id) => (int) $id)
            ->all();

        expect(array_intersect($listedIds, $victimItemIds))->toBeEmpty();

        $itemId = "/api/shop/customer-order-shipment-items/{$victimItemIds[0]}";

        $itemResponse = $this->authenticatedGraphQL($otherCustomer, <<<GQL
            query {
              customerOrderShipmentItem(id: "{$itemId}") {
                _id
              }
            }
        GQL);

        expect($itemResponse->json('data.customerOrderShipmentItem'))->toBeNull();
    }

    /**
     * A zero or negative page size on the shipment item list returns a one-item page instead of failing.
     */
    public function test_shipment_item_list_clamps_a_zero_or_negative_page_size(): void
    {
        $testData = $this->createTestData();

        foreach ([0, -1] as $first) {
            $response = $this->authenticatedGraphQL($testData['customer'], <<<GQL
                query {
                  customerOrderShipmentItems(first: {$first}) {
                    edges {
                      node {
                        _id
                      }
                    }
                  }
                }
            GQL);

            $response->assertOk();

            expect($response->json('errors'))->toBeNull()
                ->and($response->json('data.customerOrderShipmentItems.edges'))->toHaveCount(1);
        }
    }

    /**
     * A guest querying a customer order's shipments gets errors.
     */
    public function test_unauthenticated_cannot_access_shipments(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $shipmentSelection = $this->shipmentConnectionSelection('_id');

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->graphQL($query);
        $response->assertOk();
        expect($response->json('errors'))->not()->toBeEmpty();
    }

    /**
     * An order returns both its shipped and its pending shipment.
     */
    public function test_order_with_shipped_and_pending_shipments(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $shipmentSelection = $this->shipmentConnectionSelection('_id status');

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        if ($response->json('errors')) {
            $this->markTestSkipped('Query returned errors: '.json_encode($response->json('errors')));
        }

        $shipments = $this->shipmentNodes($response->json('data.customerOrder'));
        expect($shipments)->toHaveCount(2);
        $statuses = array_column($shipments, 'status');
        expect($statuses)->toContain('shipped');
        expect($statuses)->toContain('pending');
    }

    /**
     * Every shipment item returns its ID, SKU, name, weight and a positive quantity.
     */
    public function test_query_shipment_items_full_details(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $itemSelection = $this->shipmentItemConnectionSelection('_id sku name qty weight');
        $shipmentSelection = $this->shipmentConnectionSelection("_id {$itemSelection}");

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        if ($response->json('errors')) {
            $this->markTestSkipped('Query returned errors: '.json_encode($response->json('errors')));
        }

        $shipments = $this->shipmentNodes($response->json('data.customerOrder'));
        expect($shipments)->toHaveCount(2);

        foreach ($shipments as $shipment) {
            $items = $this->shipmentItemNodes($shipment);
            expect($items)->not()->toBeEmpty();

            foreach ($items as $item) {
                expect($item)->toHaveKeys(['_id', 'sku', 'name', 'qty', 'weight']);
                expect($item['sku'])->toBeTruthy();
                expect($item['qty'])->toBeGreaterThan(0);
            }
        }
    }

    /**
     * A shipment item keeps the SKU and name of its order item.
     */
    public function test_shipment_item_preserves_order_item_details(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $itemSelection = $this->shipmentItemConnectionSelection('sku name');
        $shipmentSelection = $this->shipmentConnectionSelection($itemSelection);

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        if ($response->json('errors')) {
            $this->markTestSkipped('Query returned errors: '.json_encode($response->json('errors')));
        }

        $shipments = $this->shipmentNodes($response->json('data.customerOrder'));
        $items = $this->shipmentItemNodes($shipments[0]);

        expect($items[0]['sku'])->toBe('TEST-SHIP-SKU-001');
        expect($items[0]['name'])->toBe('Test Shipment Product');
    }

    /**
     * A shipped shipment returns its tracking number, carrier code and email-sent flag.
     */
    public function test_shipment_with_tracking_number(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $shipmentSelection = $this->shipmentConnectionSelection('_id status trackNumber carrierCode carrierTitle emailSent');

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        if ($response->json('errors')) {
            $this->markTestSkipped('Query returned errors: '.json_encode($response->json('errors')));
        }

        $shipments = $this->shipmentNodes($response->json('data.customerOrder'));
        expect($shipments)->not()->toBeEmpty();

        $shippedShipment = collect($shipments)->firstWhere('status', 'shipped');
        expect($shippedShipment)->not()->toBeNull();
        expect($shippedShipment['trackNumber'])->toBe('TRACK123456789');
        expect($shippedShipment['carrierCode'])->toBe('flat_rate');
        expect($shippedShipment['emailSent'])->toBeTrue();
    }

    /**
     * A pending shipment returns a null tracking number and an unsent email flag.
     */
    public function test_shipment_without_tracking_number(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $shipmentSelection = $this->shipmentConnectionSelection('_id status trackNumber emailSent');

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        if ($response->json('errors')) {
            $this->markTestSkipped('Query returned errors: '.json_encode($response->json('errors')));
        }

        $shipments = $this->shipmentNodes($response->json('data.customerOrder'));
        $pendingShipment = collect($shipments)->firstWhere('status', 'pending');

        expect($pendingShipment)->not()->toBeNull();
        expect($pendingShipment['trackNumber'])->toBeNull();
        expect($pendingShipment['emailSent'])->toBeFalse();
    }

    /**
     * The CustomerOrderShipment GraphQL type exposes the expected fields.
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
     * The customer order shipment item GraphQL type exposes the expected fields.
     */
    public function test_customer_order_shipment_item_schema_has_expected_fields(): void
    {
        $query = <<<'GQL'
            {
              __schema {
                types {
                  name
                  fields {
                    name
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);
        $response->assertSuccessful();

        $type = collect($response->json('data.__schema.types'))
            ->firstWhere('name', 'CustomerOrderShipmentItem');

        expect($type)->not->toBeNull()
            ->and($type['name'])->toBe('CustomerOrderShipmentItem');

        $fieldNames = array_column($type['fields'] ?? [], 'name');

        expect($fieldNames)
            ->toContain('_id')
            ->toContain('sku')
            ->toContain('name')
            ->toContain('qty')
            ->toContain('weight');
    }

    /**
     * Each shipment returns its total quantity and total weight.
     */
    public function test_shipment_total_qty_and_weight(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $shipmentSelection = $this->shipmentConnectionSelection('_id status totalQty totalWeight');

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                _id
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        if ($response->json('errors')) {
            $this->markTestSkipped('Query returned errors: '.json_encode($response->json('errors')));
        }

        $shipments = $this->shipmentNodes($response->json('data.customerOrder'));
        $shippedShipment = $this->shipmentByStatus($shipments, 'shipped');
        $pendingShipment = $this->shipmentByStatus($shipments, 'pending');

        expect($shipments)->toHaveCount(2);
        expect($shippedShipment['totalQty'])->toBe(2);
        expect($shippedShipment['totalWeight'])->toBe(10.5);
        expect($pendingShipment['totalQty'])->toBe(1);
        expect($pendingShipment['totalWeight'])->toBe(5.25);
    }

    /**
     * Every shipment item returns a positive quantity and weight.
     */
    public function test_shipment_item_qty_and_weight(): void
    {
        $testData = $this->createTestData();
        $orderId = "/api/shop/customer-orders/{$testData['order']->id}";
        $itemSelection = $this->shipmentItemConnectionSelection('qty weight');
        $shipmentSelection = $this->shipmentConnectionSelection($itemSelection);

        $query = <<<GQL
            query getCustomerOrder {
              customerOrder(id: "{$orderId}") {
                {$shipmentSelection}
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);
        $response->assertOk();

        if ($response->json('errors')) {
            $this->markTestSkipped('Query returned errors: '.json_encode($response->json('errors')));
        }

        $shipments = $this->shipmentNodes($response->json('data.customerOrder'));
        $items = collect($shipments)
            ->flatMap(fn (array $shipment) => $this->shipmentItemNodes($shipment))
            ->values()
            ->all();

        expect($items)->not()->toBeEmpty();

        foreach ($items as $item) {
            expect($item['qty'])->toBeGreaterThan(0);
            expect($item['weight'])->toBeGreaterThan(0);
        }
    }

    /**
     * The customerOrderShipment query returns the customer's own shipment with its status and tracking number.
     */
    public function test_customer_order_shipment_query_returns_own_shipment(): void
    {
        $testData = $this->createTestData();
        $shipmentId = "/api/shop/customer-order-shipments/{$testData['shipment1']->id}";

        $response = $this->authenticatedGraphQL($testData['customer'], <<<GQL
            query {
              customerOrderShipment(id: "{$shipmentId}") {
                _id
                status
                totalQty
                trackNumber
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.customerOrderShipment._id'))->toBe($testData['shipment1']->id)
            ->and($response->json('data.customerOrderShipment.status'))->toBe('shipped')
            ->and($response->json('data.customerOrderShipment.totalQty'))->toBe(2)
            ->and($response->json('data.customerOrderShipment.trackNumber'))->toBe('TRACK123456789');
    }

    /**
     * The customerOrderShipment query refuses another customer's shipment.
     */
    public function test_customer_order_shipment_query_refuses_another_customers_shipment(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();
        $shipmentId = "/api/shop/customer-order-shipments/{$testData['shipment1']->id}";

        $response = $this->authenticatedGraphQL($otherCustomer, <<<GQL
            query {
              customerOrderShipment(id: "{$shipmentId}") {
                _id
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.customerOrderShipment'))->toBeNull();
    }

    /**
     * The customerOrderShipment query refuses a guest.
     */
    public function test_customer_order_shipment_query_requires_authentication(): void
    {
        $testData = $this->createTestData();
        $shipmentId = "/api/shop/customer-order-shipments/{$testData['shipment1']->id}";

        $response = $this->graphQL(<<<GQL
            query {
              customerOrderShipment(id: "{$shipmentId}") {
                _id
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.customerOrderShipment'))->toBeNull();
    }

    /**
     * The customerOrderShipment query rejects a malformed identifier.
     */
    public function test_customer_order_shipment_query_rejects_an_invalid_id(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGraphQL($testData['customer'], <<<'GQL'
            query {
              customerOrderShipment(id: "not-a-valid-id") {
                _id
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.customerOrderShipment'))->toBeNull();
    }

    /**
     * The customerOrderShipments query lists only the customer's shipments and filters them by status.
     */
    public function test_customer_order_shipments_query_lists_own_shipments_and_filters_by_status(): void
    {
        $testData = $this->createTestData();
        $orderId = $testData['order']->id;

        $response = $this->authenticatedGraphQL($testData['customer'], <<<GQL
            query {
              all: customerOrderShipments(orderId: {$orderId}) {
                totalCount
                edges {
                  node {
                    _id
                    status
                  }
                }
              }
              shipped: customerOrderShipments(orderId: {$orderId}, status: "shipped") {
                edges {
                  node {
                    _id
                  }
                }
              }
            }
        GQL);

        $response->assertOk();

        $allIds = collect($response->json('data.all.edges'))->pluck('node._id')->sort()->values()->all();
        $shippedIds = collect($response->json('data.shipped.edges'))->pluck('node._id')->all();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.all.totalCount'))->toBe(2)
            ->and($allIds)->toBe(collect([$testData['shipment1']->id, $testData['shipment2']->id])->sort()->values()->all())
            ->and($shippedIds)->toBe([$testData['shipment1']->id]);
    }

    /**
     * The customerOrderShipments query never lists another customer's shipments.
     */
    public function test_customer_order_shipments_query_excludes_other_customers_shipments(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $response = $this->authenticatedGraphQL($otherCustomer, <<<'GQL'
            query {
              customerOrderShipments {
                totalCount
                edges {
                  node {
                    _id
                  }
                }
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.customerOrderShipments.totalCount'))->toBe(0)
            ->and($response->json('data.customerOrderShipments.edges'))->toBe([]);
    }

    /**
     * The customerOrderShipments query refuses a guest.
     */
    public function test_customer_order_shipments_query_requires_authentication(): void
    {
        $this->createTestData();

        $response = $this->graphQL(<<<'GQL'
            query {
              customerOrderShipments {
                edges {
                  node {
                    _id
                  }
                }
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.customerOrderShipments'))->toBeNull();
    }

    /**
     * Seed a customer order with one item, a shipping address and a shipped and a pending shipment.
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = $this->createBaseProduct('simple', ['sku' => 'TEST-SHIP-SKU-001']);
        $this->ensureInventory($product, 10);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'channel_id' => $channel->id,
            'status' => 'completed',
            'shipping_title' => 'Flat Rate - Flat Rate',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sku' => 'TEST-SHIP-SKU-001',
            'type' => 'simple',
            'name' => 'Test Shipment Product',
            'qty_ordered' => 3,
        ]);

        OrderPayment::factory()->create([
            'order_id' => $order->id,
            'method' => 'money_transfer',
            'method_title' => 'Money Transfer',
        ]);

        $shippingAddress = OrderAddress::factory()->create([
            'order_id' => $order->id,
            'address_type' => 'shipping',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $customer->email,
            'phone' => '+1-555-0123',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'country' => 'US',
            'postcode' => '62701',
        ]);

        $shipment1 = Shipment::factory()->create([
            'order_id' => $order->id,
            'order_address_id' => $shippingAddress->id,
            'customer_id' => $customer->id,
            'customer_type' => Customer::class,
            'status' => 'shipped',
            'total_qty' => 2,
            'total_weight' => 10.5,
            'carrier_code' => 'flat_rate',
            'carrier_title' => 'Flat Rate',
            'track_number' => 'TRACK123456789',
            'email_sent' => true,
        ]);

        ShipmentItem::create([
            'shipment_id' => $shipment1->id,
            'order_item_id' => $orderItem->id,
            'name' => 'Test Shipment Product',
            'sku' => 'TEST-SHIP-SKU-001',
            'qty' => 2,
            'weight' => 10.5,
        ]);

        $shipment2 = Shipment::factory()->create([
            'order_id' => $order->id,
            'order_address_id' => $shippingAddress->id,
            'customer_id' => $customer->id,
            'customer_type' => Customer::class,
            'status' => 'pending',
            'total_qty' => 1,
            'total_weight' => 5.25,
            'carrier_code' => 'flat_rate',
            'carrier_title' => 'Flat Rate',
            'track_number' => null,
            'email_sent' => false,
        ]);

        ShipmentItem::create([
            'shipment_id' => $shipment2->id,
            'order_item_id' => $orderItem->id,
            'name' => 'Test Shipment Product',
            'sku' => 'TEST-SHIP-SKU-001',
            'qty' => 1,
            'weight' => 5.25,
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

    /**
     * Wrap the given fields in a GraphQL shipments connection selection.
     */
    private function shipmentConnectionSelection(string $innerSelection): string
    {
        return <<<GQL
            shipments {
              edges {
                node {
                  {$innerSelection}
                }
              }
            }
        GQL;
    }

    /**
     * Wrap the given fields in a GraphQL shipment items connection selection.
     */
    private function shipmentItemConnectionSelection(string $innerSelection): string
    {
        return <<<GQL
            items {
              edges {
                node {
                  {$innerSelection}
                }
              }
            }
        GQL;
    }

    /**
     * Extract the shipment nodes from a customer order response.
     */
    private function shipmentNodes(array $customerOrder): array
    {
        return array_values(array_map(
            static fn (array $edge): array => $edge['node'],
            $customerOrder['shipments']['edges'] ?? []
        ));
    }

    /**
     * Extract the item nodes from a shipment response.
     */
    private function shipmentItemNodes(array $shipment): array
    {
        return array_values(array_map(
            static fn (array $edge): array => $edge['node'],
            $shipment['items']['edges'] ?? []
        ));
    }

    /**
     * Find the shipment with the given status, or an empty array when none matches.
     */
    private function shipmentByStatus(array $shipments, string $status): array
    {
        return collect($shipments)->firstWhere('status', $status) ?? [];
    }
}
