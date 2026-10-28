<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;

class ReturnableOrderTest extends GraphQLTestCase
{
    private function query(): string
    {
        return <<<'GQL'
            query {
              returnableOrders {
                _id
                incrementId
                status
                statusLabel
                grandTotal
                formattedGrandTotal
                orderCurrencyCode
                paymentMethodTitle
                totalQtyOrdered
                totalReturnedQty
                returnableQty
                createdAt
              }
            }
        GQL;
    }

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
            'sku' => 'RETURNABLE-GQL-'.$order->id,
            'type' => 'simple',
            'name' => 'Returnable Product',
            'qty_ordered' => 2,
            'qty_invoiced' => 2,
            'rma_return_period' => 30,
        ], $itemAttributes));

        return $order;
    }

    private function rowFor($rows, int $orderId): ?array
    {
        foreach ($rows ?? [] as $row) {
            if ((int) $row['_id'] === $orderId) {
                return $row;
            }
        }

        return null;
    }

    public function test_query_returns_an_order_that_can_still_be_returned(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $order = $this->seedEligibleOrder($customer);

        $response = $this->authenticatedGraphQL($customer, $this->query());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $row = $this->rowFor($response->json('data.returnableOrders'), (int) $order->id);

        expect($row)->not->toBeNull();
        expect($row['incrementId'])->toBe((string) $order->increment_id);
        expect($row['status'])->toBe('completed');
        expect($row['statusLabel'])->not->toBeNull();
        expect($row['totalQtyOrdered'])->toBe(2);
        expect($row['returnableQty'])->toBe(2);
        expect($row['formattedGrandTotal'])->not->toBeNull();
    }

    public function test_query_counts_quantity_already_returned(): void
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

        $response = $this->authenticatedGraphQL($customer, $this->query());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $row = $this->rowFor($response->json('data.returnableOrders'), (int) $order->id);

        expect($row['totalReturnedQty'])->toBe(1);
        expect($row['returnableQty'])->toBe(1);
    }

    public function test_query_excludes_an_order_in_a_state_that_cannot_be_returned(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $order = $this->seedEligibleOrder($customer, ['status' => 'canceled']);

        $response = $this->authenticatedGraphQL($customer, $this->query());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($this->rowFor($response->json('data.returnableOrders'), (int) $order->id))->toBeNull();
    }

    public function test_query_never_returns_another_customers_order(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $other = $this->createCustomer();
        $foreign = $this->seedEligibleOrder($other);

        $response = $this->authenticatedGraphQL($customer, $this->query());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($this->rowFor($response->json('data.returnableOrders'), (int) $foreign->id))->toBeNull();
    }

    public function test_query_filters_by_order_number(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $wanted = $this->seedEligibleOrder($customer, ['increment_id' => 'RMA-WANTED-'.uniqid()]);
        $other = $this->seedEligibleOrder($customer, ['increment_id' => 'RMA-OTHER-'.uniqid()]);

        $query = <<<'GQL'
            query ($incrementId: String) {
              returnableOrders(incrementId: $incrementId) {
                _id
                incrementId
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $query, [
            'incrementId' => (string) $wanted->increment_id,
        ]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $rows = $response->json('data.returnableOrders');

        expect($this->rowFor($rows, (int) $wanted->id))->not->toBeNull();
        expect($this->rowFor($rows, (int) $other->id))->toBeNull();
    }

    public function test_query_requires_an_authenticated_customer(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL($this->query());

        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
    }
}
