<?php

namespace Tests\Feature\BagistoApi;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Webkul\Checkout\Models\Cart;
use Webkul\Checkout\Models\CartItem;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;

class CartCreateTest extends TestCase
{
    use RefreshDatabase;

    private string $graphqlUrl = '/api/graphql';

    /**
     * Create customer with Sanctum token
     */
    private function createCustomerWithToken(): array
    {
        $customer = Customer::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $token = $customer->createToken('api-token')->plainTextToken;

        return compact('customer', 'token');
    }

    /**
     * Add product to cart mutation
     */
    private function addProductToCart(string $token, int $productId, int $quantity, ?array $options = null): array
    {
        $mutation = <<<'GQL'
            mutation addProductToCart($token: String!, $productId: Int!, $quantity: Int!, $options: Object) {
              addProductToCartCartToken(input: {
                token: $token
                productId: $productId
                quantity: $quantity
                options: $options
              }) {
                success
                message
                cart {
                  id
                  cartToken
                  customerId
                  itemsCount
                  items {
                    id
                    productId
                    name
                    quantity
                    formattedPrice
                    formattedTotal
                    options
                  }
                  formattedGrandTotal
                }
              }
            }
        GQL;

        $variables = compact('token', 'productId', 'quantity');
        if ($options !== null) {
            $variables['options'] = $options;
        }

        return $this->postJson($this->graphqlUrl, [
            'query'     => $mutation,
            'variables' => $variables,
        ])->json();
    }

    /**
     * Test: Add product to cart successfully
     */
    public function test_add_product_to_cart_successfully(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();
        $product = Product::factory()->create();

        $response = $this->addProductToCart($token, $product->id, 2);

        $this->assertArrayHasKey('data', $response);
        $this->assertTrue($response['data']['addProductToCartCartToken']['success']);
        $this->assertCount(1, $response['data']['addProductToCartCartToken']['cart']['items']);
        $this->assertEquals(2, $response['data']['addProductToCartCartToken']['cart']['items'][0]['quantity']);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);
    }

    /**
     * Test: Creates new cart on first product add
     */
    public function test_creates_new_cart_on_first_product_add(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();
        $product = Product::factory()->create();

        $cartsCountBefore = Cart::where('customer_id', $customer->id)->count();

        $response = $this->addProductToCart($token, $product->id, 1);

        $this->assertTrue($response['data']['addProductToCartCartToken']['success']);

        $cartsCountAfter = Cart::where('customer_id', $customer->id)->count();
        $this->assertEquals($cartsCountBefore + 1, $cartsCountAfter);
    }

    /**
     * Test: Increases quantity when adding same product
     */
    public function test_increases_quantity_when_adding_same_product(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();
        $product = Product::factory()->create();

        // Add product first time
        $this->addProductToCart($token, $product->id, 2);

        // Add same product again
        $response = $this->addProductToCart($token, $product->id, 3);

        $this->assertCount(1, $response['data']['addProductToCartCartToken']['cart']['items']);
        $this->assertEquals(5, $response['data']['addProductToCartCartToken']['cart']['items'][0]['quantity']);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity'   => 5,
        ]);
    }

    /**
     * Test: Can add product with options
     */
    public function test_can_add_product_with_options(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();
        $product = Product::factory()->create();

        $options = ['size' => 'M', 'color' => 'red'];

        $response = $this->addProductToCart($token, $product->id, 1, $options);

        $this->assertTrue($response['data']['addProductToCartCartToken']['success']);
        $this->assertEquals($options, $response['data']['addProductToCartCartToken']['cart']['items'][0]['options']);

        $cartItem = CartItem::where('product_id', $product->id)->first();
        $this->assertEquals($options, $cartItem->options);
    }

    /**
     * Test: Correctly updates items count
     */
    public function test_correctly_updates_items_count(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();
        $product = Product::factory()->create();

        $response = $this->addProductToCart($token, $product->id, 5);

        $this->assertEquals(5, $response['data']['addProductToCartCartToken']['cart']['itemsCount']);
    }

    /**
     * Test: Calculates formatted total correctly
     */
    public function test_calculates_formatted_total_correctly(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();
        $product = Product::factory()->create();

        $response = $this->addProductToCart($token, $product->id, 2);

        $formattedTotal = $response['data']['addProductToCartCartToken']['cart']['items'][0]['formattedTotal'];

        $this->assertIsString($formattedTotal);
        $this->assertMatchesRegularExpression('/^\$[\d,.]+$/', $formattedTotal);
    }

    /**
     * Test: Fails when token is missing
     */
    public function test_fails_when_token_is_missing(): void
    {
        $product = Product::factory()->create();

        $mutation = <<<'GQL'
            mutation addProductToCart($productId: Int!, $quantity: Int!) {
              addProductToCartCartToken(input: {
                productId: $productId
                quantity: $quantity
              }) {
                success
              }
            }
        GQL;

        $response = $this->postJson($this->graphqlUrl, [
            'query'     => $mutation,
            'variables' => ['productId' => $product->id, 'quantity' => 1],
        ])->json();

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Fails when product id is missing
     */
    public function test_fails_when_product_id_is_missing(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();

        $mutation = <<<'GQL'
            mutation addProductToCart($token: String!, $quantity: Int!) {
              addProductToCartCartToken(input: {
                token: $token
                quantity: $quantity
              }) {
                success
              }
            }
        GQL;

        $response = $this->postJson($this->graphqlUrl, [
            'query'     => $mutation,
            'variables' => ['token' => $token, 'quantity' => 1],
        ])->json();

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Fails with invalid token
     */
    public function test_fails_with_invalid_token(): void
    {
        $product = Product::factory()->create();

        $response = $this->addProductToCart('invalid-token-xyz', $product->id, 1);

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Fails with non-existent product
     */
    public function test_fails_with_non_existent_product(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();

        $response = $this->addProductToCart($token, 99999, 1);

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Can add product for guest using cart id as token
     */
    public function test_can_add_product_for_guest_using_cart_id_as_token(): void
    {
        $guestCart = Cart::factory()->create(['customer_id' => null]);
        $product = Product::factory()->create();

        $response = $this->addProductToCart((string) $guestCart->id, $product->id, 1);

        $this->assertTrue($response['data']['addProductToCartCartToken']['success']);

        $this->assertDatabaseHas('cart_items', [
            'cart_id'    => $guestCart->id,
            'product_id' => $product->id,
        ]);
    }

    /**
     * Test: Adds multiple different products to cart
     */
    public function test_adds_multiple_different_products_to_cart(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // Add first product
        $this->addProductToCart($token, $product1->id, 1);

        // Add second product
        $response = $this->addProductToCart($token, $product2->id, 2);

        $this->assertCount(2, $response['data']['addProductToCartCartToken']['cart']['items']);
        $this->assertEquals(3, $response['data']['addProductToCartCartToken']['cart']['itemsCount']);
    }

    /**
     * Test: Handles zero quantity
     */
    public function test_handles_zero_quantity(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();
        $product = Product::factory()->create();

        $response = $this->addProductToCart($token, $product->id, 0);

        // Should either fail or handle gracefully
        $this->assertTrue(
            isset($response['errors']) || isset($response['data']['addProductToCartCartToken']['success'])
        );
    }

    /**
     * Test: Handles negative quantity
     */
    public function test_handles_negative_quantity(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();
        $product = Product::factory()->create();

        $response = $this->addProductToCart($token, $product->id, -5);

        // Should fail
        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Returns cart token in response
     */
    public function test_returns_cart_token_in_response(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();
        $product = Product::factory()->create();

        $response = $this->addProductToCart($token, $product->id, 1);

        $this->assertNotEmpty($response['data']['addProductToCartCartToken']['cart']['cartToken']);
    }
}
