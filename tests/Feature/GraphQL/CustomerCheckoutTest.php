<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Sales\Models\Order;

class CustomerCheckoutTest extends GraphQLTestCase
{
    /**
     * Helper method to get authorization headers with customer token
     */
    private function customerHeaders(string $token): array
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
            'quantity' => 1,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        return $product->id;
    }

    /**
     * Get Checkout Addresses (Customer)
     */
    public function test_get_checkout_addresses(): void
    {
        // First add product to cart to create a cart
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];

        // Add product to cart first to create a cart
        $this->addProductToCart($token);

        $query = <<<'GQL'
            query collectionGetCheckoutAddresses {
              collectionGetCheckoutAddresses {
                edges {
                  node {
                    id
                    _id
                    addressType
                    parentAddressId
                    firstName
                    lastName
                    gender
                    companyName
                    address
                    city
                    state
                    country
                    postcode
                    email
                    phone
                    vatId
                    defaultAddress
                    useForShipping
                    additional
                    createdAt
                    updatedAt
                    name
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [], $this->customerHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.collectionGetCheckoutAddresses');

        $this->assertNotNull($data, 'checkout addresses response is null');
        $this->assertIsArray($data['edges'] ?? [], 'edges is not an array');
    }

    /**
     * Get Shipping Methods (Customer)
     */
    public function test_get_shipping_methods(): void
    {
        // Use createTestCustomer to get customer token
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];

        // Add product to cart first
        $this->addProductToCart($token);

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

        $response = $this->graphQL($query, [], $this->customerHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.collectionShippingRates');

        $this->assertNotNull($data, 'shipping rates response is null');
        $this->assertIsArray($data, 'shipping rates is not an array');
    }

    /**
     * Get Payment Methods (Customer)
     */
    public function test_get_payment_methods(): void
    {
        // Use createTestCustomer to get customer token
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];

        // Add product to cart first
        $this->addProductToCart($token);

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

        $response = $this->graphQL($query, [], $this->customerHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.collectionPaymentMethods');

        $this->assertNotNull($data, 'payment methods response is null');
        $this->assertIsArray($data, 'payment methods is not an array');
    }

    /**
     * Set Billing/Shipping Address (Customer)
     */
    public function test_set_checkout_address(): void
    {
        // Use createTestCustomer to get customer token
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];

        // First add product to cart
        $this->addProductToCart($token);

        $headers = $this->customerHeaders($token);

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

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createCheckoutAddress.checkoutAddress');

        $this->assertNotNull($data, 'checkout address response is null');
        $this->assertArrayHasKey('_id', $data);
        $this->assertArrayHasKey('success', $data);
    }

    /**
     * Set Shipping Method (Customer)
     */
    public function test_set_shipping_method(): void
    {
        // Use createTestCustomer to get customer token
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];

        // First add product to cart and set address
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);

        $headers = $this->customerHeaders($token);

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
     * Set Payment Method (Customer)
     */
    public function test_set_payment_method(): void
    {
        // Use createTestCustomer to get customer token
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];

        // First add product to cart, set address, and set shipping
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);
        $this->setShippingMethod($token);

        $headers = $this->customerHeaders($token);

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
            'successUrl' => 'https://myapp.com/payment/success',
            'failureUrl' => 'https://myapp.com/payment/failure',
            'cancelUrl' => 'https://myapp.com/payment/cancel',
        ];

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createCheckoutPaymentMethod.checkoutPaymentMethod');

        $this->assertNotNull($data, 'checkout payment method response is null');
        $this->assertArrayHasKey('success', $data);
    }

    /**
     * Place Order (Customer)
     */
    public function test_place_order(): void
    {
        // Use createTestCustomer to get customer token
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];

        // First add product to cart, set address, shipping and payment
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);
        $this->setShippingMethod($token);
        $this->setPaymentMethod($token);

        $headers = $this->customerHeaders($token);

        $query = <<<'GQL'
            mutation createCheckoutOrder {
              createCheckoutOrder(input:{}) {
                checkoutOrder {
                  id
                  orderId
                  success
                  message
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

        // success/message must be populated on the order-placed path (not null).
        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    /**
     * Get checkout addresses without token returns error.
     */
    public function test_get_checkout_addresses_without_token_returns_error(): void
    {
        $query = <<<'GQL'
            query collectionGetCheckoutAddresses {
              collectionGetCheckoutAddresses {
                edges {
                  node {
                    id
                    firstName
                    lastName
                  }
                }
              }
            }
        GQL;

        // No Authorization header
        $response = $this->graphQL($query);

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Get checkout addresses returns empty list when customer has no addresses set.
     */
    public function test_get_checkout_addresses_empty_when_none_set(): void
    {
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];

        $this->addProductToCart($token);

        $query = <<<'GQL'
            query collectionGetCheckoutAddresses {
              collectionGetCheckoutAddresses {
                edges {
                  node {
                    id
                    firstName
                    lastName
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [], $this->customerHeaders($token));

        $response->assertSuccessful();
        $data = $response->json('data.collectionGetCheckoutAddresses');
        $this->assertNotNull($data);
        $this->assertIsArray($data['edges']);
    }

    /**
     * Set billing address only with separate shipping address.
     */
    public function test_set_checkout_address_billing_only_with_separate_shipping(): void
    {
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];
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
                $useForShipping: Boolean
                $shippingFirstName: String
                $shippingLastName: String
                $shippingEmail: String
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
                  useForShipping: $useForShipping
                  shippingFirstName: $shippingFirstName
                  shippingLastName: $shippingLastName
                  shippingEmail: $shippingEmail
                  shippingAddress: $shippingAddress
                  shippingCity: $shippingCity
                  shippingCountry: $shippingCountry
                  shippingState: $shippingState
                  shippingPostcode: $shippingPostcode
                  shippingPhoneNumber: $shippingPhoneNumber
                }
              ) {
                checkoutAddress {
                  _id
                  success
                  message
                  shippingFirstName
                  shippingLastName
                  shippingCity
                }
              }
            }
        GQL;

        $variables = [
            'billingFirstName' => 'Jane',
            'billingLastName' => 'Smith',
            'billingEmail' => 'jane@example.com',
            'billingAddress' => '456 Other Ave',
            'billingCity' => 'Mumbai',
            'billingCountry' => 'IN',
            'billingState' => 'MH',
            'billingPostcode' => '400001',
            'billingPhoneNumber' => '9876543210',
            'useForShipping' => false,
            'shippingFirstName' => 'Jane',
            'shippingLastName' => 'Smith',
            'shippingEmail' => 'jane@example.com',
            'shippingAddress' => '789 Ship Rd',
            'shippingCity' => 'Delhi',
            'shippingCountry' => 'IN',
            'shippingState' => 'DL',
            'shippingPostcode' => '110001',
            'shippingPhoneNumber' => '9876543211',
        ];

        $response = $this->graphQL($mutation, $variables, $this->customerHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL errors: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createCheckoutAddress.checkoutAddress');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('success', $data);
    }

    /**
     * Set checkout address without token returns error.
     */
    public function test_set_checkout_address_without_token_returns_error(): void
    {
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
                }
              ) {
                checkoutAddress { _id success message }
              }
            }
        GQL;

        $variables = [
            'billingFirstName' => 'John',
            'billingLastName' => 'Doe',
            'billingEmail' => 'john@example.com',
            'billingAddress' => '123 Main St',
            'billingCity' => 'LA',
            'billingCountry' => 'IN',
            'billingState' => 'UP',
            'billingPostcode' => '201301',
            'billingPhoneNumber' => '2125551234',
        ];

        // No Authorization header
        $response = $this->graphQL($mutation, $variables);

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Set an invalid shipping method returns error.
     */
    public function test_set_invalid_shipping_method_returns_error(): void
    {
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);

        $mutation = <<<'GQL'
            mutation createCheckoutShippingMethod($shippingMethod: String!) {
              createCheckoutShippingMethod(input: {shippingMethod: $shippingMethod}) {
                checkoutShippingMethod { success message }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'shippingMethod' => 'nonexistent_method',
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $errors = $response->json('errors');
        $data = $response->json('data.createCheckoutShippingMethod.checkoutShippingMethod');

        $hasError = ! empty($errors) || ($data !== null && ! ($data['success'] ?? true));
        $this->assertTrue($hasError, 'Expected an error for invalid shipping method');
    }

    /**
     * Set shipping method without token returns error.
     */
    public function test_set_shipping_method_without_token_returns_error(): void
    {
        $mutation = <<<'GQL'
            mutation createCheckoutShippingMethod($shippingMethod: String!) {
              createCheckoutShippingMethod(input: {shippingMethod: $shippingMethod}) {
                checkoutShippingMethod { success message }
              }
            }
        GQL;

        // No Authorization header
        $response = $this->graphQL($mutation, ['shippingMethod' => 'flatrate_flatrate']);

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Set an invalid payment method returns error.
     */
    public function test_set_invalid_payment_method_returns_error(): void
    {
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);
        $this->setShippingMethod($token);

        $mutation = <<<'GQL'
            mutation createCheckoutPaymentMethod($paymentMethod: String!) {
              createCheckoutPaymentMethod(input: {paymentMethod: $paymentMethod}) {
                checkoutPaymentMethod { success message }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'paymentMethod' => 'nonexistent_payment_method',
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $errors = $response->json('errors');
        $data = $response->json('data.createCheckoutPaymentMethod.checkoutPaymentMethod');

        $hasError = ! empty($errors) || ($data !== null && ! ($data['success'] ?? true));
        $this->assertTrue($hasError, 'Expected an error for invalid payment method');
    }

    /**
     * Set payment method without token returns error.
     */
    public function test_set_payment_method_without_token_returns_error(): void
    {
        $mutation = <<<'GQL'
            mutation createCheckoutPaymentMethod($paymentMethod: String!) {
              createCheckoutPaymentMethod(input: {paymentMethod: $paymentMethod}) {
                checkoutPaymentMethod { success message }
              }
            }
        GQL;

        // No Authorization header
        $response = $this->graphQL($mutation, ['paymentMethod' => 'moneytransfer']);

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Place order without token returns error.
     */
    public function test_place_order_without_token_returns_error(): void
    {
        $mutation = <<<'GQL'
            mutation createCheckoutOrder {
              createCheckoutOrder(input:{}) {
                checkoutOrder { id orderId }
              }
            }
        GQL;

        // No Authorization header
        $response = $this->graphQL($mutation);

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Place order with empty cart returns error.
     */
    public function test_place_order_with_empty_cart_returns_error(): void
    {
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];

        // Do NOT add any product to cart
        $mutation = <<<'GQL'
            mutation createCheckoutOrder {
              createCheckoutOrder(input:{}) {
                checkoutOrder { id orderId }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [], $this->customerHeaders($token));

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Place order without billing address returns error.
     */
    public function test_place_order_without_billing_address_returns_error(): void
    {
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];
        $this->addProductToCart($token);
        // Deliberately skip setCheckoutAddress

        $mutation = <<<'GQL'
            mutation createCheckoutOrder {
              createCheckoutOrder(input:{}) {
                checkoutOrder { id orderId }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [], $this->customerHeaders($token));

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Place order without payment method returns error.
     */
    public function test_place_order_without_payment_method_returns_error(): void
    {
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];
        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);
        $this->setShippingMethod($token);
        // Deliberately skip setPaymentMethod

        $mutation = <<<'GQL'
            mutation createCheckoutOrder {
              createCheckoutOrder(input:{}) {
                checkoutOrder { id orderId }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [], $this->customerHeaders($token));

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Helper to set checkout address
     */
    private function enableStripe(): void
    {
        foreach ([
            'active' => '1',
            'sandbox' => '1',
            'api_test_key' => 'sk_test_x',
            'api_test_publishable_key' => 'pk_test_x',
        ] as $field => $value) {
            $code = "sales.payment_methods.stripe.{$field}";

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

    public function test_payment_method_resolves_a_gateway_url_for_a_redirect_gateway(): void
    {
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];

        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);
        $this->setShippingMethod($token);
        $this->enableStripe();

        $query = <<<'GQL'
            mutation createCheckoutPaymentMethod($paymentMethod: String!) {
              createCheckoutPaymentMethod(input: {paymentMethod: $paymentMethod}) {
                checkoutPaymentMethod {
                  success
                  paymentGatewayUrl
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['paymentMethod' => 'stripe'], $this->customerHeaders($token));

        $response->assertSuccessful();

        $this->assertNull($response->json('errors'));

        $data = $response->json('data.createCheckoutPaymentMethod.checkoutPaymentMethod');

        $this->assertTrue($data['success']);
        $this->assertStringContainsString('/stripe/redirect', (string) $data['paymentGatewayUrl']);
    }

    public function test_place_order_returns_a_redirect_and_creates_no_order_for_a_redirect_gateway(): void
    {
        $customerData = $this->createTestCustomer();
        $token = $customerData['token'];

        $this->addProductToCart($token);
        $this->setCheckoutAddress($token);
        $this->setShippingMethod($token);
        $this->enableStripe();
        $this->setPaymentMethod($token, 'stripe');

        $ordersBefore = Order::count();

        $query = <<<'GQL'
            mutation createCheckoutOrder {
              createCheckoutOrder(input:{}) {
                checkoutOrder {
                  orderId
                  redirect
                  redirectUrl
                  success
                  message
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [], $this->customerHeaders($token));

        $response->assertSuccessful();

        $this->assertNull($response->json('errors'));

        $data = $response->json('data.createCheckoutOrder.checkoutOrder');

        $this->assertTrue($data['redirect']);
        $this->assertStringContainsString('/stripe/redirect', (string) $data['redirectUrl']);
        $this->assertNull($data['orderId']);

        // The redirect path must still be a populated, unambiguous result — not all-null.
        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);

        $this->assertSame($ordersBefore, Order::count());
    }

    private function setCheckoutAddress(string $token): void
    {
        $headers = $this->customerHeaders($token);

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

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();
    }

    /**
     * Helper to set shipping method
     */
    private function setShippingMethod(string $token): void
    {
        $headers = $this->customerHeaders($token);

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
                }
              }
            }
        GQL;

        $variables = [
            'shippingMethod' => 'flatrate_flatrate',
        ];

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();
    }

    /**
     * Helper to set payment method
     */
    private function setPaymentMethod(string $token, string $method = 'moneytransfer'): void
    {
        $headers = $this->customerHeaders($token);

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
                }
              }
            }
        GQL;

        $variables = [
            'paymentMethod' => $method,
            'successUrl' => 'https://myapp.com/payment/success',
            'failureUrl' => 'https://myapp.com/payment/failure',
            'cancelUrl' => 'https://myapp.com/payment/cancel',
        ];

        $response = $this->graphQL($query, $variables, $headers);

        $response->assertSuccessful();
    }

    /**
     * The address step applies the storefront's address rules, so an invalid phone number is
     * refused and nothing is written to the cart.
     */
    public function test_set_checkout_address_with_invalid_phone_returns_error(): void
    {
        $token = $this->createTestCustomer()['token'];

        $this->addProductToCart($token);

        $mutation = <<<'GQL'
            mutation {
              createCheckoutAddress(
                input: {
                  billingFirstName: "John"
                  billingLastName: "Doe"
                  billingEmail: "john@example.com"
                  billingAddress: "123 Main St"
                  billingCity: "Los Angeles"
                  billingCountry: "IN"
                  billingState: "UP"
                  billingPostcode: "201301"
                  billingPhoneNumber: "not-a-phone"
                  useForShipping: true
                }
              ) {
                checkoutAddress { _id success }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [], $this->customerHeaders($token));

        $response->assertSuccessful();

        $this->assertNotEmpty($response->json('errors'));
        $this->assertStringContainsString('billingPhoneNumber', (string) $response->json('errors.0.message'));
    }

    /**
     * The address mutation saves the billing address on the customer's cart.
     */
    public function test_mutation_checkout_address_saves_the_billing_address(): void
    {
        $customerData = $this->createTestCustomer();
        $this->addProductToCart($customerData['token']);

        $response = $this->graphQL($this->mutationCheckoutAddressMutation(), [
            'input' => $this->mutationCheckoutAddressInput(),
        ], $this->customerHeaders($customerData['token']));

        $response->assertSuccessful();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createMutationCheckoutAddress.mutationCheckoutAddress.id'))->not->toBeNull();

        $this->assertDatabaseHas('addresses', [
            'cart_id' => $this->activeCartIdOf($customerData['customer']->id),
            'address_type' => 'cart_billing',
            'first_name' => 'Mutation',
            'city' => 'Los Angeles',
        ]);
    }

    /**
     * The address mutation without a cart token is refused.
     */
    public function test_mutation_checkout_address_requires_a_cart_token(): void
    {
        $response = $this->graphQL($this->mutationCheckoutAddressMutation(), [
            'input' => $this->mutationCheckoutAddressInput(),
        ]);

        $response->assertSuccessful();
        expect($response->json('errors'))->not->toBeEmpty();
        $this->assertDatabaseMissing('addresses', [
            'first_name' => 'Mutation',
            'address_type' => 'cart_billing',
        ]);
    }

    /**
     * The address mutation rejects a billing address without a phone number.
     */
    public function test_mutation_checkout_address_rejects_a_missing_phone_number(): void
    {
        $customerData = $this->createTestCustomer();
        $this->addProductToCart($customerData['token']);

        $response = $this->graphQL($this->mutationCheckoutAddressMutation(), [
            'input' => $this->mutationCheckoutAddressInput(['billingPhoneNumber' => null]),
        ], $this->customerHeaders($customerData['token']));

        expect($response->json('errors'))->not->toBeEmpty();
        expect((string) $response->json('errors.0.message'))->toContain('billingPhoneNumber');
        $this->assertDatabaseMissing('addresses', [
            'first_name' => 'Mutation',
            'address_type' => 'cart_billing',
        ]);
    }

    /**
     * Reading the checkout address returns the billing address saved on the customer's cart.
     */
    public function test_read_checkout_address_returns_the_saved_billing_address(): void
    {
        $customerData = $this->createTestCustomer();
        $this->addProductToCart($customerData['token']);
        $this->setCheckoutAddress($customerData['token']);

        $response = $this->graphQL($this->readCheckoutAddressQuery(), [
            'id' => '/api/shop/checkout-addresses/'.$this->activeCartIdOf($customerData['customer']->id),
        ], $this->customerHeaders($customerData['token']));

        $response->assertSuccessful();
        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.readCheckoutAddress');

        expect((int) $data['customerId'])->toBe($customerData['customer']->id);
        expect($data['billingFirstName'])->toBe('John');
        expect($data['billingCity'])->toBe('Los Angeles');
    }

    /**
     * Another customer reading the checkout address sees only their own cart, never this one's address.
     */
    public function test_read_checkout_address_is_scoped_to_the_token_cart(): void
    {
        $owner = $this->createTestCustomer();
        $this->addProductToCart($owner['token']);
        $this->setCheckoutAddress($owner['token']);

        $other = $this->createTestCustomer();
        $this->addProductToCart($other['token']);

        $response = $this->graphQL($this->readCheckoutAddressQuery(), [
            'id' => '/api/shop/checkout-addresses/'.$this->activeCartIdOf($owner['customer']->id),
        ], $this->customerHeaders($other['token']));

        $response->assertSuccessful();
        expect($response->json('errors'))->toBeNull();
        expect((int) $response->json('data.readCheckoutAddress.customerId'))->toBe($other['customer']->id);
        expect($response->json('data.readCheckoutAddress.billingFirstName'))->toBeNull();
    }

    /**
     * Reading the checkout address without a cart token is refused.
     */
    public function test_read_checkout_address_requires_a_cart_token(): void
    {
        $response = $this->graphQL($this->readCheckoutAddressQuery(), ['id' => '/api/shop/checkout-addresses/1']);

        $response->assertSuccessful();
        expect($response->json('errors'))->not->toBeEmpty();
        expect($response->json('data.readCheckoutAddress'))->toBeNull();
    }

    /**
     * Get the id of the customer's active cart.
     */
    private function activeCartIdOf(int $customerId): int
    {
        return (int) DB::table('cart')
            ->where('customer_id', $customerId)
            ->where('is_active', 1)
            ->value('id');
    }

    /**
     * The address mutation that returns only the cart address id.
     */
    private function mutationCheckoutAddressMutation(): string
    {
        return <<<'GQL'
            mutation createMutationCheckoutAddress($input: createMutationCheckoutAddressInput!) {
              createMutationCheckoutAddress(input: $input) {
                mutationCheckoutAddress {
                  id
                }
              }
            }
        GQL;
    }

    /**
     * A complete billing address input for the address mutation.
     */
    private function mutationCheckoutAddressInput(array $overrides = []): array
    {
        return array_merge([
            'billingFirstName' => 'Mutation',
            'billingLastName' => 'Tester',
            'billingEmail' => 'mutation@example.com',
            'billingAddress' => '1 Mutation Way',
            'billingCity' => 'Los Angeles',
            'billingCountry' => 'IN',
            'billingState' => 'UP',
            'billingPostcode' => '201301',
            'billingPhoneNumber' => '2125551234',
            'useForShipping' => true,
        ], $overrides);
    }

    /**
     * The query that reads the checkout address of the token's cart.
     */
    private function readCheckoutAddressQuery(): string
    {
        return <<<'GQL'
            query readCheckoutAddress($id: ID!) {
              readCheckoutAddress(id: $id) {
                customerId
                billingFirstName
                billingCity
              }
            }
        GQL;
    }
}
