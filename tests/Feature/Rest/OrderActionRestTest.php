<?php

namespace Tests\Feature\BagistoApi\Rest;

use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;
use Webkul\Product\Models\Product;

/**
 * Use the REST test helper to test Cancel and Reorder endpoints.
 */
uses(\Tests\Feature\BagistoApi\RestApiTestCase::class);

/**
 * Helper to create a test order for a customer.
 */
function createRestTestOrder($test, $customer, $status = 'pending') {
    $product = Product::factory()->create();
    
    $order = Order::factory()->create([
        'customer_id'         => $customer->id,
        'customer_email'      => $customer->email,
        'customer_first_name' => $customer->first_name,
        'customer_last_name'  => $customer->last_name,
        'status'              => $status,
    ]);

    OrderItem::factory()->create([
        'order_id'   => $order->id,
        'product_id' => $product->id,
        'qty_ordered' => 1,
    ]);

    OrderPayment::factory()->create(['order_id' => $order->id]);

    return $order;
}

test('REST: customer can cancel their own pending order', function () {
    $this->seedRequiredData();
    $customer = $this->createCustomer();
    $order = createRestTestOrder($this, $customer, 'pending');

    $response = $this->authenticatedPost($customer, '/api/shop/cancel-order', [
        'orderId' => $order->id
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('status', 'canceled');
});

test('REST: customer can reorder from a previous order', function () {
    $this->seedRequiredData();
    $customer = $this->createCustomer();
    $order = createRestTestOrder($this, $customer, 'completed');

    $response = $this->authenticatedPost($customer, '/api/shop/reorder', [
        'orderId' => $order->id
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);
});
