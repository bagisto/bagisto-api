<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Wishlist;

class WishlistTest extends RestApiTestCase
{
    /**
     * The base url.
     */
    private string $baseUrl = '/api/shop/wishlists';

    // ============================================================================
    // GET Collection
    // ============================================================================

    /**
     * Test get wishlist collection.
     */
    public function test_get_wishlist_collection(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], $this->baseUrl);

        $response->assertOk();
        $data = $response->json();

        expect($data)->toBeArray();
        expect(count($data))->toBeGreaterThanOrEqual(2);
    }

    /**
     * Each wishlist item carries the product card, so the wishlist page needs no further call.
     */
    public function test_wishlist_items_embed_the_product_card(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], $this->baseUrl);

        $response->assertOk();

        $item = collect($response->json())->firstWhere('id', $testData['wishlistItem1']->id);

        expect($item['product'])->toBeArray()
            ->and($item['product']['id'])->toBe($testData['product1']->id)
            ->and($item['product']['sku'])->toBe($testData['product1']->sku)
            ->and($item['product'])->toHaveKeys(['name', 'price', 'formattedPrice', 'isSaleable', 'averageRating'])
            ->and($item)->toHaveKeys(['createdAt', 'updatedAt']);
    }

    /**
     * Test get wishlist collection requires auth.
     */
    public function test_get_wishlist_collection_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Test get wishlist collection only returns own items.
     */
    public function test_get_wishlist_collection_only_returns_own_items(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();
        $otherProduct = $this->createBaseProduct('simple');

        Wishlist::factory()->create([
            'customer_id' => $otherCustomer->id,
            'product_id' => $otherProduct->id,
            'channel_id' => Channel::first()->id,
        ]);

        $response = $this->authenticatedGet($testData['customer'], $this->baseUrl);

        $response->assertOk();
        $items = $response->json();

        foreach ($items as $item) {
            expect($item['customer']['id'] ?? $testData['customer']->id)->toBe($testData['customer']->id);
        }
    }

    /**
     * Test get wishlist collection default order is oldest first.
     */
    public function test_get_wishlist_collection_default_order_is_oldest_first(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], $this->baseUrl);

        $response->assertOk();
        $ids = collect($response->json())->pluck('id')->all();

        expect($ids[0])->toBe($testData['wishlistItem1']->id);
    }

    /**
     * Test get wishlist collection order desc returns newest first.
     */
    public function test_get_wishlist_collection_order_desc_returns_newest_first(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], $this->baseUrl.'?order=desc');

        $response->assertOk();
        $ids = collect($response->json())->pluck('id')->all();

        expect($ids[0])->toBe($testData['wishlistItem2']->id);
    }

    /**
     * Test get wishlist collection sort created at desc returns newest first.
     */
    public function test_get_wishlist_collection_sort_created_at_desc_returns_newest_first(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], $this->baseUrl.'?sort=created_at-desc');

        $response->assertOk();
        $ids = collect($response->json())->pluck('id')->all();

        expect($ids[0])->toBe($testData['wishlistItem2']->id);
    }

    // ============================================================================
    // GET Single
    // ============================================================================

    /**
     * Test get single wishlist item.
     */
    public function test_get_single_wishlist_item(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            $this->baseUrl.'/'.$testData['wishlistItem1']->id
        );

        $response->assertOk();
        $data = $response->json();

        expect($data)->toHaveKey('id');
        expect($data)->toHaveKey('product');
        expect($data)->toHaveKey('customer');
        expect($data)->toHaveKey('channel');
        expect($data)->toHaveKey('createdAt');
        expect($data)->toHaveKey('updatedAt');
        expect($data['id'])->toBe($testData['wishlistItem1']->id);
    }

    /**
     * Test cannot get other customers wishlist item.
     */
    public function test_cannot_get_other_customers_wishlist_item(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer(['email' => 'other.customer@example.com']);

        $response = $this->authenticatedGet(
            $otherCustomer,
            $this->baseUrl.'/'.$testData['wishlistItem1']->id
        );

        expect(in_array($response->getStatusCode(), [403, 404]))->toBeTrue();
    }

    /**
     * Test get non existent wishlist item returns 404.
     */
    public function test_get_non_existent_wishlist_item_returns_404(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, $this->baseUrl.'/999999');

        $response->assertNotFound();
    }

    /**
     * Test wishlist item id is integer.
     */
    public function test_wishlist_item_id_is_integer(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            $this->baseUrl.'/'.$testData['wishlistItem1']->id
        );

        $response->assertOk();

        expect($response->json('id'))->toBeInt();
    }

    /**
     * Test wishlist item timestamps are iso8601.
     */
    public function test_wishlist_item_timestamps_are_iso8601(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            $this->baseUrl.'/'.$testData['wishlistItem1']->id
        );

        $response->assertOk();

        expect($response->json('createdAt'))->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
        expect($response->json('updatedAt'))->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
    }

    // ============================================================================
    // POST Create
    // ============================================================================

    /**
     * Test create wishlist item.
     */
    public function test_create_wishlist_item(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $product = $this->createBaseProduct('simple');

        $response = $this->authenticatedPost($customer, $this->baseUrl, [
            'productId' => $product->id,
        ]);

        $response->assertCreated();
        $data = $response->json();

        expect($data)->toHaveKey('id');
        expect($data['id'])->toBeInt();
        expect(
            Wishlist::where('customer_id', $customer->id)
                ->where('product_id', $product->id)
                ->exists()
        )->toBeTrue();
    }

    /**
     * Test create wishlist item with snake case key.
     */
    public function test_create_wishlist_item_with_snake_case_key(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $product = $this->createBaseProduct('simple');

        $response = $this->authenticatedPost($customer, $this->baseUrl, [
            'product_id' => $product->id,
        ]);

        $response->assertCreated();
        expect($response->json('id'))->toBeInt();
    }

    /**
     * Test create wishlist requires auth.
     */
    public function test_create_wishlist_requires_auth(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');

        $response = $this->publicPost($this->baseUrl, ['productId' => $product->id]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Test create wishlist with nonexistent product returns error.
     */
    public function test_create_wishlist_with_nonexistent_product_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, $this->baseUrl, [
            'productId' => 999999,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
    }

    /**
     * Test create duplicate wishlist item returns error.
     */
    public function test_create_duplicate_wishlist_item_returns_error(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedPost($testData['customer'], $this->baseUrl, [
            'productId' => $testData['product1']->id,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 409, 422]);
    }

    // ============================================================================
    // DELETE Single
    // ============================================================================

    /**
     * Test delete wishlist item.
     */
    public function test_delete_wishlist_item(): void
    {
        $testData = $this->createTestData();
        $itemId = $testData['wishlistItem1']->id;

        $response = $this->authenticatedDelete(
            $testData['customer'],
            $this->baseUrl.'/'.$itemId
        );

        $response->assertNoContent();
        expect(Wishlist::find($itemId))->toBeNull();
    }

    /**
     * Test delete wishlist item requires auth.
     */
    public function test_delete_wishlist_item_requires_auth(): void
    {
        $testData = $this->createTestData();

        $response = $this->publicDelete($this->baseUrl.'/'.$testData['wishlistItem1']->id);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Test delete non existent wishlist item returns 404.
     */
    public function test_delete_non_existent_wishlist_item_returns_404(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedDelete($customer, $this->baseUrl.'/999999');

        $response->assertNotFound();
    }

    /**
     * Test cannot delete other customers wishlist item.
     */
    public function test_cannot_delete_other_customers_wishlist_item(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $response = $this->authenticatedDelete(
            $otherCustomer,
            $this->baseUrl.'/'.$testData['wishlistItem1']->id
        );

        expect($response->getStatusCode())->toBeIn([403, 404, 500]);
    }

    // ============================================================================
    // POST Toggle
    // ============================================================================

    /**
     * Test toggle adds product when not in wishlist.
     */
    public function test_toggle_adds_product_when_not_in_wishlist(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $product = $this->createBaseProduct('simple');

        $response = $this->authenticatedPost($customer, $this->baseUrl.'/toggle', [
            'productId' => $product->id,
        ]);

        $response->assertCreated();
        $data = $response->json();

        expect($data)->toHaveKey('id');
        expect($data['id'])->toBeInt();
        expect(
            Wishlist::where('customer_id', $customer->id)
                ->where('product_id', $product->id)
                ->exists()
        )->toBeTrue();
    }

    /**
     * Test toggle removes product when already in wishlist.
     */
    public function test_toggle_removes_product_when_already_in_wishlist(): void
    {
        $testData = $this->createTestData();
        $itemId = $testData['wishlistItem1']->id;
        $product = $testData['product1'];

        $response = $this->authenticatedPost($testData['customer'], $this->baseUrl.'/toggle', [
            'productId' => $product->id,
        ]);

        $response->assertCreated();
        $data = $response->json();

        expect($data)->toHaveKey('message');
        expect($data['message'])->toContain('Removed');
        expect(Wishlist::find($itemId))->toBeNull();
    }

    /**
     * Test toggle requires auth.
     */
    public function test_toggle_requires_auth(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');

        $response = $this->publicPost($this->baseUrl.'/toggle', [
            'productId' => $product->id,
        ]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Test toggle with nonexistent product returns error.
     */
    public function test_toggle_with_nonexistent_product_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, $this->baseUrl.'/toggle', [
            'productId' => 999999,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
    }

    /**
     * Test toggle add then toggle again removes.
     */
    public function test_toggle_add_then_toggle_again_removes(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $product = $this->createBaseProduct('simple');

        $addResponse = $this->authenticatedPost($customer, $this->baseUrl.'/toggle', [
            'productId' => $product->id,
        ]);
        $addResponse->assertCreated();
        expect(
            Wishlist::where('customer_id', $customer->id)->where('product_id', $product->id)->exists()
        )->toBeTrue();

        $removeResponse = $this->authenticatedPost($customer, $this->baseUrl.'/toggle', [
            'productId' => $product->id,
        ]);
        $removeResponse->assertCreated();
        expect(
            Wishlist::where('customer_id', $customer->id)->where('product_id', $product->id)->exists()
        )->toBeFalse();
    }

    // ============================================================================
    // Move To Cart
    // ============================================================================

    /**
     * Test move wishlist to cart.
     */
    public function test_move_wishlist_to_cart(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedPost($testData['customer'], '/api/shop/move-wishlist-to-carts', [
            'wishlistItemId' => $testData['wishlistItem1']->id,
            'quantity' => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([201, 400]);
    }

    /**
     * Test move to cart requires auth.
     */
    public function test_move_to_cart_requires_auth(): void
    {
        $response = $this->publicPost('/api/shop/move-wishlist-to-carts', [
            'wishlistItemId' => 1,
            'quantity' => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Test move nonexistent wishlist item to cart returns error.
     */
    public function test_move_nonexistent_wishlist_item_to_cart_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/move-wishlist-to-carts', [
            'wishlistItemId' => 999999,
            'quantity' => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
    }

    /**
     * Test cannot move other customers wishlist to cart.
     */
    public function test_cannot_move_other_customers_wishlist_to_cart(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $response = $this->authenticatedPost($otherCustomer, '/api/shop/move-wishlist-to-carts', [
            'wishlistItemId' => $testData['wishlistItem1']->id,
            'quantity' => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 403, 404, 500]);
    }

    // ============================================================================
    // Delete All
    // ============================================================================

    /**
     * Test delete all wishlists.
     */
    public function test_delete_all_wishlists(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedPost(
            $testData['customer'],
            '/api/shop/delete-all-wishlists'
        );

        $response->assertCreated();
        $data = $response->json();

        expect($data)->toHaveKey('message');
        expect($data)->toHaveKey('deletedCount');
        expect($data['deletedCount'])->toBe(2);
        expect(
            Wishlist::where('customer_id', $testData['customer']->id)->count()
        )->toBe(0);
    }

    /**
     * Test delete all wishlists requires auth.
     */
    public function test_delete_all_wishlists_requires_auth(): void
    {
        $response = $this->publicPost('/api/shop/delete-all-wishlists');

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Test delete all wishlists when empty returns zero count.
     */
    public function test_delete_all_wishlists_when_empty_returns_zero_count(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/delete-all-wishlists');

        $response->assertCreated();
        expect($response->json('deletedCount'))->toBe(0);
    }

    /**
     * Test delete all wishlists only removes own items.
     */
    public function test_delete_all_wishlists_only_removes_own_items(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();
        $otherProduct = $this->createBaseProduct('simple');

        Wishlist::factory()->create([
            'customer_id' => $otherCustomer->id,
            'product_id' => $otherProduct->id,
            'channel_id' => Channel::first()->id,
        ]);

        $this->authenticatedPost($testData['customer'], '/api/shop/delete-all-wishlists');

        expect(
            Wishlist::where('customer_id', $otherCustomer->id)->count()
        )->toBe(1);
    }

    /**
     * Create test data.
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product1 = $this->createBaseProduct('simple');
        $product2 = $this->createBaseProduct('simple');

        $wishlistItem1 = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product1->id,
            'channel_id' => $channel->id,
        ]);

        $wishlistItem2 = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product2->id,
            'channel_id' => $channel->id,
        ]);

        return compact('customer', 'channel', 'product1', 'product2', 'wishlistItem1', 'wishlistItem2');
    }
}
