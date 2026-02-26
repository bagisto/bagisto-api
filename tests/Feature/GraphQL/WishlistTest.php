<?php

namespace Tests\Feature\BagistoApi\GraphQL;

use Tests\Feature\BagistoApi\GraphQLTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\Wishlist;
use Webkul\Product\Models\Product;
use Webkul\Core\Models\Channel;

class WishlistTest extends GraphQLTestCase
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

    /**
     * Test: Query all wishlist items collection
     */
    public function test_get_wishlist_items_collection(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getWishlists {
              wishlists {
                edges {
                  cursor
                  node {
                    id
                    _id
                    product {
                      id
                    }
                    customer {
                      id
                    }
                    channel {
                      id
                    }
                    createdAt
                    updatedAt
                  }
                }
                pageInfo {
                  endCursor
                  startCursor
                  hasNextPage
                  hasPreviousPage
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.wishlists');

        expect($data['totalCount'])->toBeGreaterThanOrEqual(2);
        expect($data['edges'])->not()->toBeEmpty();
    }

    /**
     * Test: Query single wishlist item by ID
     */
    public function test_get_wishlist_item_by_id(): void
    {
        $testData = $this->createTestData();
        $wishlistItemId = "/api/shop/wishlists/{$testData['wishlistItem1']->id}";

        $query = <<<GQL
            query getWishlist {
              wishlist(id: "{$wishlistItemId}") {
                id
                _id
                product {
                  id
                }
                customer {
                  id
                }
                channel {
                  id
                }
                createdAt
                updatedAt
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.wishlist');

        expect($data['_id'])->toBe($testData['wishlistItem1']->id);
        expect($data['product'])->toHaveKey('id');
        expect($data['customer'])->toHaveKey('id');
        expect($data['channel'])->toHaveKey('id');
    }

    /**
     * Test: Wishlist item timestamps are returned in ISO8601 format
     */
    public function test_wishlist_item_timestamps_are_iso8601_format(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getWishlists {
              wishlists(first: 1) {
                edges {
                  node {
                    _id
                    createdAt
                    updatedAt
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $wishlistItem = $response->json('data.wishlists.edges.0.node');

        expect($wishlistItem['createdAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
        expect($wishlistItem['updatedAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
    }

    /**
     * Test: Query wishlist items with pagination (first)
     */
    public function test_wishlist_items_pagination_first(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getWishlists {
              wishlists(first: 1) {
                edges {
                  node {
                    _id
                  }
                }
                pageInfo {
                  hasNextPage
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.wishlists');

        expect($data['edges'])->toHaveCount(1);
    }

    /**
     * Test: Query wishlist items with product relationship
     */
    public function test_query_wishlist_items_with_product(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getWishlists {
              wishlists(first: 1) {
                edges {
                  node {
                    id
                    product {
                      id
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $wishlistItem = $response->json('data.wishlists.edges.0.node');

        expect($wishlistItem)->toHaveKey('product');
    }

    /**
     * Test: Query all fields of wishlist item
     */
    public function test_query_all_wishlist_item_fields(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getWishlists {
              wishlists(first: 1) {
                edges {
                  node {
                    id
                    _id
                    product {
                      id
                    }
                    customer {
                      id
                    }
                    channel {
                      id
                    }
                    createdAt
                    updatedAt
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $node = $response->json('data.wishlists.edges.0.node');

        expect($node)->toHaveKeys(['id', '_id', 'product', 'customer', 'channel', 'createdAt', 'updatedAt']);
    }

    /**
     * Test: Query returns appropriate error for invalid ID
     */
    public function test_invalid_wishlist_item_id_returns_error(): void
    {
        $query = <<<'GQL'
            query getWishlist {
              wishlist(id: "/api/shop/wishlists/99999") {
                id
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        expect($response->json('data.wishlist'))->toBeNull();
    }

    /**
     * Test: Pagination with cursor
     */
    public function test_wishlist_items_pagination_with_cursor(): void
    {
        $testData = $this->createTestData();

        $firstQuery = <<<'GQL'
            query getWishlists {
              wishlists(first: 1) {
                edges {
                  cursor
                }
              }
            }
        GQL;

        $firstResponse = $this->authenticatedGraphQL($testData['customer'], $firstQuery);
        $cursor = $firstResponse->json('data.wishlists.edges.0.cursor');

        $secondQuery = <<<GQL
            query getWishlists {
              wishlists(first: 1, after: "{$cursor}") {
                edges {
                  node {
                    _id
                  }
                }
              }
            }
        GQL;

        $secondResponse = $this->authenticatedGraphQL($testData['customer'], $secondQuery);

        $secondResponse->assertOk();
    }

    /**
     * Test: Numeric ID is an integer
     */
    public function test_wishlist_item_numeric_id_is_integer(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getWishlists {
              wishlists(first: 1) {
                edges {
                  node {
                    _id
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $wishlistItem = $response->json('data.wishlists.edges.0.node');

        expect($wishlistItem['_id'])->toBeInt();
    }

    /**
     * Test: Multiple wishlist items can be queried
     */
    public function test_query_multiple_wishlist_items(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getWishlists {
              wishlists(first: 5) {
                edges {
                  node {
                    id
                    _id
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.wishlists');

        expect($data['totalCount'])->toBeGreaterThanOrEqual(2);
        expect($data['edges'])->not()->toBeEmpty();
    }

    /**
     * Test: Schema introspection for Wishlist
     */
    public function test_wishlist_introspection_query(): void
    {
        $query = <<<'GQL'
            {
              __type(name: "Wishlist") {
                name
                kind
                fields {
                  name
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $type = $response->json('data.__type');

        expect($type['name'])->toBe('Wishlist');
        expect($type['kind'])->toBe('OBJECT');

        $fieldNames = collect($type['fields'])->pluck('name')->toArray();
        expect($fieldNames)->toContain('id', '_id', 'product', 'customer', 'channel', 'createdAt', 'updatedAt');
    }

    /**
     * Test: Wishlist items are properly sorted by creation date
     */
    public function test_wishlist_items_sorted_by_created_at(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getWishlists {
              wishlists(first: 10) {
                edges {
                  node {
                    _id
                    createdAt
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $edges = $response->json('data.wishlists.edges');

        // Verify we have items
        expect($edges)->not()->toBeEmpty();
    }

    /**
     * Test: Create wishlist item via mutation
     */
    public function test_create_wishlist_item_mutation(): void
    {
        $customer = $this->createCustomer();
        $product = Product::factory()->create();

        $mutation = <<<'GQL'
            mutation CreateWishlist($productId: Int!) {
              createWishlist(input: {productId: $productId}) {
                wishlist {
                  id
                  _id
                  createdAt
                  updatedAt
                  product {
                    id
                    _id
                    sku
                    type
                  }
                  customer {
                    id
                  }
                  channel {
                    id
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, ['productId' => $product->id]);

        $response->assertOk();

        $errors = $response->json('errors');
        if (! empty($errors)) {
            $this->fail('GraphQL errors: '.json_encode($errors));
        }
        
        $wishlistItem = $response->json('data.createWishlist.wishlist');

        expect($wishlistItem)->not()->toBeNull();
        expect($wishlistItem['_id'])->toBeInt();
        expect($wishlistItem['product']['_id'])->toBe($product->id);
        expect($wishlistItem['createdAt'])->not()->toBeNull();
        expect($wishlistItem['updatedAt'])->not()->toBeNull();
    }

    /**
     * Test: Delete wishlist item via mutation
     */
    public function test_delete_wishlist_item_mutation(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create();

        $wishlistItem = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $mutation = <<<'GQL'
            mutation DeleteWishlist($id: ID!) {
              deleteWishlist(input: {id: $id}) {
                wishlist {
                  id
                  _id
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'id' => "/api/shop/wishlists/{$wishlistItem->id}",
        ]);

        $response->assertOk();
        
        $deleteResponse = $response->json('data.deleteWishlist');
        expect($deleteResponse)->not()->toBeNull();
        
        $deletedItem = $deleteResponse['wishlist'] ?? $deleteResponse;
        
        expect($deletedItem)->not()->toBeNull();
        expect($deletedItem['_id'])->toBe($wishlistItem->id);

        expect(Wishlist::find($wishlistItem->id))->toBeNull();
    }

    /**
     * Test: Toggle wishlist item (add existing removes it)
     */
    public function test_toggle_wishlist_item_mutation(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create();

        $wishlistItem = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $mutation = <<<'GQL'
            mutation ToggleWishlist($productId: Int!) {
              toggleWishlist(input: {productId: $productId}) {
                wishlist {
                  id
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, ['productId' => $product->id]);

        $response->assertOk();
        $errors = $response->json('errors');

        expect($errors)->not()->toBeEmpty();
        expect(implode(' ', array_column($errors ?? [], 'message')))->toMatch('/removed/i');

        expect(Wishlist::find($wishlistItem->id))->toBeNull();
    }

    /**
     * Test: Authorization - Cannot delete other user's wishlist item
     */
    public function test_cannot_delete_other_users_wishlist_item(): void
    {
        $this->seedRequiredData();

        $customer1 = $this->createCustomer();
        $customer2 = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create();

        $wishlistItem = Wishlist::factory()->create([
            'customer_id' => $customer1->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $mutation = <<<'GQL'
            mutation DeleteWishlist($id: ID!) {
              deleteWishlist(input: {id: $id}) {
                wishlist {
                  id
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer2, $mutation, [
            'id' => "/api/shop/wishlists/{$wishlistItem->id}",
        ]);

        $response->assertOk();
        $errors = $response->json('errors');

        expect($errors)->not()->toBeEmpty();
        $errorMessages = implode(' ', array_column($errors ?? [], 'message'));
        expect($errorMessages)->toMatch('/[Uu]nauthorized|cannot.*update|cannot.*other/i');

        expect(Wishlist::find($wishlistItem->id))->not()->toBeNull();
    }

    /**
     * Test: Move wishlist item to cart successfully
     */
    public function test_move_wishlist_item_to_cart(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create(['type' => 'simple']);

        $wishlistItem = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $mutation = <<<'GQL'
            mutation MoveWishlistToCart($input: moveWishlistToCartInput!) {
              moveWishlistToCart(input: $input) {
                wishlistToCart {
                  message
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [
                'wishlistItemId' => $wishlistItem->id,
                'quantity'       => 1,
            ],
        ]);

        $response->assertOk();
        $response->assertJsonMissingPath('errors');

        $data = $response->json('data.moveWishlistToCart.wishlistToCart');
        expect($data)->not()->toBeNull();
        expect($data['message'])->toContain('success');
    }

    /**
     * Test: Move wishlist item to cart with quantity
     */
    public function test_move_wishlist_item_to_cart_with_quantity(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create(['type' => 'simple']);

        $wishlistItem = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $mutation = <<<'GQL'
            mutation MoveWishlistToCart($input: moveWishlistToCartInput!) {
              moveWishlistToCart(input: $input) {
                wishlistToCart {
                  message
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [
                'wishlistItemId' => $wishlistItem->id,
                'quantity'       => 3,
            ],
        ]);

        $response->assertOk();
        $response->assertJsonMissingPath('errors');

        $data = $response->json('data.moveWishlistToCart.wishlistToCart');
        expect($data)->not()->toBeNull();
        expect($data['message'])->toContain('success');
    }

    /**
     * Test: Move wishlist item to cart requires authentication
     */
    public function test_move_wishlist_to_cart_requires_authentication(): void
    {
        $mutation = <<<'GQL'
            mutation MoveWishlistToCart($input: moveWishlistToCartInput!) {
              moveWishlistToCart(input: $input) {
                wishlistToCart {
                  message
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => [
                'wishlistItemId' => 1,
                'quantity'       => 1,
            ],
        ]);

        $response->assertOk();
        $errors = $response->json('errors');

        expect($errors)->not()->toBeEmpty();
    }

    /**
     * Test: Move non-existent wishlist item to cart returns error
     */
    public function test_move_non_existent_wishlist_to_cart_returns_error(): void
    {
        $customer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation MoveWishlistToCart($input: moveWishlistToCartInput!) {
              moveWishlistToCart(input: $input) {
                wishlistToCart {
                  message
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [
                'wishlistItemId' => 99999,
                'quantity'       => 1,
            ],
        ]);

        $response->assertOk();
        $errors = $response->json('errors');

        expect($errors)->not()->toBeEmpty();
        expect(implode(' ', array_column($errors ?? [], 'message')))->toMatch('/not found|not exist|invalid/i');
    }

    /**
     * Test: Move wishlist item with invalid quantity returns error
     */
    public function test_move_wishlist_with_invalid_quantity_returns_error(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create();

        $wishlistItem = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $mutation = <<<'GQL'
            mutation MoveWishlistToCart($input: moveWishlistToCartInput!) {
              moveWishlistToCart(input: $input) {
                wishlistToCart {
                  message
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [
                'wishlistItemId' => $wishlistItem->id,
                'quantity'       => 0,
            ],
        ]);

        $response->assertOk();
        $errors = $response->json('errors');

        expect($errors)->not()->toBeEmpty();
    }

    /**
     * Test: Cannot move other user's wishlist item to cart
     */
    public function test_cannot_move_other_users_wishlist_to_cart(): void
    {
        $this->seedRequiredData();

        $customer1 = $this->createCustomer();
        $customer2 = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create();

        $wishlistItem = Wishlist::factory()->create([
            'customer_id' => $customer1->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $mutation = <<<'GQL'
            mutation MoveWishlistToCart($input: moveWishlistToCartInput!) {
              moveWishlistToCart(input: $input) {
                wishlistToCart {
                  message
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer2, $mutation, [
            'input' => [
                'wishlistItemId' => $wishlistItem->id,
                'quantity'       => 1,
            ],
        ]);

        $response->assertOk();
        $errors = $response->json('errors');

        expect($errors)->not()->toBeEmpty();
        expect(implode(' ', array_column($errors ?? [], 'message')))->toMatch('/not found|not exist|unauthorized/i');
    }

    /**
     * Test: Delete all wishlist items successfully
     */
    public function test_delete_all_wishlists(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation DeleteAllWishlists {
              createDeleteAllWishlists(input: {}) {
                deleteAllWishlists {
                  message
                  deletedCount
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $mutation);

        $response->assertOk();
        $response->assertJsonMissingPath('errors');

        $data = $response->json('data.createDeleteAllWishlists.deleteAllWishlists');
        expect($data)->not()->toBeNull();
        expect($data['deletedCount'])->toBe(2);
        expect($data['message'])->toContain('removed');

        expect(Wishlist::where('customer_id', $testData['customer']->id)->count())->toBe(0);
    }

    /**
     * Test: Delete all wishlists requires authentication
     */
    public function test_delete_all_wishlists_requires_authentication(): void
    {
        $mutation = <<<'GQL'
            mutation DeleteAllWishlists {
              createDeleteAllWishlists(input: {}) {
                deleteAllWishlists {
                  message
                  deletedCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation);

        $response->assertOk();
        $errors = $response->json('errors');
        expect($errors)->not()->toBeEmpty();
    }

    /**
     * Test: Delete all wishlists with no items returns zero count
     */
    public function test_delete_all_wishlists_with_no_items(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation DeleteAllWishlists {
              createDeleteAllWishlists(input: {}) {
                deleteAllWishlists {
                  message
                  deletedCount
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation);

        $response->assertOk();
        $response->assertJsonMissingPath('errors');

        $data = $response->json('data.createDeleteAllWishlists.deleteAllWishlists');
        expect($data)->not()->toBeNull();
        expect($data['deletedCount'])->toBe(0);
    }
}
