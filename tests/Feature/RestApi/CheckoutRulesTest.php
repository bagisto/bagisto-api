<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\CartRule\Exceptions\CouponUsageLimitExceededException;
use Webkul\Checkout\Models\Cart;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\Order;

/**
 * The storefront checkout rules the API must apply exactly as core does.
 */
class CheckoutRulesTest extends RestApiTestCase
{
    private array $address = [
        'billingFirstName' => 'John',
        'billingLastName' => 'Doe',
        'billingEmail' => 'john@example.com',
        'billingAddress' => '123 Main St',
        'billingCity' => 'Los Angeles',
        'billingCountry' => 'IN',
        'billingState' => 'UP',
        'billingPostcode' => '201301',
        'billingPhoneNumber' => '2125551234',
        'useForShipping' => true,
    ];

    private function setCoreConfig(string $code, string $value): void
    {
        DB::table('core_config')->where('code', $code)->delete();

        DB::table('core_config')->insert([
            'code' => $code,
            'value' => $value,
            'channel_code' => core()->getRequestedChannelCode(),
            'locale_code' => core()->getRequestedLocaleCode(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->forgetCoreConfigCache();
    }

    /**
     * Send a request carrying only the bearer token, as a real API client does, without
     * signing the customer into the session guard first.
     */
    private function bearerPost(Customer $customer, string $url, array $data = []): TestResponse
    {
        return $this->withHeaders($this->authHeaders($customer))->postJson($url, $data);
    }

    private function guestPost(string $token, string $url, array $data = []): TestResponse
    {
        return $this->withHeaders([
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer '.$token,
        ])->postJson($url, $data);
    }

    private function createActiveCustomer(): Customer
    {
        $this->seedRequiredData();

        return $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
            'status' => 1,
            'is_suspended' => 0,
        ]);
    }

    private function prepareCheckoutCart(Customer $customer, string $paymentMethod = 'cashondelivery'): Product
    {
        $product = $this->createTestProduct()['product'];

        $this->bearerPost($customer, '/api/shop/add-product-in-cart', [
            'productId' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();

        $this->bearerPost($customer, '/api/shop/checkout-addresses', $this->address)->assertCreated();

        $this->bearerPost($customer, '/api/shop/checkout-shipping-methods', [
            'shippingMethod' => 'flatrate_flatrate',
        ])->assertCreated();

        $this->bearerPost($customer, '/api/shop/checkout-payment-methods', [
            'paymentMethod' => $paymentMethod,
        ])->assertCreated();

        return $product;
    }

    public function test_smart_button_payment_method_is_rejected(): void
    {
        $customer = $this->createActiveCustomer();
        $product = $this->createTestProduct()['product'];

        $this->bearerPost($customer, '/api/shop/add-product-in-cart', [
            'productId' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();

        $this->bearerPost($customer, '/api/shop/checkout-addresses', $this->address)->assertCreated();

        $this->bearerPost($customer, '/api/shop/checkout-shipping-methods', [
            'shippingMethod' => 'flatrate_flatrate',
        ])->assertCreated();

        $response = $this->bearerPost($customer, '/api/shop/checkout-payment-methods', [
            'paymentMethod' => 'paypal_smart_button',
        ]);

        expect($response->getStatusCode())->not->toBeIn([200, 201]);
    }

    public function test_disabled_payment_method_is_rejected_at_order_time(): void
    {
        $customer = $this->createActiveCustomer();
        $this->prepareCheckoutCart($customer);

        $this->setCoreConfig('sales.payment_methods.cashondelivery.active', '0');

        $ordersBefore = Order::count();

        $response = $this->bearerPost($customer, '/api/shop/checkout-orders');

        expect($response->getStatusCode())->not->toBeIn([200, 201]);
        expect(Order::count())->toBe($ordersBefore);
    }

    public function test_guest_cannot_check_out_when_guest_checkout_is_disabled(): void
    {
        $this->seedRequiredData();

        $token = $this->publicPost('/api/shop/cart-tokens', ['createNew' => true])->json('cartToken');

        $this->assertNotEmpty($token);

        $product = $this->createTestProduct()['product'];

        $this->guestPost($token, '/api/shop/add-product-in-cart', [
            'productId' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();

        $this->setCoreConfig('sales.checkout.shopping_cart.allow_guest_checkout', '0');

        $response = $this->guestPost($token, '/api/shop/checkout-addresses', $this->address);

        expect($response->getStatusCode())->not->toBeIn([200, 201]);
    }

    public function test_out_of_stock_item_cannot_be_ordered(): void
    {
        $customer = $this->createActiveCustomer();
        $product = $this->prepareCheckoutCart($customer);

        $this->ensureInventory($product, 0);

        $ordersBefore = Order::count();

        $response = $this->bearerPost($customer, '/api/shop/checkout-orders');

        expect($response->getStatusCode())->not->toBeIn([200, 201]);
        expect(Order::count())->toBe($ordersBefore);
    }

    public function test_suspended_customer_cannot_place_an_order(): void
    {
        $customer = $this->createActiveCustomer();
        $this->prepareCheckoutCart($customer);

        $customer->forceFill(['is_suspended' => 1])->save();

        $ordersBefore = Order::count();

        $response = $this->bearerPost($customer, '/api/shop/checkout-orders');

        expect($response->getStatusCode())->not->toBeIn([200, 201]);
        expect(Order::count())->toBe($ordersBefore);
    }

    public function test_cart_rule_for_the_customers_group_applies_to_a_bearer_request(): void
    {
        $customer = $this->createActiveCustomer();
        $product = $this->createTestProduct()['product'];
        $code = 'GROUP_'.strtoupper(uniqid());

        $ruleId = DB::table('cart_rules')->insertGetId([
            'name' => 'Group Rule '.$code,
            'description' => 'Group rule',
            'coupon_type' => '1',
            'use_auto_generation' => '0',
            'usage_per_customer' => '0',
            'uses_per_coupon' => '0',
            'times_used' => 0,
            'condition_type' => '2',
            'end_other_rules' => '0',
            'uses_attribute_conditions' => '0',
            'discount_quantity' => '0',
            'discount_step' => '0',
            'apply_to_shipping' => '0',
            'free_shipping' => '0',
            'sort_order' => 0,
            'status' => '1',
            'action_type' => 'by_percent',
            'discount_amount' => 10,
            'conditions' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (DB::table('channels')->pluck('id') as $channelId) {
            DB::table('cart_rule_channels')->insert(['cart_rule_id' => $ruleId, 'channel_id' => $channelId]);
        }

        DB::table('cart_rule_customer_groups')->insert([
            'cart_rule_id' => $ruleId,
            'customer_group_id' => $customer->customer_group_id,
        ]);

        DB::table('cart_rule_coupons')->insert([
            'cart_rule_id' => $ruleId,
            'code' => $code,
            'usage_limit' => 0,
            'usage_per_customer' => 0,
            'times_used' => 0,
            'is_primary' => 1,
            'type' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->bearerPost($customer, '/api/shop/add-product-in-cart', [
            'productId' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();

        $this->bearerPost($customer, '/api/shop/apply-coupon', ['couponCode' => $code]);

        $cart = Cart::where('customer_id', $customer->id)->where('is_active', 1)->first();

        expect($cart)->not->toBeNull();
        expect((float) $cart->discount_amount)->toBeGreaterThan(0.0);
    }

    public function test_order_is_placed_when_the_minimum_order_amount_is_met(): void
    {
        $customer = $this->createActiveCustomer();
        $this->prepareCheckoutCart($customer);

        $this->setCoreConfig('sales.order_settings.minimum_order.enable', '1');
        $this->setCoreConfig('sales.order_settings.minimum_order.minimum_order_amount', '1');

        $ordersBefore = Order::count();

        $this->bearerPost($customer, '/api/shop/checkout-orders')->assertSuccessful();

        expect(Order::count())->toBe($ordersBefore + 1);
    }

    public function test_a_spent_coupon_is_dropped_and_the_order_can_be_retried(): void
    {
        $customer = $this->createActiveCustomer();
        $this->prepareCheckoutCart($customer);
        $cartId = Cart::where('customer_id', $customer->id)->where('is_active', 1)->value('id');
        Cart::whereKey($cartId)->update(['coupon_code' => 'SPENT']);

        Event::listen('checkout.order.save.before', fn () => throw new CouponUsageLimitExceededException('Coupon used up.'));

        $ordersBefore = Order::count();

        $response = $this->bearerPost($customer, '/api/shop/checkout-orders');

        $response->assertStatus(422);
        expect($response->json('detail'))->toBe(trans('shop::app.checkout.coupon.usage-limit-exceeded'));
        expect(Cart::find($cartId)->coupon_code)->toBeNull();
        expect(Order::count())->toBe($ordersBefore);
    }

    public function test_order_below_the_minimum_order_amount_is_rejected(): void
    {
        $customer = $this->createActiveCustomer();
        $this->prepareCheckoutCart($customer);

        $this->setCoreConfig('sales.order_settings.minimum_order.enable', '1');
        $this->setCoreConfig('sales.order_settings.minimum_order.minimum_order_amount', '99999999');

        $ordersBefore = Order::count();

        $response = $this->bearerPost($customer, '/api/shop/checkout-orders');

        expect($response->getStatusCode())->not->toBeIn([200, 201]);
        expect((string) $response->getContent())->not->toContain('sub_total');
        expect(Order::count())->toBe($ordersBefore);
    }

    public function test_customer_order_carries_the_account_details_and_currencies(): void
    {
        $customer = $this->createActiveCustomer();
        $this->prepareCheckoutCart($customer);

        $orderId = $this->bearerPost($customer, '/api/shop/checkout-orders')
            ->assertSuccessful()
            ->json('orderId');

        $order = Order::find($orderId);

        expect($order)->not->toBeNull();
        expect((int) $order->is_guest)->toBe(0);
        expect($order->customer_email)->toBe($customer->email);
        expect($order->customer_first_name)->toBe($customer->first_name);
        expect($order->customer_last_name)->toBe($customer->last_name);
        expect($order->base_currency_code)->not->toBeEmpty();
        expect($order->channel_currency_code)->not->toBeEmpty();
        expect($order->order_currency_code)->not->toBeEmpty();
    }

    public function test_guest_order_is_flagged_as_guest_with_the_billing_details(): void
    {
        $this->seedRequiredData();

        $token = $this->publicPost('/api/shop/cart-tokens', ['createNew' => true])->json('cartToken');

        $this->assertNotEmpty($token);

        $product = $this->createTestProduct()['product'];

        $this->guestPost($token, '/api/shop/add-product-in-cart', [
            'productId' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();

        $this->guestPost($token, '/api/shop/checkout-addresses', $this->address)->assertCreated();

        $this->guestPost($token, '/api/shop/checkout-shipping-methods', [
            'shippingMethod' => 'flatrate_flatrate',
        ])->assertCreated();

        $this->guestPost($token, '/api/shop/checkout-payment-methods', [
            'paymentMethod' => 'cashondelivery',
        ])->assertCreated();

        $orderId = $this->guestPost($token, '/api/shop/checkout-orders')
            ->assertSuccessful()
            ->json('orderId');

        $order = Order::find($orderId);

        expect($order)->not->toBeNull();
        expect((int) $order->is_guest)->toBe(1);
        expect($order->customer_email)->toBe('john@example.com');
        expect($order->customer_first_name)->toBe('John');
        expect($order->customer_last_name)->toBe('Doe');
        expect($order->base_currency_code)->not->toBeEmpty();
    }

    public function test_buy_now_leaves_a_single_active_cart_holding_only_that_product(): void
    {
        $customer = $this->createActiveCustomer();
        $existing = $this->createTestProduct()['product'];
        $buyNow = $this->createTestProduct()['product'];

        $this->bearerPost($customer, '/api/shop/add-product-in-cart', [
            'productId' => $existing->id,
            'quantity' => 1,
        ])->assertSuccessful();

        $previousCart = Cart::where('customer_id', $customer->id)->where('is_active', 1)->first();

        $this->bearerPost($customer, '/api/shop/add-product-in-cart', [
            'productId' => $buyNow->id,
            'quantity' => 1,
            'isBuyNow' => 1,
        ])->assertSuccessful();

        $activeCarts = Cart::where('customer_id', $customer->id)->where('is_active', 1)->get();

        expect($activeCarts)->toHaveCount(1);
        expect($activeCarts->first()->id)->not->toBe($previousCart->id);
        expect((int) $previousCart->fresh()->is_active)->toBe(0);
        expect($activeCarts->first()->items()->pluck('product_id')->all())->toBe([$buyNow->id]);
    }

    public function test_address_with_a_missing_required_field_is_rejected(): void
    {
        $customer = $this->createActiveCustomer();
        $product = $this->createTestProduct()['product'];

        $this->bearerPost($customer, '/api/shop/add-product-in-cart', [
            'productId' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();

        $address = $this->address;
        unset($address['billingPhoneNumber']);

        $response = $this->bearerPost($customer, '/api/shop/checkout-addresses', $address);

        $response->assertStatus(422);
        expect((string) $response->getContent())->toContain('billingPhoneNumber');
    }

    public function test_saving_the_address_again_resets_the_chosen_shipping_method(): void
    {
        $customer = $this->createActiveCustomer();
        $this->prepareCheckoutCart($customer);

        $cart = Cart::where('customer_id', $customer->id)->where('is_active', 1)->first();

        expect($cart->shipping_method)->toBe('flatrate_flatrate');

        $this->bearerPost($customer, '/api/shop/checkout-addresses', $this->address)->assertCreated();

        expect($cart->fresh()->shipping_method)->toBeNull();
    }
}
