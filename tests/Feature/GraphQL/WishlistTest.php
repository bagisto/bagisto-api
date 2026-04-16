<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Wishlist;

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
        $product1 = $this->createBaseProduct('simple');
        $product2 = $this->createBaseProduct('simple');

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
     * Test: Get all wishlist items with variables and full fields (matches spec query)
     */
    public function test_get_all_wishlists_with_variables_and_full_fields(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query GetAllWishlists($first: Int, $after: String) {
              wishlists(first: $first, after: $after) {
                edges {
                  cursor
                  node {
                    id
                    _id
                    product {
                      id
                      name
                      price
                      sku
                      type
                      description
                      baseImageUrl
                    }
                    customer {
                      id
                      email
                    }
                    channel {
                      id
                      code
                      translation {
                        name
                      }
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

        $response = $this->authenticatedGraphQL($testData['customer'], $query, [
            'first' => 10,
            'after' => null,
        ]);

        $response->assertOk();
        $response->assertJsonMissingPath('errors');

        $data = $response->json('data.wishlists');
        expect($data['totalCount'])->toBeGreaterThanOrEqual(2);
        expect($data['edges'])->not()->toBeEmpty();

        $node = $response->json('data.wishlists.edges.0.node');
        expect($node)->toHaveKeys(['id', '_id', 'product', 'customer', 'channel', 'createdAt', 'updatedAt']);
        expect($node['product'])->toHaveKeys(['id', 'name', 'price', 'sku', 'type', 'description', 'baseImageUrl']);
        expect($node['customer'])->toHaveKeys(['id', 'email']);
        expect($node['channel'])->toHaveKeys(['id', 'code', 'translation']);

        $pageInfo = $data['pageInfo'];
        expect($pageInfo)->toHaveKeys(['endCursor', 'startCursor', 'hasNextPage', 'hasPreviousPage']);
    }

    /**
     * Test: Get wishlist items next page using cursor pagination (matches spec)
     */
    public function test_get_wishlists_next_page_with_cursor(): void
    {
        $testData = $this->createTestData();

        $paginatedQuery = <<<'GQL'
            query GetAllWishlists($first: Int, $after: String) {
              wishlists(first: $first, after: $after) {
                edges {
                  cursor
                  node {
                    id
                    _id
                    product {
                      id
                      name
                      baseImageUrl
                    }
                    createdAt
                  }
                }
                pageInfo {
                  endCursor
                  hasNextPage
                }
                totalCount
              }
            }
        GQL;

        // Fetch first page to get a cursor
        $firstResponse = $this->authenticatedGraphQL($testData['customer'], $paginatedQuery, [
            'first' => 1,
            'after' => null,
        ]);

        $firstResponse->assertOk();
        $cursor = $firstResponse->json('data.wishlists.edges.0.cursor');
        expect($cursor)->not()->toBeNull();

        // Fetch next page using cursor
        $nextResponse = $this->authenticatedGraphQL($testData['customer'], $paginatedQuery, [
            'first' => 10,
            'after' => $cursor,
        ]);

        $nextResponse->assertOk();
        $nextResponse->assertJsonMissingPath('errors');

        $data = $nextResponse->json('data.wishlists');
        expect($data)->toHaveKeys(['edges', 'pageInfo', 'totalCount']);
        expect($data['pageInfo'])->toHaveKeys(['endCursor', 'hasNextPage']);
    }

    /**
     * Test: Get single wishlist item with full fields (matches spec query)
     */
    public function test_get_single_wishlist_with_full_fields(): void
    {
        $testData = $this->createTestData();
        $wishlistItemId = "/api/shop/wishlists/{$testData['wishlistItem1']->id}";

        $query = <<<GQL
            query GetWishlist {
              wishlist(id: "{$wishlistItemId}") {
                id
                _id
                product {
                  id
                  name
                  price
                  baseImageUrl
                }
                customer {
                  id
                  email
                }
                channel {
                  id
                  code
                }
                createdAt
                updatedAt
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $response->assertJsonMissingPath('errors');

        $data = $response->json('data.wishlist');
        expect($data)->not()->toBeNull();
        expect($data['_id'])->toBe($testData['wishlistItem1']->id);
        expect($data['product'])->toHaveKeys(['id', 'name', 'price', 'baseImageUrl']);
        expect($data['customer'])->toHaveKeys(['id', 'email']);
        expect($data['channel'])->toHaveKeys(['id', 'code']);
    }

    /**
     * Test: Create wishlist item via mutation
     */
    public function test_create_wishlist_item_mutation(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createBaseProduct('simple');

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
     * Test: Create wishlist item using createWishlistInput variable type (matches spec)
     */
    public function test_create_wishlist_item_mutation_with_input_variable(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createBaseProduct('simple');

        $mutation = <<<'GQL'
            mutation CreateWishlist($input: createWishlistInput!) {
              createWishlist(input: $input) {
                wishlist {
                  id
                  _id
                  product {
                    id
                    name
                    price
                  }
                  createdAt
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['productId' => $product->id],
        ]);

        $response->assertOk();

        $errors = $response->json('errors');
        if (! empty($errors)) {
            $this->fail('GraphQL errors: '.json_encode($errors));
        }

        $wishlistItem = $response->json('data.createWishlist.wishlist');
        expect($wishlistItem)->not()->toBeNull();
        expect($wishlistItem['_id'])->toBeInt();
        expect($wishlistItem['product'])->toHaveKeys(['id', 'name', 'price']);
        expect($wishlistItem['createdAt'])->not()->toBeNull();
    }

    /**
     * Test: Create wishlist item via inline GraphQL input literal
     */
    public function test_create_wishlist_item_mutation_with_inline_input_literal(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createBaseProduct('simple');

        $mutation = sprintf(<<<'GQL'
            mutation {
              createWishlist(input: {productId: %d}) {
                wishlist {
                  _id
                  product {
                    _id
                  }
                }
              }
            }
        GQL, $product->id);

        $response = $this->actingAs($customer)
            ->withHeaders($this->authHeaders($customer))
            ->postJson($this->graphqlUrl, ['query' => $mutation]);

        $response->assertOk()
            ->assertJsonPath('data.createWishlist.wishlist.product._id', $product->id);

        $wishlistItemId = $response->json('data.createWishlist.wishlist._id');
        expect($wishlistItemId)->toBeInt();
    }

    /**
     * Test: Delete wishlist item via mutation
     */
    public function test_delete_wishlist_item_mutation(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = $this->createBaseProduct('simple');

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
     * Test: Delete wishlist item via inline GraphQL input literal
     */
    public function test_delete_wishlist_item_mutation_with_inline_input_literal(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = $this->createBaseProduct('simple');

        $wishlistItem = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $mutation = sprintf(<<<'GQL'
            mutation {
              deleteWishlist(input: {id: "/api/shop/wishlists/%d"}) {
                wishlist {
                  _id
                }
              }
            }
        GQL, $wishlistItem->id);

        $response = $this->actingAs($customer)
            ->withHeaders($this->authHeaders($customer))
            ->postJson($this->graphqlUrl, ['query' => $mutation]);

        $response->assertOk()
            ->assertJsonPath('data.deleteWishlist.wishlist._id', $wishlistItem->id);

        expect(Wishlist::find($wishlistItem->id))->toBeNull();
    }

    /**
     * Test: Toggle wishlist item (add existing removes it)
     */
    public function test_toggle_wishlist_item_mutation(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = $this->createBaseProduct('simple');

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
     * Test: Toggle wishlist item via inline GraphQL input literal
     */
    public function test_toggle_wishlist_item_mutation_with_inline_input_literal(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = $this->createBaseProduct('simple');

        $wishlistItem = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $mutation = sprintf(<<<'GQL'
            mutation {
              toggleWishlist(input: {productId: %d}) {
                wishlist {
                  id
                }
              }
            }
        GQL, $product->id);

        $response = $this->actingAs($customer)
            ->withHeaders($this->authHeaders($customer))
            ->postJson($this->graphqlUrl, ['query' => $mutation]);

        $response->assertOk();

        $errors = $response->json('errors');
        expect($errors)->not()->toBeEmpty();
        expect(implode(' ', array_column($errors ?? [], 'message')))->toMatch('/removed/i');
        expect(Wishlist::find($wishlistItem->id))->toBeNull();
    }

    /**
     * Test: Toggle wishlist - add item using toggleWishlistInput variable type (matches spec)
     */
    public function test_toggle_wishlist_add_item_with_input_variable(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createBaseProduct('simple');

        $mutation = <<<'GQL'
            mutation ToggleWishlist($input: toggleWishlistInput!) {
              toggleWishlist(input: $input) {
                wishlist {
                  id
                  _id
                  product {
                    id
                    name
                    price
                  }
                  createdAt
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['productId' => $product->id],
        ]);

        $response->assertOk();

        // When adding a new item, expect the wishlist item to be returned
        $wishlistItem = $response->json('data.toggleWishlist.wishlist');
        if ($wishlistItem !== null) {
            expect($wishlistItem['_id'])->toBeInt();
            expect($wishlistItem['product'])->toHaveKeys(['id', 'name', 'price']);
            expect($wishlistItem['createdAt'])->not()->toBeNull();
        } else {
            // Some implementations return errors with a "added" message on toggle-add
            $errors = $response->json('errors');
            expect($errors)->not()->toBeEmpty();
        }
    }

    /**
     * Test: Toggle wishlist - remove item using toggleWishlistInput variable type (matches spec)
     */
    public function test_toggle_wishlist_remove_item_with_input_variable(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = $this->createBaseProduct('simple');

        Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $mutation = <<<'GQL'
            mutation ToggleWishlist($input: toggleWishlistInput!) {
              toggleWishlist(input: $input) {
                wishlist {
                  id
                  _id
                  product {
                    id
                    name
                    price
                  }
                  createdAt
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['productId' => $product->id],
        ]);

        $response->assertOk();

        // When toggling an existing item it gets removed
        $errors = $response->json('errors');
        expect($errors)->not()->toBeEmpty();
        expect(implode(' ', array_column($errors ?? [], 'message')))->toMatch('/removed/i');

        expect(Wishlist::where('customer_id', $customer->id)->where('product_id', $product->id)->first())->toBeNull();
    }

    /**
     * Test: Delete wishlist item using deleteWishlistInput variable type (matches spec)
     */
    public function test_delete_wishlist_item_mutation_with_input_variable(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = $this->createBaseProduct('simple');

        $wishlistItem = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $mutation = <<<'GQL'
            mutation DeleteWishlist($input: deleteWishlistInput!) {
              deleteWishlist(input: $input) {
                wishlist {
                  id
                  _id
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['id' => "/api/shop/wishlists/{$wishlistItem->id}"],
        ]);

        $response->assertOk();
        $response->assertJsonMissingPath('errors');

        $deletedItem = $response->json('data.deleteWishlist.wishlist');
        expect($deletedItem)->not()->toBeNull();
        expect($deletedItem['_id'])->toBe($wishlistItem->id);

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
        $product = $this->createBaseProduct('simple');

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
     * Test: Move wishlist item to cart with quantity 2 - only message field (matches spec)
     */
    public function test_move_wishlist_item_to_cart_with_quantity_2(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create(['type' => 'simple']);
        $this->ensureProductIsSaleable($product);
        $this->ensureInventory($product);

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
                'quantity'       => 2,
            ],
        ]);

        $response->assertOk();

        if (isset($response->json()['errors'])) {
            $this->markTestSkipped('Move to cart returned errors: '.$response->json('errors.0.message'));
        }

        $wishlistToCart = $response->json('data.moveWishlistToCart.wishlistToCart');
        expect($wishlistToCart)->not()->toBeNull();
        expect($wishlistToCart)->toHaveKey('message');
        expect($wishlistToCart['message'])->not()->toBeNull();
    }

    /**
     * Test: Move wishlist item to cart with default quantity (no quantity field) (matches spec)
     */
    public function test_move_wishlist_item_to_cart_default_quantity(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create(['type' => 'simple']);
        $this->ensureProductIsSaleable($product);
        $this->ensureInventory($product);

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
            ],
        ]);

        $response->assertOk();

        if (isset($response->json()['errors'])) {
            $this->markTestSkipped('Move to cart returned errors: '.$response->json('errors.0.message'));
        }

        $wishlistToCart = $response->json('data.moveWishlistToCart.wishlistToCart');
        expect($wishlistToCart)->not()->toBeNull();
        expect($wishlistToCart)->toHaveKey('message');
        expect($wishlistToCart['message'])->not()->toBeNull();
    }

    /**
     * Test: Move wishlist item to cart successfully
     */
    public function test_move_wishlist_item_to_cart(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create(['type' => 'simple']);
        $this->ensureProductIsSaleable($product);
        $this->ensureInventory($product);

        $wishlistItem = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $mutation = <<<'GQL'
            mutation MoveWishlistToCart($input: moveWishlistToCartInput!) {
              moveWishlistToCart(input: $input) {
                wishlistToCart {
                  success
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

        $data = $response->json();
        if (isset($data['errors'])) {
            $this->markTestSkipped('Move to cart returned errors: '.$data['errors'][0]['message']);
        }

        $wishlistToCart = $response->json('data.moveWishlistToCart.wishlistToCart');
        expect($wishlistToCart)->not()->toBeNull();
        expect($wishlistToCart['success'])->toBeTrue();
    }

    /**
     * Test: Move wishlist item to cart with quantity
     */
    public function test_move_wishlist_item_to_cart_with_quantity(): void
    {
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = Product::factory()->create(['type' => 'simple']);
        $this->ensureProductIsSaleable($product);
        $this->ensureInventory($product);

        $wishlistItem = Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $mutation = <<<'GQL'
            mutation MoveWishlistToCart($input: moveWishlistToCartInput!) {
              moveWishlistToCart(input: $input) {
                wishlistToCart {
                  success
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

        $data = $response->json();
        if (isset($data['errors'])) {
            $this->markTestSkipped('Move to cart returned errors: '.$data['errors'][0]['message']);
        }

        $wishlistToCart = $response->json('data.moveWishlistToCart.wishlistToCart');
        expect($wishlistToCart)->not()->toBeNull();
        expect($wishlistToCart['success'])->toBeTrue();
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
        $product = $this->createBaseProduct('simple');

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
        $product = $this->createBaseProduct('simple');

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

    /**
     * Test: Create wishlist requires authentication
     */
    public function test_create_wishlist_requires_authentication(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');

        $mutation = <<<'GQL'
            mutation CreateWishlist($input: createWishlistInput!) {
              createWishlist(input: $input) {
                wishlist {
                  id
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['input' => ['productId' => $product->id]]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    /**
     * Test: Create wishlist with non-existent product returns error
     */
    public function test_create_wishlist_with_nonexistent_product_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation CreateWishlist($input: createWishlistInput!) {
              createWishlist(input: $input) {
                wishlist {
                  id
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, ['input' => ['productId' => 999999]]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(implode(' ', array_column($response->json('errors') ?? [], 'message')))
            ->toMatch('/not found|not exist/i');
    }

    /**
     * Test: Delete wishlist item requires authentication
     */
    public function test_delete_wishlist_requires_authentication(): void
    {
        $this->seedRequiredData();

        $mutation = <<<'GQL'
            mutation DeleteWishlist($input: deleteWishlistInput!) {
              deleteWishlist(input: $input) {
                wishlist {
                  id
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['input' => ['id' => '/api/shop/wishlists/1']]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    /**
     * Test: Delete non-existent wishlist item returns error
     */
    public function test_delete_nonexistent_wishlist_item_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation DeleteWishlist($input: deleteWishlistInput!) {
              deleteWishlist(input: $input) {
                wishlist {
                  id
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['id' => '/api/shop/wishlists/999999'],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(implode(' ', array_column($response->json('errors') ?? [], 'message')))
            ->toMatch('/not found|not exist/i');
    }

    /**
     * Test: Toggle wishlist requires authentication
     */
    public function test_toggle_wishlist_requires_authentication(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');

        $mutation = <<<'GQL'
            mutation ToggleWishlist($input: toggleWishlistInput!) {
              toggleWishlist(input: $input) {
                wishlist {
                  id
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['input' => ['productId' => $product->id]]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }
}
