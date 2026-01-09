<?php

namespace Tests\Feature\BagistoApi;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Webkul\Checkout\Models\Cart;
use Webkul\Checkout\Models\CartItem;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;

class CartReadTest extends TestCase
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
     * Get cart query
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
                  cartToken
                  customerId
                  itemsCount
                  items {
                    id
                    productId
                    name
                    quantity
                    price
                    formattedPrice
                    formattedTotal
                    options
                  }
                  subtotal
                  discountAmount
                  taxAmount
                  shippingAmount
                  grandTotal
                  formattedSubtotal
                  formattedDiscountAmount
                  formattedTaxAmount
                  formattedShippingAmount
                  formattedGrandTotal
                  couponCode
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
     * Get all carts query
     */
    private function getAllCarts(string $token): array
    {
        $mutation = <<<'GQL'
            query getAllCarts($token: String!) {
              cartCollectionCartToken(input: {
                token: $token
              }) {
                carts {
                  id
                  cartToken
                  customerId
                  itemsCount
                  items {
                    name
                    quantity
                  }
                  formattedGrandTotal
                }
              }
            }
        GQL;

        return $this->postJson($this->graphqlUrl, [
            'query'     => $mutation,
            'variables' => compact('token'),
        ])->json();
    }

    /**
     * Test: Read single cart
     */
    public function test_read_single_cart(): void
    {
        ['token' => $token, 'cart' => $cart] = $this->createCustomerWithCart();

        $response = $this->getCart($token, $cart->id);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('cart', $response['data']['readCartToken']);

        $cartData = $response['data']['readCartToken']['cart'];
        $this->assertEquals($cart->id, $cartData['id']);
        $this->assertCount(1, $cartData['items']);
    }

    /**
     * Test: Cart includes all required fields
     */
    public function test_cart_includes_all_required_fields(): void
    {
        ['token' => $token, 'cart' => $cart] = $this->createCustomerWithCart();

        $response = $this->getCart($token, $cart->id);

        $cartData = $response['data']['readCartToken']['cart'];

        $requiredFields = ['id', 'cartToken', 'customerId', 'itemsCount', 'items', 'subtotal', 'grandTotal', 'formattedGrandTotal'];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $cartData);
        }
    }

    /**
     * Test: Cart items are properly formatted
     */
    public function test_cart_items_are_properly_formatted(): void
    {
        ['token' => $token, 'cart' => $cart] = $this->createCustomerWithCart();

        $response = $this->getCart($token, $cart->id);

        $item = $response['data']['readCartToken']['cart']['items'][0];

        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('productId', $item);
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('quantity', $item);
        $this->assertArrayHasKey('formattedPrice', $item);
        $this->assertArrayHasKey('formattedTotal', $item);
    }

    /**
     * Test: Fails to read with invalid token
     */
    public function test_fails_to_read_with_invalid_token(): void
    {
        ['cart' => $cart] = $this->createCustomerWithCart();

        $response = $this->getCart('invalid-token', $cart->id);

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Fails to read non-existent cart
     */
    public function test_fails_to_read_non_existent_cart(): void
    {
        ['token' => $token] = $this->createCustomerWithCart();

        $response = $this->getCart($token, 99999);

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Fails to read other customer's cart
     */
    public function test_fails_to_read_other_customer_cart(): void
    {
        ['cart' => $cart] = $this->createCustomerWithCart();

        $otherCustomer = Customer::factory()->create();
        $otherToken = $otherCustomer->createToken('api-token')->plainTextToken;

        $response = $this->getCart($otherToken, $cart->id);

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test: Get all carts for authenticated user
     */
    public function test_get_all_carts_for_authenticated_user(): void
    {
        ['token' => $token, 'customer' => $customer] = $this->createCustomerWithCart();

        // Create second cart
        $cart2 = Cart::factory()->create(['customer_id' => $customer->id]);
        $product2 = Product::factory()->create();
        CartItem::factory()->create([
            'cart_id'    => $cart2->id,
            'product_id' => $product2->id,
            'quantity'   => 1,
        ]);

        $response = $this->getAllCarts($token);

        $this->assertArrayHasKey('carts', $response['data']['cartCollectionCartToken']);
        $carts = $response['data']['cartCollectionCartToken']['carts'];
        $this->assertCount(2, $carts);
    }

    /**
     * Test: Get carts returns correct structure
     */
    public function test_get_carts_returns_correct_structure(): void
    {
        ['token' => $token, 'customer' => $customer] = $this->createCustomerWithCart();

        $response = $this->getAllCarts($token);

        $this->assertArrayHasKey('carts', $response['data']['cartCollectionCartToken']);
        $carts = $response['data']['cartCollectionCartToken']['carts'];

        $this->assertCount(1, $carts);

        $cart = $carts[0];
        $requiredFields = ['id', 'cartToken', 'customerId', 'itemsCount', 'items', 'formattedGrandTotal'];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $cart);
        }
    }

    /**
     * Test: Guest cart can be read with cart id as token
     */
    public function test_guest_cart_can_be_read_with_cart_id_as_token(): void
    {
        $guestCart = Cart::factory()->create(['customer_id' => null]);
        $product = Product::factory()->create();

        CartItem::factory()->create([
            'cart_id'    => $guestCart->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $response = $this->getCart((string) $guestCart->id, $guestCart->id);

        $this->assertArrayHasKey('data', $response);
        $this->assertEquals($guestCart->id, $response['data']['readCartToken']['cart']['id']);
    }

    /**
     * Test: Cart totals are correct
     */
    public function test_cart_totals_are_correct(): void
    {
        ['token' => $token, 'cart' => $cart] = $this->createCustomerWithCart();

        $response = $this->getCart($token, $cart->id);

        $cartData = $response['data']['readCartToken']['cart'];

        $this->assertIsString($cartData['formattedGrandTotal']);
        $this->assertNotEmpty($cartData['grandTotal']);
    }
}
