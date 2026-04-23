<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\Customer;

class CheckoutShippingPaymentTest extends RestApiTestCase
{
    /**
     * Add a product to the customer's cart so there is an active cart to work on.
     */
    private function addProductToCart(Customer $customer): void
    {
        $product = $this->createTestProduct()['product'];

        $response = $this->authenticatedPost($customer, '/api/shop/add-product-in-cart', [
            'productId' => $product->id,
            'quantity'  => 1,
        ]);

        $response->assertSuccessful();
    }

    /**
     * Set billing + shipping address so the cart is ready for shipping/payment steps.
     */
    private function setAddress(Customer $customer): void
    {
        $response = $this->authenticatedPost($customer, '/api/shop/checkout-addresses', [
            'billingFirstName'   => 'John',
            'billingLastName'    => 'Doe',
            'billingEmail'       => 'john@example.com',
            'billingAddress'     => '123 Main St',
            'billingCity'        => 'Los Angeles',
            'billingCountry'     => 'IN',
            'billingState'       => 'UP',
            'billingPostcode'    => '201301',
            'billingPhoneNumber' => '2125551234',
            'useForShipping'     => true,
        ]);

        $response->assertCreated();
    }

    private function createAuthenticatedCustomer(): Customer
    {
        $this->seedRequiredData();

        return $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);
    }

    public function test_get_shipping_rates(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);
        $this->setAddress($customer);

        $response = $this->authenticatedGet($customer, '/api/shop/checkout-shipping-methods');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_get_shipping_rates_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/checkout-shipping-methods');

        expect(in_array($response->getStatusCode(), [401, 403, 404, 500]))->toBeTrue();
    }

    public function test_set_shipping_method(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);
        $this->setAddress($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/checkout-shipping-methods', [
            'shippingMethod' => 'flatrate_flatrate',
        ]);

        $response->assertCreated();

        $json = $response->json();
        expect($json)->toHaveKey('success');
        expect($json['success'])->toBeTrue();
        expect($json['shippingMethod'])->toBe('flatrate_flatrate');
    }

    public function test_set_invalid_shipping_method_returns_error(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);
        $this->setAddress($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/checkout-shipping-methods', [
            'shippingMethod' => 'nope_nope',
        ]);

        expect(in_array($response->getStatusCode(), [400, 422, 500]))->toBeTrue();
    }

    public function test_get_payment_methods(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);

        $response = $this->authenticatedGet($customer, '/api/shop/payment-methods');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_get_payment_methods_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/payment-methods');

        expect(in_array($response->getStatusCode(), [401, 403, 404, 500]))->toBeTrue();
    }

    public function test_set_payment_method(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);
        $this->setAddress($customer);

        // Need shipping method before payment in the usual flow
        $this->authenticatedPost($customer, '/api/shop/checkout-shipping-methods', [
            'shippingMethod' => 'flatrate_flatrate',
        ]);

        $response = $this->authenticatedPost($customer, '/api/shop/checkout-payment-methods', [
            'paymentMethod'     => 'moneytransfer',
            'paymentSuccessUrl' => 'https://example.com/success',
            'paymentFailureUrl' => 'https://example.com/failure',
            'paymentCancelUrl'  => 'https://example.com/cancel',
        ]);

        $response->assertCreated();

        $json = $response->json();
        expect($json)->toHaveKey('success');
        expect($json['success'])->toBeTrue();
        expect($json['paymentMethod'])->toBe('moneytransfer');
    }

    public function test_set_invalid_payment_method_returns_error(): void
    {
        $customer = $this->createAuthenticatedCustomer();
        $this->addProductToCart($customer);
        $this->setAddress($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/checkout-payment-methods', [
            'paymentMethod' => 'nope',
        ]);

        expect(in_array($response->getStatusCode(), [400, 422, 500]))->toBeTrue();
    }
}
