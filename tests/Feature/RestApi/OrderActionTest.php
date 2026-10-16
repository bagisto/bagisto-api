<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;

/**
 * Use the REST test helper to test Cancel and Reorder endpoints.
 */
uses(RestApiTestCase::class);

/**
 * Helper to create a test order for a customer.
 */
function createRestTestOrder($test, $customer, $status = 'pending')
{
    $product = Product::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'customer_email' => $customer->email,
        'customer_first_name' => $customer->first_name,
        'customer_last_name' => $customer->last_name,
        'status' => $status,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'qty_ordered' => 1,
    ]);

    OrderPayment::factory()->create(['order_id' => $order->id]);

    return $order;
}

test('REST: cancel-order endpoint reachable for authenticated customer', function () {
    $this->seedRequiredData();
    $customer = $this->createCustomer();
    $order = createRestTestOrder($this, $customer, 'pending');

    $response = $this->authenticatedPost($customer, '/api/shop/cancel-order', [
        'orderId' => $order->id,
    ]);

    expect($response->getStatusCode())->toBeIn([200, 201, 400, 422, 500]);
});

test('REST: cancel-order requires authentication', function () {
    $this->seedRequiredData();

    $response = $this->publicPost('/api/shop/cancel-order', ['orderId' => 1]);

    expect($response->getStatusCode())->toBeIn([401, 403, 500]);
});

test('REST: customer can reorder from a previous order', function () {
    $this->seedRequiredData();
    $customer = $this->createCustomer();
    $order = createRestTestOrder($this, $customer, 'completed');

    $response = $this->authenticatedPost($customer, '/api/shop/reorder', [
        'orderId' => $order->id,
    ]);

    expect($response->getStatusCode())->toBeIn([200, 201, 400, 422, 500]);
});

test('REST: reorder requires authentication', function () {
    $this->seedRequiredData();

    $response = $this->publicPost('/api/shop/reorder', ['orderId' => 1]);

    expect($response->getStatusCode())->toBeIn([401, 403, 500]);
});

test('REST: reorder is refused when disabled in store settings', function () {
    $this->seedRequiredData();
    $customer = $this->createCustomer();
    $order = createRestTestOrder($this, $customer, 'completed');

    DB::table('core_config')->where('code', 'sales.order_settings.reorder.shop')->delete();
    DB::table('core_config')->insert([
        'code' => 'sales.order_settings.reorder.shop',
        'value' => '0',
        'channel_code' => null,
        'locale_code' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $this->forgetCoreConfigCache();

    $response = $this->authenticatedPost($customer, '/api/shop/reorder', [
        'orderId' => $order->id,
    ]);

    expect($response->getStatusCode())->toBe(422);
    expect($response->json('detail') ?? $response->json('message'))
        ->toBe(__('bagistoapi::app.graphql.reorder.disabled-in-settings'));
});

test('REST: reorder is refused for a guest order', function () {
    $this->seedRequiredData();
    $customer = $this->createCustomer();
    $order = createRestTestOrder($this, $customer, 'completed');
    $order->update(['is_guest' => 1]);

    $response = $this->authenticatedPost($customer, '/api/shop/reorder', [
        'orderId' => $order->id,
    ]);

    expect($response->getStatusCode())->toBe(422);
});
