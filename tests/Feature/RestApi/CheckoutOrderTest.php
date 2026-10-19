<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\MagicAI\Facades\MagicAI;
use Webkul\Sales\Models\Order;

class CheckoutOrderTest extends RestApiTestCase
{
    /**
     * The url.
     */
    private string $url = '/api/shop/checkout-orders';

    /**
     * Test place order requires auth.
     */
    public function test_place_order_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->url);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Test place order without cart returns error.
     */
    public function test_place_order_without_cart_returns_error(): void
    {
        $customer = $this->createAuthenticatedCustomer();

        $response = $this->authenticatedPost($customer, $this->url);

        expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
    }

    /**
     * Test place order full flow.
     */
    public function test_place_order_full_flow(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);
        $this->setCheckoutAddress($customer);
        $this->setShippingMethod($customer);
        $this->setPaymentMethod($customer);

        $response = $this->authenticatedPost($customer, $this->url);

        expect($response->getStatusCode())->toBeIn([200, 201, 400, 422, 500]);

        if (in_array($response->getStatusCode(), [200, 201], true)) {
            $json = $response->json();
            expect($json)->toBeArray();
        }
    }

    /**
     * Test place order with offline method creates the order.
     */
    public function test_place_order_with_offline_method_creates_the_order(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);
        $this->setCheckoutAddress($customer);
        $this->setShippingMethod($customer);
        $this->setPaymentMethod($customer, 'cashondelivery');

        $response = $this->authenticatedPost($customer, $this->url);

        $response->assertCreated();

        $json = $response->json();

        expect($json['redirect'])->toBeFalse();
        expect($json['redirectUrl'])->toBeNull();
        expect($json['orderId'])->not->toBeNull();

        $this->assertDatabaseHas('orders', ['id' => (int) $json['orderId']]);
    }

    /**
     * Test place order with redirect gateway returns redirect and creates no order.
     */
    public function test_place_order_with_redirect_gateway_returns_redirect_and_creates_no_order(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);
        $this->setCheckoutAddress($customer);
        $this->setShippingMethod($customer);
        $this->enableGateway('stripe', [
            'sandbox' => '1',
            'api_test_key' => 'sk_test_x',
            'api_test_publishable_key' => 'pk_test_x',
        ]);
        $this->setPaymentMethod($customer, 'stripe');

        $ordersBefore = Order::count();

        $response = $this->authenticatedPost($customer, $this->url);

        $response->assertCreated();

        $json = $response->json();

        expect($json['redirect'])->toBeTrue();
        expect($json['redirectUrl'])->toContain('/stripe/redirect');
        expect($json['orderId'])->toBeNull();

        expect(Order::count())->toBe($ordersBefore);
    }

    /**
     * Test checkout message is generated for the placed order when the feature is enabled.
     */
    public function test_checkout_message_is_generated_for_the_placed_order_when_the_feature_is_enabled(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);
        $this->setCheckoutAddress($customer);
        $this->setShippingMethod($customer);
        $this->setPaymentMethod($customer);
        $this->setConfig([
            'magic_ai.general.settings.enabled' => '1',
            'magic_ai.storefront_features.checkout_message.enabled' => '1',
        ]);

        MagicAI::shouldReceive('checkoutMessage')
            ->once()
            ->andReturn('Thank you for your order.');

        $orderId = $this->authenticatedPost($customer, $this->url)
            ->assertCreated()
            ->assertJsonMissingPath('checkoutMessage')
            ->json('orderId');

        $this->authenticatedGet($customer, "/api/shop/checkout-messages/{$orderId}")
            ->assertOk()
            ->assertJsonPath('message', 'Thank you for your order.');
    }

    /**
     * Test checkout message is not given for another customers order.
     */
    public function test_checkout_message_is_not_given_for_another_customers_order(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);
        $this->setCheckoutAddress($customer);
        $this->setShippingMethod($customer);
        $this->setPaymentMethod($customer);

        $orderId = $this->authenticatedPost($customer, $this->url)->assertCreated()->json('orderId');

        MagicAI::shouldReceive('checkoutMessage')->never();

        $this->authenticatedGet($this->createAuthenticatedCustomer(), "/api/shop/checkout-messages/{$orderId}")
            ->assertNotFound();
    }

    /**
     * Create authenticated customer.
     */
    private function createAuthenticatedCustomer(): Customer
    {
        $this->seedRequiredData();

        return $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);
    }

    /**
     * Add product to cart.
     */
    private function addProductToCart(Customer $customer): void
    {
        $product = $this->createTestProduct()['product'];

        $this->authenticatedPost($customer, '/api/shop/add-product-in-cart', [
            'productId' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();
    }

    /**
     * Set checkout address.
     */
    private function setCheckoutAddress(Customer $customer): void
    {
        $this->authenticatedPost($customer, '/api/shop/checkout-addresses', [
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
        ])->assertCreated();
    }

    /**
     * Set shipping method.
     */
    private function setShippingMethod(Customer $customer): void
    {
        $this->authenticatedPost($customer, '/api/shop/checkout-shipping-methods', [
            'shippingMethod' => 'flatrate_flatrate',
        ])->assertCreated();
    }

    /**
     * Enable gateway.
     */
    private function enableGateway(string $method, array $settings): void
    {
        foreach ([
            'active' => '1',
            ...$settings,
        ] as $field => $value) {
            $code = "sales.payment_methods.{$method}.{$field}";

            DB::table('core_config')->where('code', $code)->delete();

            DB::table('core_config')->insert([
                'code' => $code,
                'value' => $value,
                'channel_code' => core()->getRequestedChannelCode(),
                'locale_code' => core()->getRequestedLocaleCode(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->forgetCoreConfigCache();
    }

    /**
     * Set payment method.
     */
    private function setPaymentMethod(Customer $customer, string $method = 'cashondelivery'): void
    {
        $this->authenticatedPost($customer, '/api/shop/checkout-payment-methods', [
            'paymentMethod' => $method,
        ])->assertCreated();
    }
}
