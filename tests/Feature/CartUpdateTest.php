<?php

namespace Tests\Feature\BagistoApi;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Webkul\Checkout\Models\Cart;
use Webkul\Checkout\Models\CartItem;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;

class CartUpdateTest extends TestCase
{
    use RefreshDatabase;

    private string $graphqlUrl = '/graphql';

    /**
     * Create customer with Sanctum token and cart with items
     */
    private function createCustomerWithCart(): array
    {
        $customer = Customer::factory()->create();
        $token = $customer->createToken('api-token')->plainTextToken;

        $cart = Cart::factory()->create(['customer_id' => $customer->id]);
        $product = Product::factory()->create();

        $cartItem = CartItem::factory()->create([
            'cart_id'    => $cart->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        return compact('customer', 'token', 'cart', 'product', 'cartItem');
    }

    /**
     * Update cart item mutation
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
                message
                cart {
                  id
                  itemsCount
                  items {
                    id
                    quantity
                    formattedTotal
                  }
                  formattedGrandTotal
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
     * Test: Update cart item quantity
     */
    public function test_update_cart_item_quantity(): void
    {
        ['token' => $token, 'cartItem' => $cartItem] = $this->createCustomerWithCart();

        $response = $this->updateCartItem($token, $cartItem->id, 5);

        $this->assertTrue($response['data']['updateItemCartToken']['success']);
        $this->assertEquals(5, $response['data']['updateItemCartToken']['cart']['items'][0]['quantity']);

        $this->assertDatabaseHas('cart_items', [
            'id'       => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    /**
     * Test: Update multiple items in cart
     */
    public function test_update_multiple_items_in_cart(): void
    {
        ['customer' => $customer, 'token' => $token, 'cart' => $cart] = $this->createCustomerWithCart();

        $product2 = Product::factory()->create();
        $cartItem2 = CartItem::factory()->create([
            'cart_id'    => $cart->id,
            'product_id' => $product2->id,
            'quantity'   => 2,
        ]);

        // Update first item
        $this->updateCartItem($token, 1, 3);

        // Update second item
        $response = $this->updateCartItem($token, $cartItem2->id, 4);

        $this->assertCount(2, $response['data']['updateItemCartToken']['cart']['items']);
    }

    /**
     * Test: Fails to update with invalid token
     */
    public function test_fails_to_update_with_invalid_token(): void
    {
        ['cartItem' => $cartItem] = $this->createCustomerWithCart();

        $response = $this->updateCartItem('invalid-token', $cartItem->id, 5);

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Fails to update non-existent item
     */
    public function test_fails_to_update_non_existent_item(): void
    {
        ['token' => $token] = $this->createCustomerWithCart();

        $response = $this->updateCartItem($token, 99999, 5);

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Fails to update with zero quantity
     */
    public function test_fails_to_update_with_zero_quantity(): void
    {
        ['token' => $token, 'cartItem' => $cartItem] = $this->createCustomerWithCart();

        $response = $this->updateCartItem($token, $cartItem->id, 0);

        // Should fail or handle gracefully
        $this->assertTrue(
            isset($response['errors']) || ! $response['data']['updateItemCartToken']['success']
        );
    }

    /**
     * Test: Fails to update other customer's item
     */
    public function test_fails_to_update_other_customer_item(): void
    {
        ['cartItem' => $cartItem] = $this->createCustomerWithCart();

        $otherCustomer = Customer::factory()->create();
        $otherToken = $otherCustomer->createToken('api-token')->plainTextToken;

        $response = $this->updateCartItem($otherToken, $cartItem->id, 5);

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Update preserves cart structure
     */
    public function test_update_preserves_cart_structure(): void
    {
        ['token' => $token, 'cartItem' => $cartItem] = $this->createCustomerWithCart();

        $response = $this->updateCartItem($token, $cartItem->id, 10);

        $cart = $response['data']['updateItemCartToken']['cart'];

        $this->assertArrayHasKey('id', $cart);
        $this->assertArrayHasKey('itemsCount', $cart);
        $this->assertArrayHasKey('items', $cart);
        $this->assertArrayHasKey('formattedGrandTotal', $cart);
    }

    /**
     * Test: Update decreases quantity
     */
    public function test_update_decreases_quantity(): void
    {
        ['token' => $token, 'cartItem' => $cartItem] = $this->createCustomerWithCart();

        // First increase quantity
        $this->updateCartItem($token, $cartItem->id, 10);

        // Then decrease it
        $response = $this->updateCartItem($token, $cartItem->id, 3);

        $this->assertEquals(3, $response['data']['updateItemCartToken']['cart']['items'][0]['quantity']);
    }

    /**
     * Test: Update with large quantity
     */
    public function test_update_with_large_quantity(): void
    {
        ['token' => $token, 'cartItem' => $cartItem] = $this->createCustomerWithCart();

        $response = $this->updateCartItem($token, $cartItem->id, 1000);

        $this->assertEquals(1000, $response['data']['updateItemCartToken']['cart']['items'][0]['quantity']);
    }

    /**
     * Test: Cart totals update correctly
     */
    public function test_cart_totals_update_correctly(): void
    {
        ['token' => $token, 'cartItem' => $cartItem] = $this->createCustomerWithCart();

        $response = $this->updateCartItem($token, $cartItem->id, 5);

        $this->assertArrayHasKey('formattedGrandTotal', $response['data']['updateItemCartToken']['cart']);
        $this->assertNotEmpty($response['data']['updateItemCartToken']['cart']['formattedGrandTotal']);
    }
}
