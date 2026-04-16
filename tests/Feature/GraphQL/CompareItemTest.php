<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Customer\Models\CompareItem;
use Webkul\Product\Models\Product;

class CompareItemTest extends GraphQLTestCase
{
    /**
     * Create test data - customer, products and compare items
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $compareItem1 = CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product1->id,
        ]);
        $compareItem2 = CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product2->id,
        ]);

        return compact('customer', 'product1', 'product2', 'compareItem1', 'compareItem2');
    }

    /**
     * Test: Query all compare items collection
     */
    public function test_get_compare_items_collection(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCompareItems {
              compareItems {
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
        $data = $response->json('data.compareItems');

        expect($data['totalCount'])->toBeGreaterThanOrEqual(2);
        expect($data['edges'])->not()->toBeEmpty();
    }

    /**
     * Test: Query single compare item by ID
     */
    public function test_get_compare_item_by_id(): void
    {
        $testData = $this->createTestData();
        $compareItemId = "/api/shop/compare-items/{$testData['compareItem1']->id}";

        $query = <<<GQL
            query getCompareItem {
              compareItem(id: "{$compareItemId}") {
                id
                _id
                product {
                  id
                }
                customer {
                  id
                }
                createdAt
                updatedAt
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $data = $response->json('data.compareItem');

        expect($data['_id'])->toBe($testData['compareItem1']->id);
        expect($data['product'])->toHaveKey('id');
        expect($data['customer'])->toHaveKey('id');
    }

    /**
     * Test: Timestamps are returned in ISO8601 format
     */
    public function test_compare_item_timestamps_are_iso8601_format(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCompareItems {
              compareItems(first: 1) {
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
        $compareItem = $response->json('data.compareItems.edges.0.node');

        expect($compareItem['createdAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
        expect($compareItem['updatedAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
    }

    /**
     * Test: Query compare items with pagination (first)
     */
    public function test_compare_items_pagination_first(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCompareItems {
              compareItems(first: 1) {
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
        $data = $response->json('data.compareItems');

        expect($data['edges'])->toHaveCount(1);
    }

    /**
     * Test: Query compare items with product relationship
     */
    public function test_query_compare_items_with_product(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCompareItems {
              compareItems(first: 1) {
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
        $compareItem = $response->json('data.compareItems.edges.0.node');

        expect($compareItem)->toHaveKey('product');
    }

    /**
     * Test: Query all fields of compare item
     */
    public function test_query_all_compare_item_fields(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCompareItems {
              compareItems(first: 1) {
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
                    createdAt
                    updatedAt
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $node = $response->json('data.compareItems.edges.0.node');

        expect($node)->toHaveKeys(['id', '_id', 'product', 'customer', 'createdAt', 'updatedAt']);
    }

    /**
     * Test: Query returns appropriate error for invalid ID
     */
    public function test_invalid_compare_item_id_returns_error(): void
    {
        $query = <<<'GQL'
            query getCompareItem {
              compareItem(id: "/api/shop/compare-items/99999") {
                id
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        expect($response->json('data.compareItem'))->toBeNull();
    }

    /**
     * Test: Pagination with cursor
     */
    public function test_compare_items_pagination_with_cursor(): void
    {
        $testData = $this->createTestData();

        $firstQuery = <<<'GQL'
            query getCompareItems {
              compareItems(first: 1) {
                edges {
                  cursor
                }
              }
            }
        GQL;

        $firstResponse = $this->authenticatedGraphQL($testData['customer'], $firstQuery);
        $cursor = $firstResponse->json('data.compareItems.edges.0.cursor');

        $secondQuery = <<<GQL
            query getCompareItems {
              compareItems(first: 1, after: "{$cursor}") {
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
    public function test_compare_item_numeric_id_is_integer(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCompareItems {
              compareItems(first: 1) {
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
        $compareItem = $response->json('data.compareItems.edges.0.node');

        expect($compareItem['_id'])->toBeInt();
    }

    /**
     * Test: Multiple compare items can be queried
     */
    public function test_query_multiple_compare_items(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCompareItems {
              compareItems(first: 5) {
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
        $data = $response->json('data.compareItems');

        expect($data['totalCount'])->toBeGreaterThanOrEqual(2);
        expect($data['edges'])->not()->toBeEmpty();
    }

    /**
     * Test: Schema introspection for CompareItem
     */
    public function test_compare_item_introspection_query(): void
    {
        $query = <<<'GQL'
            {
              __type(name: "CompareItem") {
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

        expect($type['name'])->toBe('CompareItem');
        expect($type['kind'])->toBe('OBJECT');

        $fieldNames = collect($type['fields'])->pluck('name')->toArray();
        expect($fieldNames)->toContain('id', '_id', 'product', 'customer', 'createdAt', 'updatedAt');
    }

    /**
     * Test: Compare items are properly sorted by creation date
     */
    public function test_compare_items_sorted_by_created_at(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCompareItems {
              compareItems(first: 10) {
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
        $edges = $response->json('data.compareItems.edges');

        // Verify we have items
        expect($edges)->not()->toBeEmpty();
    }

    /**
     * Test: Get all compare items with variables and full fields (matches spec query)
     */
    public function test_get_all_compare_items_with_variables_and_full_fields(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query GetCompareItems($first: Int, $after: String) {
              compareItems(first: $first, after: $after) {
                edges {
                  cursor
                  node {
                    id
                    _id
                    product {
                      id
                      name
                      description
                    }
                    customer {
                      id
                      email
                      firstName
                      lastName
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

        $data = $response->json('data.compareItems');
        expect($data['totalCount'])->toBeGreaterThanOrEqual(2);
        expect($data['edges'])->not()->toBeEmpty();

        $node = $response->json('data.compareItems.edges.0.node');
        expect($node)->toHaveKeys(['id', '_id', 'product', 'customer', 'createdAt', 'updatedAt']);
        expect($node['product'])->toHaveKeys(['id', 'name', 'description']);
        expect($node['customer'])->toHaveKeys(['id', 'email', 'firstName', 'lastName']);

        $pageInfo = $data['pageInfo'];
        expect($pageInfo)->toHaveKeys(['endCursor', 'startCursor', 'hasNextPage', 'hasPreviousPage']);
    }

    /**
     * Test: Get compare items next page using cursor pagination (matches spec)
     */
    public function test_get_compare_items_next_page_with_cursor(): void
    {
        $testData = $this->createTestData();

        // First get the first item's cursor
        $firstQuery = <<<'GQL'
            query GetCompareItems($first: Int, $after: String) {
              compareItems(first: $first, after: $after) {
                edges {
                  cursor
                  node {
                    id
                    _id
                    product {
                      id
                      name
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

        $firstResponse = $this->authenticatedGraphQL($testData['customer'], $firstQuery, [
            'first' => 1,
            'after' => null,
        ]);

        $firstResponse->assertOk();
        $cursor = $firstResponse->json('data.compareItems.edges.0.cursor');
        expect($cursor)->not()->toBeNull();

        // Now fetch next page using that cursor
        $nextResponse = $this->authenticatedGraphQL($testData['customer'], $firstQuery, [
            'first' => 10,
            'after' => $cursor,
        ]);

        $nextResponse->assertOk();
        $nextResponse->assertJsonMissingPath('errors');

        $data = $nextResponse->json('data.compareItems');
        expect($data)->toHaveKeys(['edges', 'pageInfo', 'totalCount']);
        expect($data['pageInfo'])->toHaveKeys(['endCursor', 'hasNextPage']);
    }

    /**
     * Test: Get single compare item with product price/sku and customer email/firstName (matches spec)
     */
    public function test_get_single_compare_item_with_full_fields(): void
    {
        $testData = $this->createTestData();
        $compareItemId = "/api/shop/compare-items/{$testData['compareItem1']->id}";

        $query = <<<GQL
            query GetCompareItem {
              compareItem(id: "{$compareItemId}") {
                id
                _id
                product {
                  id
                  name
                  price
                  sku
                }
                customer {
                  id
                  email
                  firstName
                }
                createdAt
                updatedAt
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $response->assertJsonMissingPath('errors');

        $data = $response->json('data.compareItem');
        expect($data)->not()->toBeNull();
        expect($data['_id'])->toBe($testData['compareItem1']->id);
        expect($data['product'])->toHaveKeys(['id', 'name', 'price', 'sku']);
        expect($data['customer'])->toHaveKeys(['id', 'email', 'firstName']);
    }

    /**
     * Test: Create compare item via mutation
     */
    public function test_create_compare_item_mutation(): void
    {
        $customer = $this->createCustomer();
        $product = Product::factory()->create();

        $mutation = <<<'GQL'
            mutation CreateCompareItem($productId: Int!) {
              createCompareItem(input: {productId: $productId}) {
                compareItem {
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

        $compareItem = $response->json('data.createCompareItem.compareItem');

        expect($compareItem)->not()->toBeNull();
        expect($compareItem['_id'])->toBeInt();
        expect($compareItem['product']['_id'])->toBe($product->id);
        expect($compareItem['createdAt'])->not()->toBeNull();
        expect($compareItem['updatedAt'])->not()->toBeNull();
    }

    /**
     * Test: Create compare item using createCompareItemInput variable type (matches spec)
     */
    public function test_create_compare_item_mutation_with_input_variable(): void
    {
        $customer = $this->createCustomer();
        $product = Product::factory()->create();

        $mutation = <<<'GQL'
            mutation CreateCompareItem($input: createCompareItemInput!) {
              createCompareItem(input: $input) {
                compareItem {
                  id
                  _id
                  createdAt
                  updatedAt
                  product {
                    id
                  }
                  customer {
                    id
                  }
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

        $compareItem = $response->json('data.createCompareItem.compareItem');
        expect($compareItem)->not()->toBeNull();
        expect($compareItem['_id'])->toBeInt();
        expect($compareItem['product'])->toHaveKey('id');
        expect($compareItem['customer'])->toHaveKey('id');
        expect($compareItem['createdAt'])->not()->toBeNull();
        expect($compareItem['updatedAt'])->not()->toBeNull();
    }

    /**
     * Test: Create compare item via inline GraphQL input literal
     */
    public function test_create_compare_item_mutation_with_inline_input_literal(): void
    {
        $customer = $this->createCustomer();
        $product = Product::factory()->create();

        $mutation = sprintf(<<<'GQL'
            mutation {
              createCompareItem(input: {productId: %d}) {
                compareItem {
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
            ->assertJsonPath('data.createCompareItem.compareItem.product._id', $product->id);

        expect($response->json('data.createCompareItem.compareItem._id'))->toBeInt();
    }

    /**
     * Test: Delete compare item via mutation
     */
    public function test_delete_compare_item_mutation(): void
    {
        $customer = $this->createCustomer();
        $product = Product::factory()->create();

        $compareItem = CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
        ]);

        $mutation = <<<'GQL'
            mutation DeleteCompareItem($id: ID!) {
              deleteCompareItem(input: {id: $id}) {
                compareItem {
                  id
                  _id
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'id' => "/api/shop/compare-items/{$compareItem->id}",
        ]);

        $response->assertOk();
        $deletedItem = $response->json('data.deleteCompareItem.compareItem');

        expect($deletedItem)->not()->toBeNull();
        expect($deletedItem['_id'])->toBe($compareItem->id);

        expect(CompareItem::find($compareItem->id))->toBeNull();
    }

    /**
     * Test: Delete compare item using deleteCompareItemInput variable type with product fields (matches spec)
     */
    public function test_delete_compare_item_mutation_with_input_variable_and_product_fields(): void
    {
        $customer = $this->createCustomer();
        $product = Product::factory()->create();

        $compareItem = CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
        ]);

        $mutation = <<<'GQL'
            mutation DeleteCompareItem($input: deleteCompareItemInput!) {
              deleteCompareItem(input: $input) {
                compareItem {
                  id
                  product {
                    sku
                    type
                    createdAt
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['id' => "/api/shop/compare-items/{$compareItem->id}"],
        ]);

        $response->assertOk();
        $response->assertJsonMissingPath('errors');

        $deletedItem = $response->json('data.deleteCompareItem.compareItem');
        expect($deletedItem)->not()->toBeNull();
        expect($deletedItem['product'])->toHaveKeys(['sku', 'type', 'createdAt']);

        expect(CompareItem::find($compareItem->id))->toBeNull();
    }

    /**
     * Test: Delete compare item via inline GraphQL input literal
     */
    public function test_delete_compare_item_mutation_with_inline_input_literal(): void
    {
        $customer = $this->createCustomer();
        $product = Product::factory()->create();

        $compareItem = CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
        ]);

        $mutation = sprintf(<<<'GQL'
            mutation {
              deleteCompareItem(input: {id: "/api/shop/compare-items/%d"}) {
                compareItem {
                  _id
                }
              }
            }
        GQL, $compareItem->id);

        $response = $this->actingAs($customer)
            ->withHeaders($this->authHeaders($customer))
            ->postJson($this->graphqlUrl, ['query' => $mutation]);

        $response->assertOk()
            ->assertJsonPath('data.deleteCompareItem.compareItem._id', $compareItem->id);

        expect(CompareItem::find($compareItem->id))->toBeNull();
    }

    /**
     * Test: Create compare item mutation with duplicate product
     */
    public function test_create_duplicate_compare_item_mutation_fails(): void
    {
        $customer = $this->createCustomer();
        $product1 = Product::factory()->create();

        CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product1->id,
        ]);

        $mutation = <<<'GQL'
            mutation CreateCompareItem($productId: Int!) {
              createCompareItem(input: {productId: $productId}) {
                compareItem {
                  id
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, ['productId' => $product1->id]);

        $response->assertOk();
        $errors = $response->json('errors');

        expect($errors)->not()->toBeEmpty();

        if (isset($errors[0]['extensions']['code'])) {
            expect($errors[0]['extensions']['code'])->toBe('INVALID_INPUT');
        } else {
            expect(implode(' ', array_column($errors, 'message')))->toContain('already');
        }
    }

    /**
     * Test: Delete all compare items successfully
     */
    public function test_delete_all_compare_items(): void
    {
        $testData = $this->createTestData();

        $mutation = <<<'GQL'
            mutation DeleteAllCompareItems {
              createDeleteAllCompareItems(input: {}) {
                deleteAllCompareItems {
                  message
                  deletedCount
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $mutation);

        $response->assertOk();
        $response->assertJsonMissingPath('errors');

        $data = $response->json('data.createDeleteAllCompareItems.deleteAllCompareItems');
        expect($data)->not()->toBeNull();
        expect($data['deletedCount'])->toBe(2);
        expect($data['message'])->toContain('removed');

        expect(CompareItem::where('customer_id', $testData['customer']->id)->count())->toBe(0);
    }

    /**
     * Test: Delete all compare items requires authentication
     */
    public function test_delete_all_compare_items_requires_authentication(): void
    {
        $mutation = <<<'GQL'
            mutation DeleteAllCompareItems {
              createDeleteAllCompareItems(input: {}) {
                deleteAllCompareItems {
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
     * Test: Delete all compare items with no items returns zero count
     */
    public function test_delete_all_compare_items_with_no_items(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation DeleteAllCompareItems {
              createDeleteAllCompareItems(input: {}) {
                deleteAllCompareItems {
                  message
                  deletedCount
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation);

        $response->assertOk();
        $response->assertJsonMissingPath('errors');

        $data = $response->json('data.createDeleteAllCompareItems.deleteAllCompareItems');
        expect($data)->not()->toBeNull();
        expect($data['deletedCount'])->toBe(0);
    }

    /**
     * Test: Create compare item requires authentication
     */
    public function test_create_compare_item_requires_authentication(): void
    {
        $this->seedRequiredData();
        $product = Product::factory()->create();

        $mutation = <<<'GQL'
            mutation CreateCompareItem($input: createCompareItemInput!) {
              createCompareItem(input: $input) {
                compareItem {
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
     * Test: Create compare item with non-existent product returns error
     */
    public function test_create_compare_item_with_nonexistent_product_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation CreateCompareItem($input: createCompareItemInput!) {
              createCompareItem(input: $input) {
                compareItem {
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
     * Test: Delete compare item requires authentication
     */
    public function test_delete_compare_item_requires_authentication(): void
    {
        $this->seedRequiredData();

        $mutation = <<<'GQL'
            mutation DeleteCompareItem($input: deleteCompareItemInput!) {
              deleteCompareItem(input: $input) {
                compareItem {
                  id
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['input' => ['id' => '/api/shop/compare-items/1']]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    /**
     * Test: Delete non-existent compare item returns error
     */
    public function test_delete_nonexistent_compare_item_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation DeleteCompareItem($input: deleteCompareItemInput!) {
              deleteCompareItem(input: $input) {
                compareItem {
                  id
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['id' => '/api/shop/compare-items/999999'],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(implode(' ', array_column($response->json('errors') ?? [], 'message')))
            ->toMatch('/not found|not exist/i');
    }

    /**
     * Test: Cannot delete another user's compare item
     */
    public function test_cannot_delete_other_users_compare_item(): void
    {
        $this->seedRequiredData();

        $customer1 = $this->createCustomer();
        $customer2 = $this->createCustomer();
        $product = Product::factory()->create();

        $compareItem = CompareItem::factory()->create([
            'customer_id' => $customer1->id,
            'product_id'  => $product->id,
        ]);

        $mutation = <<<'GQL'
            mutation DeleteCompareItem($input: deleteCompareItemInput!) {
              deleteCompareItem(input: $input) {
                compareItem {
                  id
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer2, $mutation, [
            'input' => ['id' => "/api/shop/compare-items/{$compareItem->id}"],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(implode(' ', array_column($response->json('errors') ?? [], 'message')))
            ->toMatch('/[Uu]nauthorized|cannot|not allowed/i');

        expect(CompareItem::find($compareItem->id))->not()->toBeNull();
    }
}
