<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Checkout\Repositories\CartRepository;

class MoveCartItemsToWishlistTest extends RestApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['url']->forceRootUrl('http://localhost');
    }

    private function createCustomerCartWithItem(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $product = $this->createTestProduct()['product'];

        $this->authenticatedPost($customer, '/api/shop/add-product-in-cart', [
            'productId' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();

        $cart = app(CartRepository::class)->findOneWhere([
            'customer_id' => $customer->id,
            'is_active' => 1,
        ]);

        return [$customer, $cart->items()->first(), $product];
    }

    public function test_move_cart_item_to_wishlist_removes_it_from_the_cart(): void
    {
        [$customer, $cartItem, $product] = $this->createCustomerCartWithItem();

        $response = $this->authenticatedPost($customer, '/api/shop/move-to-wishlists', [
            'itemIds' => [$cartItem->id],
            'quantities' => [1],
        ]);

        $response->assertCreated();

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
        $this->assertDatabaseHas('wishlist_items', [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_move_to_wishlist_requires_item_ids(): void
    {
        [$customer, $cartItem] = $this->createCustomerCartWithItem();

        $response = $this->authenticatedPost($customer, '/api/shop/move-to-wishlists', [
            'itemIds' => [],
            'quantities' => [],
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422]);
        $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id]);
    }

    public function test_customer_cannot_move_another_customers_cart_item_to_their_wishlist(): void
    {
        [$owner, $cartItem, $product] = $this->createCustomerCartWithItem();
        $otherCustomer = $this->createCustomer();

        $response = $this->authenticatedPost($otherCustomer, '/api/shop/move-to-wishlists', [
            'itemIds' => [$cartItem->id],
            'quantities' => [1],
        ]);

        expect($response->getStatusCode())->toBeIn([400, 404]);

        $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id]);
        $this->assertDatabaseMissing('wishlist_items', [
            'customer_id' => $otherCustomer->id,
            'product_id' => $product->id,
        ]);
        $this->assertDatabaseMissing('wishlist_items', [
            'customer_id' => $owner->id,
            'product_id' => $product->id,
        ]);
    }
}
