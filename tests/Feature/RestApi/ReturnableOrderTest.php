<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;

class ReturnableOrderTest extends RestApiTestCase
{
    /**
     * The url.
     */
    private string $url = '/api/shop/returnable-orders';

    /**
     * Test lists an order that can still be returned.
     */
    public function test_lists_an_order_that_can_still_be_returned(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $order = $this->seedEligibleOrder($customer);

        $response = $this->authenticatedGet($customer, $this->url);

        $response->assertOk();

        $row = $this->rowFor($response->json(), (int) $order->id);

        expect($row)->not->toBeNull();
        expect($row['incrementId'])->toBe((string) $order->increment_id);
        expect($row['status'])->toBe('completed');
        expect($row['statusLabel'])->not->toBeNull();
        expect($row['totalQtyOrdered'])->toBe(2);
        expect($row['totalReturnedQty'])->toBe(0);
        expect($row['returnableQty'])->toBe(2);
        expect($row['formattedGrandTotal'])->not->toBeNull();
        expect($row)->toHaveKeys(['orderCurrencyCode', 'paymentMethodTitle', 'createdAt']);
    }

    /**
     * Test excludes an order whose items were sold without returns.
     */
    public function test_excludes_an_order_whose_items_were_sold_without_returns(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $order = $this->seedEligibleOrder($customer, [], ['rma_return_period' => null]);

        $response = $this->authenticatedGet($customer, $this->url);

        $response->assertOk();
        expect($this->rowFor($response->json(), (int) $order->id))->toBeNull();
    }

    /**
     * Test excludes an order whose return window has closed.
     */
    public function test_excludes_an_order_whose_return_window_has_closed(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $order = $this->seedEligibleOrder($customer, [], ['rma_return_period' => 1]);

        DB::table('order_items')
            ->where('order_id', $order->id)
            ->update(['created_at' => now()->subDays(10)]);

        $response = $this->authenticatedGet($customer, $this->url);

        $response->assertOk();
        expect($this->rowFor($response->json(), (int) $order->id))->toBeNull();
    }

    /**
     * Test excludes an order in a state that cannot be returned.
     */
    public function test_excludes_an_order_in_a_state_that_cannot_be_returned(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $order = $this->seedEligibleOrder($customer, ['status' => 'canceled']);

        $response = $this->authenticatedGet($customer, $this->url);

        $response->assertOk();
        expect($this->rowFor($response->json(), (int) $order->id))->toBeNull();
    }

    /**
     * Test counts quantity already returned.
     */
    public function test_counts_quantity_already_returned(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $order = $this->seedEligibleOrder($customer);

        $orderItem = OrderItem::where('order_id', $order->id)->first();

        DB::table('rma_items')->insert([
            'order_item_id' => $orderItem->id,
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->authenticatedGet($customer, $this->url);

        $response->assertOk();

        $row = $this->rowFor($response->json(), (int) $order->id);

        expect($row)->not->toBeNull();
        expect($row['totalReturnedQty'])->toBe(1);
        expect($row['returnableQty'])->toBe(1);
    }

    /**
     * Test drops an order once every unit is returned.
     */
    public function test_drops_an_order_once_every_unit_is_returned(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $order = $this->seedEligibleOrder($customer);

        $orderItem = OrderItem::where('order_id', $order->id)->first();

        DB::table('rma_items')->insert([
            'order_item_id' => $orderItem->id,
            'quantity' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->authenticatedGet($customer, $this->url);

        $response->assertOk();
        expect($this->rowFor($response->json(), (int) $order->id))->toBeNull();
    }

    /**
     * Test never lists another customers order.
     */
    public function test_never_lists_another_customers_order(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $other = $this->createCustomer();
        $foreign = $this->seedEligibleOrder($other);

        $response = $this->authenticatedGet($customer, $this->url);

        $response->assertOk();
        expect($this->rowFor($response->json(), (int) $foreign->id))->toBeNull();
    }

    /**
     * Test filters by order number.
     */
    public function test_filters_by_order_number(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $wanted = $this->seedEligibleOrder($customer, ['increment_id' => 'RMA-WANTED-'.uniqid()]);
        $other = $this->seedEligibleOrder($customer, ['increment_id' => 'RMA-OTHER-'.uniqid()]);

        $response = $this->authenticatedGet($customer, $this->url.'?increment_id='.$wanted->increment_id);

        $response->assertOk();
        expect($this->rowFor($response->json(), (int) $wanted->id))->not->toBeNull();
        expect($this->rowFor($response->json(), (int) $other->id))->toBeNull();
    }

    /**
     * Test filters by status.
     */
    public function test_filters_by_status(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $completed = $this->seedEligibleOrder($customer, ['status' => 'completed']);
        $processing = $this->seedEligibleOrder($customer, ['status' => 'processing']);

        $response = $this->authenticatedGet($customer, $this->url.'?status=processing');

        $response->assertOk();
        expect($this->rowFor($response->json(), (int) $processing->id))->not->toBeNull();
        expect($this->rowFor($response->json(), (int) $completed->id))->toBeNull();
    }

    /**
     * Test requires an authenticated customer.
     */
    public function test_requires_an_authenticated_customer(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url);

        expect($response->getStatusCode())->toBeIn([401, 403]);
    }

    /**
     * Seed eligible order.
     */
    private function seedEligibleOrder($customer, array $orderAttributes = [], array $itemAttributes = []): Order
    {
        $channel = Channel::first();
        $product = Product::factory()->create();

        $order = Order::factory()->create(array_merge([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'channel_id' => $channel->id,
            'status' => 'completed',
        ], $orderAttributes));

        OrderItem::factory()->create(array_merge([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'sku' => 'RETURNABLE-'.$order->id,
            'type' => 'simple',
            'name' => 'Returnable Product',
            'qty_ordered' => 2,
            'qty_invoiced' => 2,
            'rma_return_period' => 30,
        ], $itemAttributes));

        return $order;
    }

    /**
     * Row for.
     */
    private function rowFor(array $body, int $orderId): ?array
    {
        foreach ($body as $row) {
            if ((int) $row['id'] === $orderId) {
                return $row;
            }
        }

        return null;
    }
}
