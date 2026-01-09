<?php

namespace Tests\Feature\BagistoApi;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Webkul\Checkout\Models\Cart;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;

class CartIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private string $graphqlUrl = '/graphql';

    /**
     * Helper to create customer with Sanctum token
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
     * Add product to cart
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
                cart {
                  id
                  itemsCount
                  formattedGrandTotal
                  items {
                    id
                    productId
                    quantity
                  }
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
     * Update cart item
     */
    private function updateCartItem(string $token, int $cartItemId, int $quantity): array
    {
        $mutation = <<<'GQL'
            mutation updateCartItem($token: String!, $cartItemId: Int!, $quantity: Int!) {
              updateItemCartToken(input: {
                token: $token
                cartItemId: $cartItemId
                quantity: $quantity
              }) {
                success
                cart {
                  itemsCount
                  items {
                    id
                    quantity
                  }
                }
              }
            }
        GQL;

        return $this->postJson($this->graphqlUrl, [
            'query'     => $mutation,
            'variables' => compact('token', 'cartItemId', 'quantity'),
        ])->json();
    }

    /**
     * Remove cart item
     */
    private function removeCartItem(string $token, int $cartItemId): array
    {
        $mutation = <<<'GQL'
            mutation removeCartItem($token: String!, $cartItemId: Int!) {
              removeItemCartToken(input: {
                token: $token
                cartItemId: $cartItemId
              }) {
                success
                cart {
                  itemsCount
                  items {
                    id
                  }
                }
              }
            }
        GQL;

        return $this->postJson($this->graphqlUrl, [
            'query'     => $mutation,
            'variables' => compact('token', 'cartItemId'),
        ])->json();
    }

    /**
     * Get cart details
     */
    private function getCart(string $token, int $cartId): array
    {
        $mutation = <<<'GQL'
            query getCart($token: String!, $cartId: Int!) {
              readCartToken(input: {
                token: $token
                cartId: $cartId
              }) {
                cart {
                  id
                  itemsCount
                  items {
                    id
                    productId
                    quantity
                  }
                  formattedGrandTotal
                }
              }
            }
        GQL;

        return $this->postJson($this->graphqlUrl, [
            'query'     => $mutation,
            'variables' => compact('token', 'cartId'),
        ])->json();
    }

    /**
     * Test: Complete shopping flow - add, update, remove
     */
    public function test_complete_shopping_flow_add_update_remove(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();

        $product = Product::factory()->create();

        // Step 1: Add product to cart
        $addResponse = $this->addProductToCart($token, $product->id, 2);
        $this->assertTrue($addResponse['data']['addProductToCartCartToken']['success']);

        $cartId = $addResponse['data']['addProductToCartCartToken']['cart']['id'];
        $cartItemId = $addResponse['data']['addProductToCartCartToken']['cart']['items'][0]['id'];

        $this->assertEquals(2, $addResponse['data']['addProductToCartCartToken']['cart']['itemsCount']);

        // Step 2: Update quantity
        $updateResponse = $this->updateCartItem($token, $cartItemId, 5);
        $this->assertTrue($updateResponse['data']['updateItemCartToken']['success']);
        $this->assertEquals(5, $updateResponse['data']['updateItemCartToken']['cart']['items'][0]['quantity']);

        // Step 3: Remove item
        $removeResponse = $this->removeCartItem($token, $cartItemId);
        $this->assertTrue($removeResponse['data']['removeItemCartToken']['success']);
        $this->assertEquals(0, $removeResponse['data']['removeItemCartToken']['cart']['itemsCount']);
    }

    /**
     * Test: Multiple products in cart
     */
    public function test_multiple_products_in_cart(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $product3 = Product::factory()->create();

        // Add first product
        $response1 = $this->addProductToCart($token, $product1->id, 1);
        $cartId = $response1['data']['addProductToCartCartToken']['cart']['id'];
        $this->assertEquals(1, $response1['data']['addProductToCartCartToken']['cart']['itemsCount']);

        // Add second product
        $response2 = $this->addProductToCart($token, $product2->id, 2);
        $this->assertEquals(3, $response2['data']['addProductToCartCartToken']['cart']['itemsCount']);

        // Add third product
        $response3 = $this->addProductToCart($token, $product3->id, 3);
        $this->assertEquals(6, $response3['data']['addProductToCartCartToken']['cart']['itemsCount']);

        // Verify cart has 3 items
        $getResponse = $this->getCart($token, $cartId);
        $this->assertCount(3, $getResponse['data']['readCartToken']['cart']['items']);
    }

    /**
     * Test: Guest to authenticated checkout flow
     */
    public function test_guest_to_authenticated_checkout_flow(): void
    {
        // Step 1: Guest adds product
        $guestCart = Cart::factory()->create(['customer_id' => null]);
        $product = Product::factory()->create();

        $guestResponse = $this->addProductToCart((string) $guestCart->id, $product->id, 2);
        $this->assertTrue($guestResponse['data']['addProductToCartCartToken']['success']);
        $this->assertEquals(2, $guestResponse['data']['addProductToCartCartToken']['cart']['itemsCount']);

        // Step 2: Guest registers/logs in
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();

        // Step 3: Merge guest cart
        $mergeResponse = $this->addProductToCart($token, $product->id, 1);
        $this->assertTrue($mergeResponse['data']['addProductToCartCartToken']['success']);

        // After merge, customer should have product
        $this->assertGreaterThan(0, $mergeResponse['data']['addProductToCartCartToken']['cart']['itemsCount']);
    }

    /**
     * Test: Update same product multiple times
     */
    public function test_update_same_product_multiple_times(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();

        $product = Product::factory()->create();

        // Add product
        $response = $this->addProductToCart($token, $product->id, 1);
        $cartItemId = $response['data']['addProductToCartCartToken']['cart']['items'][0]['id'];

        // Update to 5
        $this->updateCartItem($token, $cartItemId, 5);

        // Update to 10
        $this->updateCartItem($token, $cartItemId, 10);

        // Update to 3
        $response = $this->updateCartItem($token, $cartItemId, 3);

        $this->assertEquals(3, $response['data']['updateItemCartToken']['cart']['items'][0]['quantity']);
    }

    /**
     * Test: Add product with options
     */
    public function test_add_product_with_options_and_update(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();

        $product = Product::factory()->create();

        $options = ['size' => 'M', 'color' => 'red'];
        $response = $this->addProductToCart($token, $product->id, 1, $options);

        $this->assertTrue($response['data']['addProductToCartCartToken']['success']);
        $this->assertEquals($options, $response['data']['addProductToCartCartToken']['cart']['items'][0]);
    }

    /**
     * Test: Empty cart after removing all items
     */
    public function test_empty_cart_after_removing_all_items(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();

        $products = Product::factory()->count(3)->create();

        // Add 3 products
        foreach ($products as $index => $product) {
            $this->addProductToCart($token, $product->id, $index + 1);
        }

        // Get cart to find items
        $getResponse = $this->addProductToCart($token, $products[0]->id, 0); // Dummy to get cart ID
        $cartId = $getResponse['data']['addProductToCartCartToken']['cart']['id'];

        $cartDetails = $this->getCart($token, $cartId);
        $items = $cartDetails['data']['readCartToken']['cart']['items'];

        // Remove all items
        foreach ($items as $item) {
            $this->removeCartItem($token, $item['id']);
        }

        // Verify cart is empty
        $finalCart = $this->getCart($token, $cartId);
        $this->assertCount(0, $finalCart['data']['readCartToken']['cart']['items']);
    }

    /**
     * Test: Cart persistence across operations
     */
    public function test_cart_persistence_across_operations(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->createCustomerWithToken();

        $product = Product::factory()->create();

        // Add product
        $addResponse = $this->addProductToCart($token, $product->id, 1);
        $cartId = $addResponse['data']['addProductToCartCartToken']['cart']['id'];

        // Fetch cart
        $getResponse1 = $this->getCart($token, $cartId);
        $this->assertEquals($cartId, $getResponse1['data']['readCartToken']['cart']['id']);

        // Update item
        $cartItemId = $getResponse1['data']['readCartToken']['cart']['items'][0]['id'];
        $this->updateCartItem($token, $cartItemId, 5);

        // Fetch again
        $getResponse2 = $this->getCart($token, $cartId);
        $this->assertEquals($cartId, $getResponse2['data']['readCartToken']['cart']['id']);
        $this->assertEquals(5, $getResponse2['data']['readCartToken']['cart']['items'][0]['quantity']);
    }
}
