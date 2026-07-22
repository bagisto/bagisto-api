<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Checkout\Models\CartAddress;

class CheckoutAddressTest extends RestApiTestCase
{
    private function guestPostWithToken(string $url, string $token, array $payload): TestResponse
    {
        return $this->withHeaders([
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer '.$token,
        ])->postJson($url, $payload);
    }

    /**
     * Add product to customer's cart via REST so we have an active cart to set addresses on.
     */
    private function addProductToCart($customer): void
    {
        $product = $this->createTestProduct()['product'];

        $response = $this->authenticatedPost($customer, '/api/shop/add-product-in-cart', [
            'productId' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertSuccessful();
    }

    public function test_address_response_returns_the_real_guest_cart_token(): void
    {
        $this->seedRequiredData();

        $tokenResponse = $this->publicPost('/api/shop/cart-tokens', ['createNew' => true]);

        expect($tokenResponse->getStatusCode())->toBeIn([200, 201]);

        $cartToken = $tokenResponse->json('cartToken') ?? $tokenResponse->json('sessionToken');

        $this->assertNotEmpty($cartToken);

        $product = $this->createTestProduct()['product'];

        $this->guestPostWithToken('/api/shop/add-product-in-cart', $cartToken, [
            'productId' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();

        $response = $this->guestPostWithToken('/api/shop/checkout-addresses', $cartToken, [
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
        ]);

        $response->assertCreated();

        expect($response->json('cartToken'))->toBe($cartToken);
    }

    public function test_address_response_returns_no_cart_token_for_a_customer_cart(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);
        $this->addProductToCart($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/checkout-addresses', [
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
        ]);

        $response->assertCreated();

        expect($response->json('cartToken'))->toBeNull();
        expect($response->json('cartToken'))->not->toBe((string) $customer->id);
    }

    public function test_set_checkout_address_with_use_for_shipping(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);
        $this->addProductToCart($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/checkout-addresses', [
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
        ]);

        $response->assertCreated();

        $json = $response->json();
        expect($json)->toHaveKey('billingFirstName');
        expect($json['billingFirstName'])->toBe('John');
        expect($json['success'])->toBeTrue();

        expect(CartAddress::where('address_type', CartAddress::ADDRESS_TYPE_BILLING)
            ->where('email', 'john@example.com')
            ->exists())->toBeTrue();
        expect(CartAddress::where('address_type', CartAddress::ADDRESS_TYPE_SHIPPING)
            ->where('email', 'john@example.com')
            ->exists())->toBeTrue();
    }

    public function test_set_checkout_address_with_different_shipping_address(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);
        $this->addProductToCart($customer);

        $response = $this->authenticatedPost($customer, '/api/shop/checkout-addresses', [
            'billingFirstName' => 'John',
            'billingLastName' => 'Doe',
            'billingEmail' => 'john@example.com',
            'billingAddress' => '123 Main St',
            'billingCity' => 'Los Angeles',
            'billingCountry' => 'IN',
            'billingState' => 'UP',
            'billingPostcode' => '201301',
            'billingPhoneNumber' => '2125551234',
            'shippingFirstName' => 'Jane',
            'shippingLastName' => 'Doe',
            'shippingEmail' => 'jane@example.com',
            'shippingAddress' => '456 Oak Ave',
            'shippingCity' => 'San Francisco',
            'shippingCountry' => 'IN',
            'shippingState' => 'UP',
            'shippingPostcode' => '201302',
            'shippingPhoneNumber' => '4155559876',
            'useForShipping' => false,
        ]);

        $response->assertCreated();

        $json = $response->json();
        expect($json['shippingFirstName'])->toBe('Jane');
        expect($json['billingFirstName'])->toBe('John');

        expect(CartAddress::where('address_type', CartAddress::ADDRESS_TYPE_SHIPPING)
            ->where('email', 'jane@example.com')
            ->exists())->toBeTrue();
    }

    public function test_set_checkout_address_without_auth_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/checkout-addresses', [
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
        ]);

        expect(in_array($response->getStatusCode(), [400, 401, 403, 500]))->toBeTrue();
    }
}
