<?php

namespace Tests\Feature\BagistoApi;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Webkul\Checkout\Models\Cart;
use Webkul\Checkout\Models\CartItem;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;

class CartRemoveTest extends TestCase
{
    use RefreshDatabase;

    private string $graphqlUrl = '/graphql';

    /**
     * Create customer with cart and items
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
            'quantity'   => 2,
        ]);

        return compact('customer', 'token', 'cart', 'product', 'cartItem');
    }

    /**
     * Remove cart item mutation
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
                message
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
            'variables' => compact('token', 'cartItemId'),
        ])->json();
    }

    /**
     * Test: Remove item from cart
     */
    public function test_remove_item_from_cart(): void
    {
        ['token' => $token, 'cartItem' => $cartItem, 'cart' => $cart] = $this->createCustomerWithCart();

        $response = $this->removeCartItem($token, $cartItem->id);

        $this->assertTrue($response['data']['removeItemCartToken']['success']);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    /**
     * Test: Items count decreases after removal
     */
    public function test_items_count_decreases_after_removal(): void
    {
        ['token' => $token, 'cartItem' => $cartItem, 'cart' => $cart] = $this->createCustomerWithCart();

        // Add second item
        $product2 = Product::factory()->create();
        $cartItem2 = CartItem::factory()->create([
            'cart_id'    => $cart->id,
            'product_id' => $product2->id,
            'quantity'   => 1,
        ]);

        $response = $this->removeCartItem($token, $cartItem->id);

        // Items count should be reduced
        $this->assertTrue($response['data']['removeItemCartToken']['success']);
        $this->assertCount(1, $response['data']['removeItemCartToken']['cart']['items']);
    }

    /**
     * Test: Fails to remove with invalid token
     */
    public function test_fails_to_remove_with_invalid_token(): void
    {
        ['cartItem' => $cartItem] = $this->createCustomerWithCart();

        $response = $this->removeCartItem('invalid-token', $cartItem->id);

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Fails to remove non-existent item
     */
    public function test_fails_to_remove_non_existent_item(): void
    {
        ['token' => $token] = $this->createCustomerWithCart();

        $response = $this->removeCartItem($token, 99999);

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Fails to remove other customer's item
     */
    public function test_fails_to_remove_other_customer_item(): void
    {
        ['cartItem' => $cartItem] = $this->createCustomerWithCart();

        $otherCustomer = Customer::factory()->create();
        $otherToken = $otherCustomer->createToken('api-token')->plainTextToken;

        $response = $this->removeCartItem($otherToken, $cartItem->id);

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Remove preserves other items
     */
    public function test_remove_preserves_other_items(): void
    {
        ['token' => $token, 'cartItem' => $cartItem, 'cart' => $cart] = $this->createCustomerWithCart();

        // Add second item
        $product2 = Product::factory()->create();
        $cartItem2 = CartItem::factory()->create([
            'cart_id'    => $cart->id,
            'product_id' => $product2->id,
            'quantity'   => 3,
        ]);

        $response = $this->removeCartItem($token, $cartItem->id);

        // Other item should still exist
        $remainingItems = $response['data']['removeItemCartToken']['cart']['items'];
        $this->assertCount(1, $remainingItems);
        $this->assertEquals($product2->id, $remainingItems[0]['productId']);
    }

    /**
     * Test: Cart totals update after removal
     */
    public function test_cart_totals_update_after_removal(): void
    {
        ['token' => $token, 'cartItem' => $cartItem] = $this->createCustomerWithCart();

        $response = $this->removeCartItem($token, $cartItem->id);

        $this->assertArrayHasKey('formattedGrandTotal', $response['data']['removeItemCartToken']['cart']);
    }

    /**
     * Test: Removing all items shows empty cart
     */
    public function test_removing_all_items_shows_empty_cart(): void
    {
        ['token' => $token, 'cartItem' => $cartItem] = $this->createCustomerWithCart();

        $response = $this->removeCartItem($token, $cartItem->id);

        $this->assertTrue($response['data']['removeItemCartToken']['success']);
        $this->assertCount(0, $response['data']['removeItemCartToken']['cart']['items']);
    }
}
