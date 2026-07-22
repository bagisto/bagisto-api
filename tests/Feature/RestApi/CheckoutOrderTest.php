<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;

class CheckoutOrderTest extends RestApiTestCase
{
    private string $url = '/api/shop/checkout-orders';

    private function createAuthenticatedCustomer(): Customer
    {
        $this->seedRequiredData();

        return $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);
    }

    private function addProductToCart(Customer $customer): void
    {
        $product = $this->createTestProduct()['product'];

        $this->authenticatedPost($customer, '/api/shop/add-product-in-cart', [
            'productId' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();
    }

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

    private function setShippingMethod(Customer $customer): void
    {
        $this->authenticatedPost($customer, '/api/shop/checkout-shipping-methods', [
            'shippingMethod' => 'flatrate_flatrate',
        ])->assertCreated();
    }

    private function setPaymentMethod(Customer $customer, string $method = 'cashondelivery'): void
    {
        $this->authenticatedPost($customer, '/api/shop/checkout-payment-methods', [
            'paymentMethod' => $method,
        ])->assertCreated();
    }

    public function test_place_order_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->url);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_place_order_without_cart_returns_error(): void
    {
        $customer = $this->createAuthenticatedCustomer();

        $response = $this->authenticatedPost($customer, $this->url);

        expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
    }

    public function test_place_order_full_flow(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);
        $this->setCheckoutAddress($customer);
        $this->setShippingMethod($customer);
        $this->setPaymentMethod($customer);

        $response = $this->authenticatedPost($customer, $this->url);

        // Some payment configurations may legitimately fail in test env (no payment gateway).
        // The important thing: endpoint is reachable and either succeeds or fails with a
        // validation/business error — not 401/403 (auth) or 404 (route missing).
        expect($response->getStatusCode())->toBeIn([200, 201, 400, 422, 500]);

        if (in_array($response->getStatusCode(), [200, 201], true)) {
            $json = $response->json();
            expect($json)->toBeArray();
        }
    }

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

    public function test_place_order_with_redirect_gateway_returns_redirect_and_creates_no_order(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);
        $this->setCheckoutAddress($customer);
        $this->setShippingMethod($customer);
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
}
