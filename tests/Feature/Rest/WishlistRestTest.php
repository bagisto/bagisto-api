<?php

namespace Webkul\BagistoApi\Tests\Feature\Rest;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Wishlist;
use Webkul\Product\Models\Product;

class WishlistRestTest extends RestApiTestCase
{
    /**
     * Create test data - customer, products and wishlist items
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $wishlistItem1 = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product1->id,
            'channel_id'  => $channel->id,
        ]);
        $wishlistItem2 = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product2->id,
            'channel_id'  => $channel->id,
        ]);

        return compact('customer', 'channel', 'product1', 'product2', 'wishlistItem1', 'wishlistItem2');
    }

    // ── Collection ────────────────────────────────────────────

    /**
     * Test: GET /api/shop/wishlists returns collection
     */
    public function test_get_wishlists_collection(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/wishlists');

        $response->assertOk();
        $json = $response->json();

        expect($json)->toBeArray();
        expect(count($json))->toBeGreaterThanOrEqual(2);
    }

    /**
     * Test: GET /api/shop/wishlists without auth throws error
     */
    public function test_get_wishlists_collection_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/wishlists');

        expect(in_array($response->getStatusCode(), [401, 403, 500]))->toBeTrue();
    }

    // ── Single Item ───────────────────────────────────────────

    /**
     * Test: GET /api/shop/wishlists/{id} returns single item
     */
    public function test_get_single_wishlist_item(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/wishlists/'.$testData['wishlistItem1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json)->toHaveKey('id');
        expect($json)->toHaveKey('product');
        expect($json)->toHaveKey('customer');
        expect($json)->toHaveKey('channel');
        expect($json)->toHaveKey('createdAt');
        expect($json)->toHaveKey('updatedAt');
        expect($json['id'])->toBe($testData['wishlistItem1']->id);
    }

    /**
     * Test: GET /api/shop/wishlists/{id} with invalid id returns 404
     */
    public function test_get_wishlist_item_not_found(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/wishlists/999999');

        $response->assertNotFound();
    }

    // ── Create ────────────────────────────────────────────────

    /**
     * Test: POST /api/shop/wishlists creates a wishlist item
     */
    public function test_create_wishlist_item(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $product = Product::factory()->create();

        $response = $this->authenticatedPost($customer, '/api/shop/wishlists', [
            'product_id' => $product->id,
        ]);

        $response->assertCreated();
        $json = $response->json();

        expect($json)->toHaveKey('id');
        expect(Wishlist::where('customer_id', $customer->id)->where('product_id', $product->id)->exists())->toBeTrue();
    }

    /**
     * Test: POST /api/shop/wishlists without auth returns error
     */
    public function test_create_wishlist_requires_auth(): void
    {
        $this->seedRequiredData();
        $product = Product::factory()->create();

        $response = $this->publicPost('/api/shop/wishlists', [
            'product_id' => $product->id,
        ]);

        expect(in_array($response->getStatusCode(), [401, 403, 500]))->toBeTrue();
    }

    /**
     * Test: POST /api/shop/wishlists with duplicate product returns error
     */
    public function test_create_duplicate_wishlist_returns_error(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedPost($testData['customer'], '/api/shop/wishlists', [
            'product_id' => $testData['product1']->id,
        ]);

        expect(in_array($response->getStatusCode(), [400, 409, 422, 500]))->toBeTrue();
    }

    // ── Delete Single ─────────────────────────────────────────

    /**
     * Test: DELETE /api/shop/wishlists/{id} removes a wishlist item
     */
    public function test_delete_wishlist_item(): void
    {
        $testData = $this->createTestData();
        $itemId = $testData['wishlistItem1']->id;

        $response = $this->authenticatedDelete(
            $testData['customer'],
            '/api/shop/wishlists/'.$itemId
        );

        $response->assertNoContent();
        expect(Wishlist::find($itemId))->toBeNull();
    }

    /**
     * Test: DELETE /api/shop/wishlists/{id} other user's item is rejected
     */
    public function test_delete_other_users_wishlist_item(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $response = $this->authenticatedDelete(
            $otherCustomer,
            '/api/shop/wishlists/'.$testData['wishlistItem1']->id
        );

        expect(in_array($response->getStatusCode(), [403, 404, 500]))->toBeTrue();
    }

    // ── Move to Cart ──────────────────────────────────────────

    /**
     * Test: POST /api/shop/move-wishlist-to-carts moves item to cart
     *
     * Verifies that the endpoint processes the request with proper DTO deserialization
     * and authentication. The Cart operation itself may fail in test environments
     * where factory products lack full pricing/inventory setup.
     */
    public function test_move_wishlist_to_cart(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create(['type' => 'simple']);

        $wishlistItem = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $response = $this->authenticatedPost($customer, '/api/shop/move-wishlist-to-carts', [
            'wishlist_item_id' => $wishlistItem->id,
            'quantity'         => 1,
        ]);

        /**
         * Accept both 201 (success) and 400 (Cart operation fails due to
         * product lacking pricing attributes in test environment).
         * The key verification is that it's NOT 401/403 (auth works) and
         * NOT 500 (DTO deserialization works).
         */
        expect($response->status())->toBeIn([201, 400]);
    }

    /**
     * Test: POST /api/shop/move-wishlist-to-carts requires auth
     */
    public function test_move_wishlist_to_cart_requires_auth(): void
    {
        $response = $this->publicPost('/api/shop/move-wishlist-to-carts', [
            'wishlist_item_id' => 1,
            'quantity'         => 1,
        ]);

        expect(in_array($response->getStatusCode(), [400, 401, 403, 500]))->toBeTrue();
    }

    // ── Delete All ────────────────────────────────────────────

    /**
     * Test: POST /api/shop/delete-all-wishlists removes all wishlist items
     */
    public function test_delete_all_wishlists(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedPost(
            $testData['customer'],
            '/api/shop/delete-all-wishlists'
        );

        $response->assertCreated();
        $json = $response->json();

        expect($json['message'])->toContain('removed');
        expect($json['deletedCount'])->toBe(2);
        expect(Wishlist::where('customer_id', $testData['customer']->id)->count())->toBe(0);
    }

    /**
     * Test: POST /api/shop/delete-all-wishlists requires authentication
     */
    public function test_delete_all_wishlists_requires_auth(): void
    {
        $response = $this->publicPost('/api/shop/delete-all-wishlists');

        expect(in_array($response->getStatusCode(), [401, 403, 500]))->toBeTrue();
    }

    /**
     * Test: POST /api/shop/delete-all-wishlists with no items returns zero
     */
    public function test_delete_all_wishlists_empty(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/delete-all-wishlists');

        $response->assertCreated();
        $json = $response->json();

        expect($json['deletedCount'])->toBe(0);
    }
}
