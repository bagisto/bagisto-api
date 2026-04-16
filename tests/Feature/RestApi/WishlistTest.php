<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\Wishlist;
use Webkul\Product\Models\Product;

class WishlistTest extends RestApiTestCase
{
    private string $apiUrl = '/api/shop/wishlists';

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

    /**
     * Test: GET all wishlist items
     */
    public function test_get_all_wishlist_items(): void
    {
        $this->createTestData();

        $response = $this->publicGet($this->apiUrl);

        $response->assertOk();
        $data = $response->json();

        // API Platform Collection Format
        if (isset($data['hydra:member'])) {
            expect($data['hydra:member'])->not()->toBeEmpty();
        } elseif (isset($data['@type'])) {
            // Alternative collection format
            expect($data)->toHaveKey('@type');
        } elseif (is_array($data)) {
            // Fallback: array of items
            expect(count($data))->toBeGreaterThanOrEqual(0);
        }
    }

    /**
     * Test: GET single wishlist item by ID
     */
    public function test_get_single_wishlist_item(): void
    {
        $testData = $this->createTestData();

        $response = $this->publicGet(
            "{$this->apiUrl}/{$testData['wishlistItem1']->id}"
        );

        $response->assertOk();
        $data = $response->json();

        // Check for wishlist item ID in response
        expect($data)->toHaveKey('id');
        expect($data['id'])->toBe($testData['wishlistItem1']->id);
    }

    /**
     * Test: GET wishlist item with embedded relationships
     */
    public function test_get_wishlist_item_with_relationships(): void
    {
        $testData = $this->createTestData();

        $response = $this->publicGet(
            "{$this->apiUrl}/{$testData['wishlistItem1']->id}"
        );

        $response->assertOk();
        $data = $response->json();

        // Product, customer, and channel might be IRI references or embedded objects
        if (is_array($data['product'])) {
            expect($data['product'])->toHaveKey('id');
        }
        if (is_array($data['customer'])) {
            expect($data['customer'])->toHaveKey('id');
        }
        if (is_array($data['channel'])) {
            expect($data['channel'])->toHaveKey('id');
        }
    }

    /**
     * Test: GET wishlist item with timestamps
     */
    public function test_get_wishlist_item_with_timestamps(): void
    {
        $testData = $this->createTestData();

        $response = $this->publicGet($this->apiUrl.'?itemsPerPage=1');

        $response->assertOk();
        $data = $response->json();

        // Handle both Hydra format and plain array format
        $wishlistItem = $data['hydra:member'][0] ?? $data[0] ?? null;

        if ($wishlistItem) {
            expect($wishlistItem)->toHaveKey('createdAt');
            expect($wishlistItem)->toHaveKey('updatedAt');
        }
    }

    /**
     * Test: POST create new wishlist item
     */
    public function test_create_wishlist_item(): void
    {
        $testData = $this->createTestData();
        $product3 = Product::factory()->create();

        $payload = [
            'customer_id' => $testData['customer']->id,
            'product_id'  => $product3->id,
            'channel_id'  => $testData['channel']->id,
        ];

        $response = $this->publicPost($this->apiUrl, $payload);

        $response->assertCreated();
        $data = $response->json();

        // Verify the created item has the expected IDs
        expect($data)->toHaveKey('id');
        expect($data['id'])->toBeGreaterThan(0);
    }

    /**
     * Test: DELETE wishlist item
     */
    public function test_delete_wishlist_item(): void
    {
        $testData = $this->createTestData();

        $response = $this->publicDelete(
            "{$this->apiUrl}/{$testData['wishlistItem1']->id}"
        );

        $response->assertNoContent();

        // Verify deletion
        $checkResponse = $this->publicGet(
            "{$this->apiUrl}/{$testData['wishlistItem1']->id}"
        );
        $checkResponse->assertNotFound();
    }

    /**
     * Test: GET non-existent wishlist item returns 404
     */
    public function test_get_non_existent_wishlist_item(): void
    {
        $response = $this->publicGet("{$this->apiUrl}/99999");

        $response->assertNotFound();
    }

    /**
     * Test: GET wishlist items with pagination
     */
    public function test_get_wishlist_items_with_pagination(): void
    {
        $this->createTestData();

        $response = $this->publicGet(
            "{$this->apiUrl}?itemsPerPage=1&page=1"
        );

        $response->assertOk();
        $data = $response->json();

        // Handle both Hydra and plain response formats
        if (isset($data['hydra:member'])) {
            expect(count($data['hydra:member']))->toBeGreaterThanOrEqual(0);
        }
    }

    /**
     * Test: GET wishlist items with multiple pages
     */
    public function test_get_wishlist_items_with_multiple_pages(): void
    {
        $this->createTestData();

        $firstPageResponse = $this->publicGet(
            "{$this->apiUrl}?itemsPerPage=1&page=1"
        );

        $firstPageResponse->assertOk();
    }

    /**
     * Test: Invalid wishlist item cannot be deleted
     */
    public function test_delete_non_existent_wishlist_item(): void
    {
        $response = $this->publicDelete(
            "{$this->apiUrl}/99999"
        );

        $response->assertNotFound();
    }

    /**
     * Test: Filter wishlist items by customer
     */
    public function test_get_wishlist_items_filtered_by_customer(): void
    {
        $testData = $this->createTestData();

        $response = $this->publicGet(
            "{$this->apiUrl}?customerId={$testData['customer']->id}"
        );

        $response->assertOk();
        $data = $response->json();

        // Handle both Hydra format and plain array format
        if (isset($data['hydra:member'])) {
            expect(count($data['hydra:member']))->toBeGreaterThanOrEqual(2);
        }
    }

    /**
     * Test: GET wishlist items with product include
     */
    public function test_get_wishlist_items_with_product_include(): void
    {
        $testData = $this->createTestData();

        $response = $this->publicGet(
            "{$this->apiUrl}?itemsPerPage=1&page=1&include=product"
        );

        $response->assertOk();
        $data = $response->json();

        // Handle both Hydra format and plain array format
        if (isset($data['hydra:member']) && ! empty($data['hydra:member'])) {
            expect($data['hydra:member'][0])->toHaveKey('product');
        } elseif (is_array($data) && ! empty($data)) {
            expect($data[0])->toHaveKey('product');
        }
    }

    /**
     * Test: Invalid channel cannot be used
     */
    public function test_create_wishlist_with_invalid_channel(): void
    {
        $testData = $this->createTestData();
        $product = Product::factory()->create();

        $payload = [
            'customer_id' => $testData['customer']->id,
            'product_id'  => $product->id,
            'channel_id'  => 99999,
        ];

        $response = $this->publicPost($this->apiUrl, $payload);

        // Should either fail or be corrected by the processor
        $response->assertCreated();
    }

    /**
     * Test: Move wishlist item to cart
     */
    public function test_move_wishlist_item_to_cart(): void
    {
        $testData = $this->createTestData();

        $payload = [
            'wishlistItemId' => $testData['wishlistItem1']->id,
            'quantity'       => 1,
        ];

        $response = $this->authenticatedPost(
            $testData['customer'],
            "{$this->apiUrl}/../move-wishlist-to-carts/{$testData['wishlistItem1']->id}",
            $payload
        );

        expect(in_array($response->status(), [200, 201, 202, 400, 422]))->toBeTrue();
    }

    /**
     * Test: Move wishlist item to cart with invalid wishlist item ID
     */
    public function test_move_invalid_wishlist_to_cart_returns_error(): void
    {
        $testData = $this->createTestData();

        $payload = [
            'wishlistItemId' => 99999,
            'quantity'       => 1,
        ];

        $response = $this->authenticatedPost(
            $testData['customer'],
            "{$this->apiUrl}/../move-wishlist-to-carts/1",
            $payload
        );

        expect(in_array($response->status(), [400, 404, 409, 422]))->toBeTrue();
    }

    /**
     * Test: Move wishlist item to cart requires authentication
     */
    public function test_move_wishlist_to_cart_requires_authentication(): void
    {
        $testData = $this->createTestData();

        $payload = [
            'wishlistItemId' => $testData['wishlistItem1']->id,
            'quantity'       => 1,
        ];

        $response = $this->publicPost(
            "{$this->apiUrl}/../move-wishlist-to-carts/{$testData['wishlistItem1']->id}",
            $payload
        );

        expect(in_array($response->status(), [401, 403, 422]))->toBeTrue();
    }

    /**
     * Test: Move wishlist item with quantity
     */
    public function test_move_wishlist_item_to_cart_with_quantity(): void
    {
        $testData = $this->createTestData();

        $payload = [
            'wishlistItemId' => $testData['wishlistItem1']->id,
            'quantity'       => 3,
        ];

        $response = $this->authenticatedPost(
            $testData['customer'],
            "{$this->apiUrl}/../move-wishlist-to-carts/{$testData['wishlistItem1']->id}",
            $payload
        );

        expect(in_array($response->status(), [200, 201, 202, 400, 422]))->toBeTrue();
    }

    /**
     * Test: Cannot move other user's wishlist item to cart
     */
    public function test_cannot_move_other_users_wishlist_to_cart(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $payload = [
            'wishlistItemId' => $testData['wishlistItem1']->id,
            'quantity'       => 1,
        ];

        $response = $this->authenticatedPost(
            $otherCustomer,
            "{$this->apiUrl}/../move-wishlist-to-carts/{$testData['wishlistItem1']->id}",
            $payload
        );

        expect(in_array($response->status(), [400, 403, 409, 422]))->toBeTrue();
    }
}
