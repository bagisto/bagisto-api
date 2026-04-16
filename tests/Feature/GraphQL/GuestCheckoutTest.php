<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;

class GuestCheckoutTest extends GraphQLTestCase
{
    /**
     * Get guest cart token from the createCart mutation response
     */
    private function getGuestCartToken(): string
    {
        $mutation = <<<'GQL'
            mutation createCart {
              createCartToken(input: {}) {
                cartToken {
                  id
                  _id
                  cartToken
                  customerId
                  channelId
                  itemsCount
                  subtotal
                  baseSubtotal
                  discountAmount
                  baseDiscountAmount
                  taxAmount
                  baseTaxAmount
                  shippingAmount
                  baseShippingAmount
                  grandTotal
                  baseGrandTotal
                  formattedSubtotal
                  formattedDiscountAmount
                  formattedTaxAmount
                  formattedShippingAmount
                  formattedGrandTotal
                  couponCode
                  success
                  message
                  sessionToken
                  isGuest
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation);
        $response->assertSuccessful();

        $data = $response->json('data.createCartToken.cartToken');

        $this->assertNotNull($data, 'cartToken response is null');
        $this->assertTrue((bool) ($data['success'] ?? false));

        // Use cartToken as the bearer token
        $token = $data['cartToken'] ?? null;
        $this->assertNotEmpty($token, 'guest cart token is missing');

        return $token;
    }

    /**
     * Helper method to get authorization headers with guest cart token
     */
    private function guestHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }

    /**
     * Add product to cart first for checkout tests
     */
    private function addProductToCart(string $token): int
    {
        // Use test product helper to get a product with inventory
        $productData = $this->createTestProduct();
        $product = $productData['product'];

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) {
                addProductInCart {
                  id
                  itemsCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => $product->id,
            'quantity'  => 1,
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        return $product->id;
    }

    /**
     * Get Shipping Methods
     */
    public function test_get_shipping_methods(): void
    {
        $token = $this->getGuestCartToken();

        $query = <<<'GQL'
            query checkoutShippingRates {
              collectionShippingRates {
                _id
                id
                code
                description
                method
                price
                label
              }
            }
        GQL;

        $response = $this->graphQL($query, [], $this->guestHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.collectionShippingRates');

        $this->assertNotNull($data, 'shipping rates response is null');
        $this->assertIsArray($data, 'shipping rates is not an array');
    }

    /**
     * Get Payment Methods
     */
    public function test_get_payment_methods(): void
    {
        $token = $this->getGuestCartToken();

        $query = <<<'GQL'
            query checkoutPaymentMethods {
              collectionPaymentMethods {
                id
                _id
                method
                title
                description
                icon
                isAllowed
              }
            }
        GQL;

        $response = $this->graphQL($query, [], $this->guestHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.collectionPaymentMethods');

        $this->assertNotNull($data, 'payment methods response is null');
        $this->assertIsArray($data, 'payment methods is not an array');
    }

    /**
     * Set Billing/Shipping Address (Guest)
     */
    public function test_set_checkout_address(): void
    {
        $token = $this->getGuestCartToken();

        // First add product to cart
        $this->addProductToCart($token);

        $headers = $this->guestHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutAddress(
                $billingFirstName: String!
                $billingLastName: String!
                $billingEmail: String!
                $billingAddress: String!
                $billingCity: String!
                $billingCountry: String!
                $billingState: String!
                $billingPostcode: String!
                $billingPhoneNumber: String!
                $useForShipping: Boolean
            ) {
              createCheckoutAddress(
                input: {
                  billingFirstName: $billingFirstName
                  billingLastName: $billingLastName
                  billingEmail: $billingEmail
                  billingAddress: $billingAddress
                  billingCity: $billingCity
                  billingCountry: $billingCountry
                  billingState: $billingState
                  billingPostcode: $billingPostcode
                  billingPhoneNumber: $billingPhoneNumber
                  useForShipping: $useForShipping
                }
              ) {
                checkoutAddress {
                  _id
                  success
                  message
                  id
                  cartToken
                  billingFirstName
                  billingLastName
                  billingAddress
                  billingCity
                  billingState
                  billingPostcode
                  billingPhoneNumber
                  shippingFirstName
                  shippingLastName
                  shippingCity
                }
              }
            }
        GQL;

        $variables = [
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
        ];

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createCheckoutAddress.checkoutAddress');

        $this->assertNotNull($data, 'checkout address response is null');
        $this->assertArrayHasKey('_id', $data);
        $this->assertArrayHasKey('success', $data);
    }

    /**
     * Set Shipping Method (Guest)
     */
    public function test_set_shipping_method(): void
    {
        $token = $this->getGuestCartToken();

        // First add product to cart and set address
        $this->addProductToCart($token);

        // Set address first
        $this->setCheckoutAddress($token);

        $headers = $this->guestHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutShippingMethod(
                $shippingMethod: String!
            ) {
              createCheckoutShippingMethod(
                input: {
                  shippingMethod: $shippingMethod
                }
              ) {
                checkoutShippingMethod {
                  success
                  id
                  message
                }
              }
            }
        GQL;

        $variables = [
            'shippingMethod' => 'flatrate_flatrate',
        ];

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createCheckoutShippingMethod.checkoutShippingMethod');

        $this->assertNotNull($data, 'checkout shipping method response is null');
        $this->assertArrayHasKey('success', $data);
    }

    /**
     * Set Payment Method (Guest)
     */
    public function test_set_payment_method(): void
    {
        $token = $this->getGuestCartToken();

        // First add product to cart, set address, and set shipping
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);
        $this->setShippingMethod($token);

        $headers = $this->guestHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutPaymentMethod(
                $paymentMethod: String!,
                $successUrl: String,
                $failureUrl: String,
                $cancelUrl: String
            ) {
              createCheckoutPaymentMethod(
                input: {
                    paymentMethod: $paymentMethod,
                    paymentSuccessUrl: $successUrl,
                    paymentFailureUrl: $failureUrl,
                    paymentCancelUrl: $cancelUrl
                }
              ) {
                checkoutPaymentMethod {
                  success
                  message
                  paymentGatewayUrl
                  paymentData
                }
              }
            }
        GQL;

        $variables = [
            'paymentMethod' => 'moneytransfer',
            'successUrl'    => 'https://myapp.com/payment/success',
            'failureUrl'    => 'https://myapp.com/payment/failure',
            'cancelUrl'     => 'https://myapp.com/payment/cancel',
        ];

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createCheckoutPaymentMethod.checkoutPaymentMethod');

        $this->assertNotNull($data, 'checkout payment method response is null');
        $this->assertArrayHasKey('success', $data);
    }

    /**
     * Place Order (Guest)
     */
    public function test_place_order(): void
    {
        $token = $this->getGuestCartToken();

        // First add product to cart, set address, shipping and payment
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);
        $this->setShippingMethod($token);
        $this->setPaymentMethod($token);

        $headers = $this->guestHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutOrder {
              createCheckoutOrder(input:{}) {
                checkoutOrder {
                  id
                  orderId
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [], $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createCheckoutOrder.checkoutOrder');

        $this->assertNotNull($data, 'checkout order response is null');
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('orderId', $data);
    }

    /**
     * Set billing address only with separate shipping address fields
     */
    public function test_set_checkout_address_billing_only_with_separate_shipping(): void
    {
        $token = $this->getGuestCartToken();
        $this->addProductToCart($token);

        $mutation = <<<'GQL'
            mutation createCheckoutAddress(
                $billingFirstName: String!
                $billingLastName: String!
                $billingEmail: String!
                $billingAddress: String!
                $billingCity: String!
                $billingCountry: String!
                $billingState: String!
                $billingPostcode: String!
                $billingPhoneNumber: String!
                $shippingFirstName: String
                $shippingLastName: String
                $shippingAddress: String
                $shippingCity: String
                $shippingCountry: String
                $shippingState: String
                $shippingPostcode: String
                $shippingPhoneNumber: String
            ) {
              createCheckoutAddress(
                input: {
                  billingFirstName: $billingFirstName
                  billingLastName: $billingLastName
                  billingEmail: $billingEmail
                  billingAddress: $billingAddress
                  billingCity: $billingCity
                  billingCountry: $billingCountry
                  billingState: $billingState
                  billingPostcode: $billingPostcode
                  billingPhoneNumber: $billingPhoneNumber
                  shippingFirstName: $shippingFirstName
                  shippingLastName: $shippingLastName
                  shippingAddress: $shippingAddress
                  shippingCity: $shippingCity
                  shippingCountry: $shippingCountry
                  shippingState: $shippingState
                  shippingPostcode: $shippingPostcode
                  shippingPhoneNumber: $shippingPhoneNumber
                  useForShipping: false
                }
              ) {
                checkoutAddress {
                  _id
                  success
                  billingFirstName
                  shippingFirstName
                  shippingCity
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'billingFirstName'   => 'John',
            'billingLastName'    => 'Doe',
            'billingEmail'       => 'john@example.com',
            'billingAddress'     => '123 Main St',
            'billingCity'        => 'Los Angeles',
            'billingCountry'     => 'IN',
            'billingState'       => 'UP',
            'billingPostcode'    => '201301',
            'billingPhoneNumber' => '2125551234',
            'shippingFirstName'  => 'Jane',
            'shippingLastName'   => 'Doe',
            'shippingAddress'    => '456 Elm St',
            'shippingCity'       => 'Mumbai',
            'shippingCountry'    => 'IN',
            'shippingState'      => 'MH',
            'shippingPostcode'   => '400001',
            'shippingPhoneNumber'=> '9876543210',
        ], $this->guestHeaders($token));

        $response->assertSuccessful();
        $data = $response->json('data.createCheckoutAddress.checkoutAddress');
        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertSame('John', $data['billingFirstName'] ?? null);
        $this->assertSame('Jane', $data['shippingFirstName'] ?? null);
    }

    /**
     * Set checkout address without token returns error
     */
    public function test_set_checkout_address_without_token_returns_error(): void
    {
        $mutation = <<<'GQL'
            mutation createCheckoutAddress(
                $billingFirstName: String!
                $billingEmail: String!
                $billingAddress: String!
                $billingCity: String!
                $billingCountry: String!
                $billingState: String!
                $billingPostcode: String!
                $billingPhoneNumber: String!
            ) {
              createCheckoutAddress(
                input: {
                  billingFirstName: $billingFirstName
                  billingLastName: "Doe"
                  billingEmail: $billingEmail
                  billingAddress: $billingAddress
                  billingCity: $billingCity
                  billingCountry: $billingCountry
                  billingState: $billingState
                  billingPostcode: $billingPostcode
                  billingPhoneNumber: $billingPhoneNumber
                  useForShipping: true
                }
              ) {
                checkoutAddress {
                  success
                }
              }
            }
        GQL;

        // No Authorization header
        $response = $this->graphQL($mutation, [
            'billingFirstName'   => 'John',
            'billingEmail'       => 'john@example.com',
            'billingAddress'     => '123 Main St',
            'billingCity'        => 'Los Angeles',
            'billingCountry'     => 'IN',
            'billingState'       => 'UP',
            'billingPostcode'    => '201301',
            'billingPhoneNumber' => '2125551234',
        ]);

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Set shipping method with invalid code returns error
     */
    public function test_set_invalid_shipping_method_returns_error(): void
    {
        $token = $this->getGuestCartToken();
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);

        $mutation = <<<'GQL'
            mutation createCheckoutShippingMethod($shippingMethod: String!) {
              createCheckoutShippingMethod(input: {shippingMethod: $shippingMethod}) {
                checkoutShippingMethod {
                  success
                  message
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'shippingMethod' => 'invalid_nonexistent_method',
        ], $this->guestHeaders($token));

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Set shipping method without token returns error
     */
    public function test_set_shipping_method_without_token_returns_error(): void
    {
        $mutation = <<<'GQL'
            mutation createCheckoutShippingMethod($shippingMethod: String!) {
              createCheckoutShippingMethod(input: {shippingMethod: $shippingMethod}) {
                checkoutShippingMethod {
                  success
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['shippingMethod' => 'flatrate_flatrate']);

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Set invalid payment method returns error
     */
    public function test_set_invalid_payment_method_returns_error(): void
    {
        $token = $this->getGuestCartToken();
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);
        $this->setShippingMethod($token);

        $mutation = <<<'GQL'
            mutation createCheckoutPaymentMethod($paymentMethod: String!) {
              createCheckoutPaymentMethod(input: {paymentMethod: $paymentMethod}) {
                checkoutPaymentMethod {
                  success
                  message
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'paymentMethod' => 'invalid_nonexistent_gateway',
        ], $this->guestHeaders($token));

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Set payment method without token returns error
     */
    public function test_set_payment_method_without_token_returns_error(): void
    {
        $mutation = <<<'GQL'
            mutation createCheckoutPaymentMethod($paymentMethod: String!) {
              createCheckoutPaymentMethod(input: {paymentMethod: $paymentMethod}) {
                checkoutPaymentMethod {
                  success
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['paymentMethod' => 'moneytransfer']);

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Place order without token returns error
     */
    public function test_place_order_without_token_returns_error(): void
    {
        $mutation = <<<'GQL'
            mutation createCheckoutOrder {
              createCheckoutOrder(input:{}) {
                checkoutOrder {
                  id
                  orderId
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation);

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Place order with empty cart returns error
     */
    public function test_place_order_with_empty_cart_returns_error(): void
    {
        // Cart created but no product added
        $token = $this->getGuestCartToken();

        $mutation = <<<'GQL'
            mutation createCheckoutOrder {
              createCheckoutOrder(input:{}) {
                checkoutOrder {
                  id
                  orderId
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [], $this->guestHeaders($token));

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Place order without billing address returns error
     */
    public function test_place_order_without_billing_address_returns_error(): void
    {
        $token = $this->getGuestCartToken();
        $this->addProductToCart($token);
        // Deliberately skip setCheckoutAddress

        $mutation = <<<'GQL'
            mutation createCheckoutOrder {
              createCheckoutOrder(input:{}) {
                checkoutOrder {
                  id
                  orderId
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [], $this->guestHeaders($token));

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Place order without payment method returns error
     */
    public function test_place_order_without_payment_method_returns_error(): void
    {
        $token = $this->getGuestCartToken();
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);
        $this->setShippingMethod($token);
        // Deliberately skip setPaymentMethod

        $mutation = <<<'GQL'
            mutation createCheckoutOrder {
              createCheckoutOrder(input:{}) {
                checkoutOrder {
                  id
                  orderId
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [], $this->guestHeaders($token));

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Helper to set checkout address
     */
    private function setCheckoutAddress(string $token): void
    {
        $headers = $this->guestHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutAddress(
                $billingFirstName: String!
                $billingLastName: String!
                $billingEmail: String!
                $billingAddress: String!
                $billingCity: String!
                $billingCountry: String!
                $billingState: String!
                $billingPostcode: String!
                $billingPhoneNumber: String!
                $useForShipping: Boolean
            ) {
              createCheckoutAddress(
                input: {
                  billingFirstName: $billingFirstName
                  billingLastName: $billingLastName
                  billingEmail: $billingEmail
                  billingAddress: $billingAddress
                  billingCity: $billingCity
                  billingCountry: $billingCountry
                  billingState: $billingState
                  billingPostcode: $billingPostcode
                  billingPhoneNumber: $billingPhoneNumber
                  useForShipping: $useForShipping
                }
              ) {
                checkoutAddress {
                  _id
                  success
                }
              }
            }
        GQL;

        $variables = [
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
        ];

        $this->graphQL($query, $variables, $headers);
    }

    /**
     * Helper to set shipping method
     */
    private function setShippingMethod(string $token): void
    {
        $headers = $this->guestHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutShippingMethod(
                $shippingMethod: String!
            ) {
              createCheckoutShippingMethod(
                input: {
                  shippingMethod: $shippingMethod
                }
              ) {
                checkoutShippingMethod {
                  success
                }
              }
            }
        GQL;

        $variables = [
            'shippingMethod' => 'flatrate_flatrate',
        ];

        $this->graphQL($query, $variables, $headers);
    }

    /**
     * Helper to set payment method
     */
    private function setPaymentMethod(string $token): void
    {
        $headers = $this->guestHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutPaymentMethod(
                $paymentMethod: String!
            ) {
              createCheckoutPaymentMethod(
                input: {
                    paymentMethod: $paymentMethod
                }
              ) {
                checkoutPaymentMethod {
                  success
                }
              }
            }
        GQL;

        $variables = [
            'paymentMethod' => 'moneytransfer',
        ];

        $this->graphQL($query, $variables, $headers);
    }
}
