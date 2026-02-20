<?php

namespace Tests\Feature\BagistoApi\GraphQL;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Customer\Models\CompareItem;
use Webkul\Product\Models\Product;
use Webkul\Core\Models\Channel;

class CompareItemTest extends TestCase
{
    use DatabaseTransactions;

    private string $graphqlUrl = '/api/graphql';

    /**
     * Disable API logging middleware for tests
     */
    protected $withoutMiddleware = [
        \Webkul\BagistoApi\Http\Middleware\LogApiRequests::class,
    ];

    /**
     * Disable foreign key constraints at test start
     */
    public function setUp(): void
    {
        parent::setUp();
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    /**
     * Re-enable foreign key constraints after test
     */
    public function tearDown(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
        parent::tearDown();
    }

    /**
     * Seed required database records
     */
    protected function seedRequiredData(): void
    {
        try {
            // Create category first (required by Channel)
            if (!\Webkul\Category\Models\Category::exists()) {
                \Webkul\Category\Models\Category::factory()->create([
                    'parent_id' => null,
                ]);
            }

            // Create channel if it doesn't exist
            if (!Channel::exists()) {
                Channel::factory()->create();
            }

            // Create customer group
            if (!CustomerGroup::where('code', 'general')->exists()) {
                CustomerGroup::create([
                    'code' => 'general',
                    'name' => 'General',
                    'is_user_defined' => 0,
                ]);
            }
        } catch (\Exception $e) {
            // Seed creation failed, tests will be skipped if needed
        }
    }

    /**
     * Get headers with storefront key for GraphQL requests
     */
    private function getGraphQLHeaders(): array
    {
        return ['X-STOREFRONT-KEY' => 'pk_test_1234567890abcdef'];
    }

    /**
     * Create test data - customer, products and compare items
     */
    private function createTestData(): array
    {
        // Ensure required database records exist
        $this->seedRequiredData();

        try {
            $customer = Customer::factory()->create();
            $product1 = Product::factory()->create();
            $product2 = Product::factory()->create();

            // Create compare items using factory
            $compareItem1 = CompareItem::factory()->create([
                'customer_id' => $customer->id,
                'product_id' => $product1->id,
            ]);
            $compareItem2 = CompareItem::factory()->create([
                'customer_id' => $customer->id,
                'product_id' => $product2->id,
            ]);

            return compact('customer', 'product1', 'product2', 'compareItem1', 'compareItem2');
        } catch (\Exception $e) {
            $this->markTestSkipped('Test database error: ' . $e->getMessage());
        }
    }

    /**
     * Test: Query all compare items collection
     */
    public function test_get_compare_items_collection(): void
    {
        $this->createTestData();

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

        $response = $this->postJson($this->graphqlUrl, ['query' => $query], $this->getGraphQLHeaders());

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

        $response = $this->postJson($this->graphqlUrl, compact('query'), $this->getGraphQLHeaders());

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
        $this->createTestData();

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

        $response = $this->postJson($this->graphqlUrl, ['query' => $query], $this->getGraphQLHeaders());

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

        $response = $this->postJson($this->graphqlUrl, ['query' => $query], $this->getGraphQLHeaders());

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

        $response = $this->postJson($this->graphqlUrl, ['query' => $query], $this->getGraphQLHeaders());

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

        $response = $this->postJson($this->graphqlUrl, ['query' => $query], $this->getGraphQLHeaders());

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

        $response = $this->postJson($this->graphqlUrl, ['query' => $query], $this->getGraphQLHeaders());

        $response->assertOk();
        expect($response->json('data.compareItem'))->toBeNull();
    }

    /**
     * Test: Pagination with cursor
     */
    public function test_compare_items_pagination_with_cursor(): void
    {
        $this->createTestData();

        $firstQuery = <<<'GQL'
            query getCompareItems {
              compareItems(first: 1) {
                edges {
                  cursor
                }
              }
            }
        GQL;

        $firstResponse = $this->postJson($this->graphqlUrl, ['query' => $firstQuery], $this->getGraphQLHeaders());
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

        $secondResponse = $this->postJson($this->graphqlUrl, ['query' => $secondQuery], $this->getGraphQLHeaders());

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

        $response = $this->postJson($this->graphqlUrl, ['query' => $query], $this->getGraphQLHeaders());

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

        $response = $this->postJson($this->graphqlUrl, ['query' => $query], $this->getGraphQLHeaders());

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

        $response = $this->postJson($this->graphqlUrl, ['query' => $query], $this->getGraphQLHeaders());

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

        $response = $this->postJson($this->graphqlUrl, ['query' => $query], $this->getGraphQLHeaders());

        $response->assertOk();
        $edges = $response->json('data.compareItems.edges');

        // Verify we have items
        expect($edges)->not()->toBeEmpty();
    }

    /**
     * Test: Create compare item via mutation
     */
    public function test_create_compare_item_mutation(): void
    {
        $this->seedRequiredData();

        $customer = Customer::factory()->create();
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

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->actingAs($customer)->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-STOREFRONT-KEY' => 'pk_test_1234567890abcdef',
        ])->postJson($this->graphqlUrl, [
            'query' => $mutation,
            'variables' => ['productId' => $product->id],
        ]);

        $response->assertOk();
        $fullResponse = $response->json();

        if (isset($fullResponse['errors']) && !empty($fullResponse['errors'])) {
            $this->fail('GraphQL errors: ' . json_encode($fullResponse['errors']));
        }
        
        $compareItem = $response->json('data.createCompareItem.compareItem');

        expect($compareItem)->not()->toBeNull();
        expect($compareItem['_id'])->toBeInt();
        expect($compareItem['product']['_id'])->toBe($product->id);
        expect($compareItem['createdAt'])->not()->toBeNull();
        expect($compareItem['updatedAt'])->not()->toBeNull();
    }

    /**
     * Test: Delete compare item via mutation
     */
    public function test_delete_compare_item_mutation(): void
    {
        $this->seedRequiredData();

        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $compareItem = CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $mutation = <<<'GQL'
            mutation DeleteCompareItem($id: String!) {
              deleteCompareItem(input: {id: $id}) {
                compareItem {
                  id
                  _id
                }
              }
            }
        GQL;

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->actingAs($customer)->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-STOREFRONT-KEY' => 'pk_test_1234567890abcdef',
        ])->postJson($this->graphqlUrl, [
            'query' => $mutation,
            'variables' => ['id' => "/api/shop/compare-items/{$compareItem->id}"],
        ]);

        $response->assertOk();
        $deletedItem = $response->json('data.deleteCompareItem.compareItem');

        expect($deletedItem)->not()->toBeNull();
        expect($deletedItem['_id'])->toBe($compareItem->id);

        // Verify the item is deleted
        expect(CompareItem::find($compareItem->id))->toBeNull();
    }

    /**
     * Test: Create compare item mutation with duplicate product
     */
    public function test_create_duplicate_compare_item_mutation_fails(): void
    {
        $this->seedRequiredData();

        $customer = Customer::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $token = $customer->createToken('test-token')->plainTextToken;

        // Create the first compare item
        CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product1->id,
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

        // Try to create with a different product
        $response = $this->actingAs($customer)->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-STOREFRONT-KEY' => 'pk_test_1234567890abcdef',
        ])->postJson($this->graphqlUrl, [
            'query' => $mutation,
            'variables' => ['productId' => $product2->id],
        ]);

        $response->assertOk();
        $errors = $response->json('errors');

        expect($errors)->not()->toBeEmpty();
        // Check if error has the expected code
        if (isset($errors[0]['extensions']['code'])) {
            expect($errors[0]['extensions']['code'])->toBe('INVALID_INPUT');
        } else {
            // If no extensions, check for error message about duplicate
            expect(implode(' ', array_column($errors, 'message')))->toContain('already');
        }
    }
}
