<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class ProductSearchFilterTest extends GraphQLTestCase
{
    // ============================================================================
    // Search With Query Variables
    // ============================================================================

    /**
     * Search with query string and all sort variables bound.
     * Matches spec: products(query: $query, sortKey: $sortKey, reverse: $reverse, first: $first).
     */
    public function test_search_products_with_query_variables(): void
    {
        $query = <<<'GQL'
            query getProductsSearchFilter($query: String, $sortKey: String, $reverse: Boolean, $first: Int) {
              products(query: $query, sortKey: $sortKey, reverse: $reverse, first: $first) {
                edges {
                  node {
                    id
                    name
                    sku
                    price
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'query' => 'product',
            'sortKey' => 'TITLE',
            'reverse' => false,
            'first' => 10,
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('edges', $data);

        if (! empty($data['edges'])) {
            $node = $data['edges'][0]['node'];
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('name', $node);
            $this->assertArrayHasKey('sku', $node);
            $this->assertArrayHasKey('price', $node);
        }
    }

    /**
     * Empty query string returns products (not an error).
     */
    public function test_search_with_empty_query_returns_products(): void
    {
        $query = <<<'GQL'
            query getProductsSearchFilter($query: String, $first: Int) {
              products(query: $query, first: $first) {
                edges {
                  node {
                    id
                    name
                    sku
                    price
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'query' => '',
            'first' => 10,
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));
        $this->assertNotNull($response->json('data.products'));
        $this->assertArrayHasKey('edges', $response->json('data.products'));
    }

    /**
     * Search term that matches no products returns empty edges (not an error).
     */
    public function test_search_with_nonexistent_term_returns_empty_edges(): void
    {
        $query = <<<'GQL'
            query getProductsSearchFilter($query: String) {
              products(query: $query) {
                edges {
                  node {
                    id
                    name
                    sku
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'query' => 'XYZZY_NO_MATCH_PRODUCT_12345_ABCDE',
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $edges = $response->json('data.products.edges') ?? [];
        $this->assertIsArray($edges);
        $this->assertEmpty($edges, 'Non-matching search should return empty edges');
    }

    // ============================================================================
    // Category Filter With Pagination
    // ============================================================================

    /**
     * Category ID filter with full spec fields, pageInfo, and totalCount.
     * Matches spec: products(filter: "{\"category_id\": \"22\"}", first: 2, after: "Mg==").
     */
    public function test_filter_by_category_id_with_pagination_full_fields(): void
    {
        $query = <<<'GQL'
            query getProducts($filter: String, $first: Int, $after: String) {
              products(filter: $filter, first: $first, after: $after) {
                edges {
                  node {
                    id
                    sku
                    price
                    name
                    urlKey
                    baseImageUrl
                    description
                    shortDescription
                    specialPrice
                  }
                }
                pageInfo {
                  hasNextPage
                  hasPreviousPage
                  startCursor
                  endCursor
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'filter' => '{"category_id": "1"}',
            'first' => 2,
            'after' => null,
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertArrayHasKey('pageInfo', $data);

        $pageInfo = $data['pageInfo'];
        $this->assertArrayHasKey('hasNextPage', $pageInfo);
        $this->assertArrayHasKey('hasPreviousPage', $pageInfo);
        $this->assertArrayHasKey('startCursor', $pageInfo);
        $this->assertArrayHasKey('endCursor', $pageInfo);

        if (! empty($data['edges'])) {
            $node = $data['edges'][0]['node'];
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('sku', $node);
            $this->assertArrayHasKey('price', $node);
            $this->assertArrayHasKey('name', $node);
            $this->assertArrayHasKey('urlKey', $node);
            $this->assertArrayHasKey('baseImageUrl', $node);
            $this->assertArrayHasKey('description', $node);
            $this->assertArrayHasKey('shortDescription', $node);
            $this->assertArrayHasKey('specialPrice', $node);
        }
    }

    /**
     * Non-existent category ID returns empty results (not an error).
     */
    public function test_filter_by_nonexistent_category_returns_empty(): void
    {
        $query = <<<'GQL'
            query getProducts($filter: String) {
              products(filter: $filter) {
                edges {
                  node {
                    id
                    sku
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'filter' => '{"category_id": "9999999"}',
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertNotNull($data);
        $this->assertSame(0, (int) ($data['totalCount'] ?? 0));
        $this->assertEmpty($data['edges'] ?? []);
    }

    // ============================================================================
    // Type Filter
    // ============================================================================

    /**
     * Filter by product type includes totalCount.
     * Matches spec: products(filter: "{\"type\": \"configurable\"}").
     */
    public function test_filter_by_product_type_with_total_count(): void
    {
        $query = <<<'GQL'
            query getProducts($filter: String) {
              products(filter: $filter) {
                edges {
                  node {
                    id
                    sku
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'filter' => '{"type": "configurable"}',
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);
    }

    // ============================================================================
    // Attribute Filters
    // ============================================================================

    /**
     * Filter by color attribute returns valid response structure.
     * Matches spec: products(filter: "{\"color\": \"3\"}").
     */
    public function test_filter_by_color_attribute(): void
    {
        $filter = '{"color": "3"}';

        $colorAttribute = Attribute::where('code', 'color')->first();
        if ($colorAttribute) {
            $option = $colorAttribute->options()->first();
            if ($option) {
                $product = $this->createBaseProduct('simple', [
                    'sku' => 'TEST-COLOR-'.uniqid(),
                ]);
                $this->ensureInventory($product, 10);
                $this->upsertProductAttributeValue($product->id, 'color', $option->id, null, 'default');
                $filter = "{\"color\": \"{$option->id}\"}";
            }
        }

        $query = <<<'GQL'
            query getProducts($filter: String) {
              products(filter: $filter) {
                edges {
                  node {
                    id
                    sku
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, ['filter' => $filter]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);
    }

    /**
     * A sku or type filter combined with an attribute filter reads the product's own columns, not the variant join's.
     */
    public function test_filter_by_sku_and_type_together_with_an_attribute(): void
    {
        $option = $this->colorOption();

        $product = $this->createBaseProduct('simple', [
            'sku' => 'TEST-SKU-COLOR-'.uniqid(),
        ]);
        $this->ensureInventory($product, 10);
        $this->upsertProductAttributeValue($product->id, 'color', $option->id, null, 'default');

        $response = $this->graphQL($this->productIdsQuery(), [
            'filter' => json_encode([
                'sku' => $product->sku,
                'type' => 'simple',
                'color' => (string) $option->id,
            ]),
        ]);

        expect($response->json('errors'))->toBeNull();
        expect($this->nodeIds($response))->toBe([$product->id]);
    }

    /**
     * A non-numeric term on a select attribute matches no product and never reaches the integer column.
     */
    public function test_filter_by_a_select_attribute_with_a_non_numeric_term_matches_nothing(): void
    {
        $option = $this->colorOption();

        $product = $this->createBaseProduct('simple', [
            'sku' => 'TEST-COLOR-TERM-'.uniqid(),
        ]);
        $this->ensureInventory($product, 10);
        $this->upsertProductAttributeValue($product->id, 'color', $option->id, null, 'default');

        $bindings = [];

        DB::listen(function ($query) use (&$bindings) {
            $bindings = array_merge($bindings, $query->bindings);
        });

        foreach (['red-term', 'red-term,blue-term'] as $term) {
            $response = $this->graphQL($this->productIdsQuery(), [
                'filter' => json_encode(['color' => $term]),
            ]);

            expect($response->json('errors'))->toBeNull();
            expect($this->nodeIds($response))->toBe([]);
        }

        expect($bindings)->not->toContain('red-term');
        expect($bindings)->not->toContain('blue-term');
    }

    /**
     * Filter by multiple attributes (color + size + brand).
     * Matches spec: products(filter: "{\"color\": \"5\", \"size\": \"1\", \"brand\": \"5\"}", first: 10).
     */
    public function test_filter_by_multiple_attributes(): void
    {
        $query = <<<'GQL'
            query getProducts($filter: String, $first: Int) {
              products(filter: $filter, first: $first) {
                edges {
                  node {
                    id
                    sku
                    name
                    price
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'filter' => '{"color": "5", "size": "1", "brand": "5"}',
            'first' => 10,
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);
    }

    // ============================================================================
    // Sort With TotalCount
    // ============================================================================

    /**
     * Sort A-Z by title returns edges and totalCount.
     * Matches spec: products(sortKey: "TITLE", reverse: false, first: 5).
     */
    public function test_sort_az_with_total_count(): void
    {
        $query = <<<'GQL'
            query getProducts {
              products(sortKey: "TITLE", reverse: false, first: 5) {
                edges {
                  node {
                    id
                    name
                    sku
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);
        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);
    }

    /**
     * Sort Z-A by title returns edges and totalCount.
     * Matches spec: products(sortKey: "TITLE", reverse: true, first: 5).
     */
    public function test_sort_za_with_total_count(): void
    {
        $query = <<<'GQL'
            query getProducts {
              products(sortKey: "TITLE", reverse: true, first: 5) {
                edges {
                  node {
                    id
                    name
                    sku
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);
        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);
    }

    /**
     * Sort newest first returns edges and totalCount.
     * Matches spec: products(sortKey: "CREATED_AT", reverse: true, first: 10).
     */
    public function test_sort_newest_first_with_total_count(): void
    {
        $query = <<<'GQL'
            query getProducts {
              products(sortKey: "CREATED_AT", reverse: true, first: 10) {
                edges {
                  node {
                    id
                    name
                    sku
                    price
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);
        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);
    }

    /**
     * Sort oldest first returns edges and totalCount.
     * Matches spec: products(sortKey: "CREATED_AT", reverse: false, first: 10).
     */
    public function test_sort_oldest_first_with_total_count(): void
    {
        $query = <<<'GQL'
            query getProducts {
              products(sortKey: "CREATED_AT", reverse: false, first: 10) {
                edges {
                  node {
                    id
                    name
                    sku
                    price
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);
        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);
    }

    /**
     * Sort cheapest first returns edges with minimumPrice, price, specialPrice and totalCount.
     * Matches spec: products(reverse: false, sortKey: "PRICE", first: 10).
     */
    public function test_sort_cheapest_first_with_total_count(): void
    {
        $query = <<<'GQL'
            query getProductsSorted {
              products(reverse: false, sortKey: "PRICE", first: 10) {
                edges {
                  node {
                    id
                    name
                    sku
                    minimumPrice
                    price
                    specialPrice
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);
        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);

        foreach ($data['edges'] as $edge) {
            $node = $edge['node'];
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('name', $node);
            $this->assertArrayHasKey('sku', $node);
            $this->assertArrayHasKey('minimumPrice', $node);
            $this->assertArrayHasKey('price', $node);
            $this->assertArrayHasKey('specialPrice', $node);
        }

        $prices = array_filter(
            array_column(array_column($data['edges'], 'node'), 'minimumPrice'),
            fn ($p) => $p !== null
        );
        $prices = array_values($prices);
        for ($i = 0; $i < count($prices) - 1; $i++) {
            $this->assertLessThanOrEqual(
                (float) $prices[$i + 1],
                (float) $prices[$i],
                'minimumPrice should be non-decreasing when sorted cheapest first'
            );
        }
    }

    /**
     * Sort most expensive first returns edges with minimumPrice, price, specialPrice and totalCount.
     * Matches spec: products(reverse: true, sortKey: "PRICE", first: 10).
     */
    public function test_sort_most_expensive_first_with_total_count(): void
    {
        $query = <<<'GQL'
            query getProductsSorted {
              products(reverse: true, sortKey: "PRICE", first: 10) {
                edges {
                  node {
                    id
                    name
                    sku
                    minimumPrice
                    price
                    specialPrice
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);
        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);

        foreach ($data['edges'] as $edge) {
            $node = $edge['node'];
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('name', $node);
            $this->assertArrayHasKey('sku', $node);
            $this->assertArrayHasKey('minimumPrice', $node);
            $this->assertArrayHasKey('price', $node);
            $this->assertArrayHasKey('specialPrice', $node);
        }

        $prices = array_filter(
            array_column(array_column($data['edges'], 'node'), 'minimumPrice'),
            fn ($p) => $p !== null
        );
        $prices = array_values($prices);
        for ($i = 0; $i < count($prices) - 1; $i++) {
            $this->assertGreaterThanOrEqual(
                (float) $prices[$i + 1],
                (float) $prices[$i],
                'minimumPrice should be non-increasing when sorted most expensive first'
            );
        }
    }

    // ============================================================================
    // New / Featured / Brand Filters
    // ============================================================================

    /**
     * New products filter (new=1) returns products marked as new.
     * Matches spec: products(filter: "{\"new\": \"1\"}", sortKey: "CREATED_AT", reverse: false, first: 10).
     */
    public function test_filter_new_products(): void
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'TEST-NEW-'.uniqid()]);
        $this->upsertProductAttributeValue($product->id, 'price', 15.0, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'new', 1, null, 'default');
        $this->ensureInventory($product, 10);

        $query = <<<'GQL'
            query getProducts($filter: String, $first: Int) {
              products(filter: $filter, sortKey: "CREATED_AT", reverse: false, first: $first) {
                edges {
                  node {
                    id
                    name
                    sku
                    price
                    urlKey
                    baseImageUrl
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'filter' => '{"new": "1"}',
            'first' => 10,
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);

        if (! empty($data['edges'])) {
            $node = $data['edges'][0]['node'];
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('name', $node);
            $this->assertArrayHasKey('sku', $node);
            $this->assertArrayHasKey('price', $node);
            $this->assertArrayHasKey('urlKey', $node);
            $this->assertArrayHasKey('baseImageUrl', $node);
        }
    }

    /**
     * Featured products filter (featured=1) returns products marked as featured.
     * Matches spec: products(filter: "{\"featured\": \"1\"}", sortKey: "CREATED_AT", reverse: true, first: 12).
     */
    public function test_filter_featured_products(): void
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'TEST-FEATURED-'.uniqid()]);
        $this->upsertProductAttributeValue($product->id, 'price', 20.0, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'featured', 1, null, 'default');
        $this->ensureInventory($product, 10);

        $query = <<<'GQL'
            query getProducts($filter: String, $first: Int) {
              products(filter: $filter, sortKey: "CREATED_AT", reverse: true, first: $first) {
                edges {
                  node {
                    id
                    name
                    sku
                    price
                    urlKey
                    baseImageUrl
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'filter' => '{"featured": "1"}',
            'first' => 12,
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);

        if (! empty($data['edges'])) {
            $node = $data['edges'][0]['node'];
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('urlKey', $node);
            $this->assertArrayHasKey('baseImageUrl', $node);
        }
    }

    /**
     * Filter by brand attribute returns valid response.
     * Matches spec: products(filter: "{\"brand\": \"25\"}", sortKey: "CREATED_AT", reverse: true, first: 12).
     */
    public function test_filter_by_brand(): void
    {
        $filter = '{"brand": "25"}';

        $brandAttribute = Attribute::where('code', 'brand')->first();
        if ($brandAttribute) {
            $option = $brandAttribute->options()->first();
            if ($option) {
                $product = $this->createBaseProduct('simple', ['sku' => 'TEST-BRAND-'.uniqid()]);
                $this->ensureInventory($product, 10);
                $this->upsertProductAttributeValue($product->id, 'brand', $option->id, null, 'default');
                $filter = "{\"brand\": \"{$option->id}\"}";
            }
        }

        $query = <<<'GQL'
            query getProducts($filter: String, $first: Int) {
              products(filter: $filter, sortKey: "CREATED_AT", reverse: true, first: $first) {
                edges {
                  node {
                    id
                    name
                    sku
                    price
                    urlKey
                    baseImageUrl
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'filter' => $filter,
            'first' => 12,
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);

        if (! empty($data['edges'])) {
            $node = $data['edges'][0]['node'];
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('urlKey', $node);
            $this->assertArrayHasKey('baseImageUrl', $node);
        }
    }

    // ============================================================================
    // Edge Cases
    // ============================================================================

    /**
     * Cursor-based pagination: second page uses endCursor from first page.
     */
    public function test_pagination_second_page_with_cursor(): void
    {
        $firstPageQuery = <<<'GQL'
            query getProducts {
              products(first: 1) {
                edges {
                  node { id sku }
                }
                pageInfo {
                  hasNextPage
                  endCursor
                }
                totalCount
              }
            }
        GQL;

        $firstResponse = $this->graphQL($firstPageQuery);
        $firstResponse->assertSuccessful();
        $this->assertNull($firstResponse->json('errors'));

        $firstData = $firstResponse->json('data.products');
        $this->assertNotNull($firstData);

        if (! ($firstData['pageInfo']['hasNextPage'] ?? false)) {
            $this->markTestSkipped('Not enough products to test cursor pagination.');
        }

        $endCursor = $firstData['pageInfo']['endCursor'];
        $firstId = $firstData['edges'][0]['node']['id'] ?? null;

        $secondPageQuery = <<<'GQL'
            query getProducts($after: String) {
              products(first: 1, after: $after) {
                edges {
                  node { id sku }
                }
                pageInfo {
                  hasPreviousPage
                  startCursor
                }
                totalCount
              }
            }
        GQL;

        $secondResponse = $this->graphQL($secondPageQuery, ['after' => $endCursor]);
        $secondResponse->assertSuccessful();
        $this->assertNull($secondResponse->json('errors'));

        $secondData = $secondResponse->json('data.products');
        $this->assertNotNull($secondData);
        $this->assertNotEmpty($secondData['edges'], 'Second page should have results');

        $secondId = $secondData['edges'][0]['node']['id'] ?? null;
        $this->assertNotSame($firstId, $secondId, 'Second page should return a different product');
    }

    /**
     * Invalid cursor returns error or empty results — not a 500.
     */
    public function test_pagination_with_invalid_cursor_does_not_500(): void
    {
        $query = <<<'GQL'
            query getProducts($after: String) {
              products(first: 5, after: $after) {
                edges {
                  node { id sku }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, ['after' => 'INVALID_CURSOR_XYZ_!@#']);

        $response->assertSuccessful();

        $errors = $response->json('errors');
        $data = $response->json('data.products');
        $this->assertTrue(
            ! empty($errors) || $data !== null && empty($data['edges']),
            'Invalid cursor should return error or empty results, not 500'
        );
    }

    /**
     * Invalid JSON in filter returns a GraphQL error — not a 500.
     */
    public function test_filter_with_invalid_json_returns_error_not_500(): void
    {
        $query = <<<'GQL'
            query getProducts($filter: String) {
              products(filter: $filter) {
                edges {
                  node { id sku }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, ['filter' => '{invalid_json_no_quotes']);

        $response->assertSuccessful();

        $errors = $response->json('errors');
        $data = $response->json('data.products');
        $this->assertTrue(
            ! empty($errors) || $data !== null,
            'Invalid JSON filter must not cause a 500'
        );
    }

    /**
     * A first or last of zero or below returns an empty page with the total still counted.
     */
    public function test_first_and_last_zero_or_negative_return_an_empty_page(): void
    {
        $this->createBaseProduct('simple', ['sku' => 'TEST-EMPTY-PAGE-'.uniqid()]);

        foreach (['first: 0', 'first: -1', 'last: 0', 'last: -1'] as $window) {
            $response = $this->graphQL("query { products({$window}) { totalCount edges { node { id } } } }");

            $response->assertSuccessful();

            expect($response->json('errors'))->toBeNull();
            expect($response->json('data.products.edges'))->toBe([]);
            expect($response->json('data.products.totalCount'))->toBeGreaterThan(0);
        }
    }

    /**
     * Last N without before returns the last N products of the set, and before stops at the cursor.
     */
    public function test_last_returns_the_final_rows_and_before_stops_at_the_cursor(): void
    {
        foreach (range(1, 3) as $index) {
            $this->createBaseProduct('simple', ['sku' => 'TEST-LAST-'.$index.'-'.uniqid()]);
        }

        $all = $this->graphQL('query { products(first: 50) { totalCount edges { cursor node { _id } } } }');

        expect($all->json('errors'))->toBeNull();

        $edges = $all->json('data.products.edges');
        $total = $all->json('data.products.totalCount');

        expect($total)->toBeGreaterThanOrEqual(3)->toBeLessThanOrEqual(50);

        $ids = array_map(fn ($edge) => (int) $edge['node']['_id'], $edges);

        $last = $this->graphQL('query { products(last: 2) { totalCount edges { cursor node { _id } } } }');

        expect($last->json('errors'))->toBeNull();
        expect($this->nodeIds($last))->toBe(array_slice($ids, -2));
        expect(array_column($last->json('data.products.edges'), 'cursor'))->toBe(array_column(array_slice($edges, -2), 'cursor'));

        $before = $this->graphQL('query($before: String) { products(last: 5, before: $before) { edges { node { _id } } } }', [
            'before' => $edges[1]['cursor'],
        ]);

        expect($before->json('errors'))->toBeNull();
        expect($this->nodeIds($before))->toBe([$ids[0]]);
    }

    /**
     * A huge first is capped at the configured maximum page size.
     */
    public function test_huge_first_is_capped(): void
    {
        $response = $this->graphQL('query { products(first: 100000) { edges { node { id } } } }');

        $response->assertSuccessful();

        expect(count($response->json('data.products.edges') ?? []))
            ->toBeLessThanOrEqual((int) config('api-platform.defaults.pagination_maximum_items_per_page', 50));
    }

    /**
     * Combining a type filter with a price sort returns correctly structured results.
     */
    public function test_combined_type_filter_and_price_sort(): void
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'TEST-COMBO-'.uniqid()]);
        $this->upsertProductAttributeValue($product->id, 'price', 30.0, null, 'default');
        $this->ensureInventory($product, 10);

        $query = <<<'GQL'
            query getProducts($filter: String, $first: Int) {
              products(filter: $filter, sortKey: "PRICE", reverse: false, first: $first) {
                edges {
                  node {
                    id
                    name
                    sku
                    minimumPrice
                    price
                    specialPrice
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'filter' => '{"type": "simple"}',
            'first' => 10,
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);

        if (! empty($data['edges'])) {
            $node = $data['edges'][0]['node'];
            $this->assertArrayHasKey('minimumPrice', $node);
            $this->assertArrayHasKey('price', $node);
            $this->assertArrayHasKey('specialPrice', $node);
        }
    }

    /**
     * Combining a new=1 filter with a title sort (A-Z) returns valid structure.
     */
    public function test_combined_new_filter_and_title_sort(): void
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'TEST-NEW-SORT-'.uniqid()]);
        $this->upsertProductAttributeValue($product->id, 'price', 10.0, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'new', 1, null, 'default');
        $this->ensureInventory($product, 10);

        $query = <<<'GQL'
            query getProducts($filter: String) {
              products(filter: $filter, sortKey: "TITLE", reverse: false, first: 10) {
                edges {
                  node {
                    id
                    name
                    sku
                    price
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, ['filter' => '{"new": "1"}']);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
    }

    /**
     * Test unknown filter keys are ignored.
     */
    public function test_unknown_filter_keys_are_ignored(): void
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'TEST-UNKNOWN-'.uniqid()]);

        $response = $this->graphQL($this->productIdsQuery(), [
            'filter' => json_encode([
                'sku' => $product->sku,
                'utm_source' => 'newsletter',
            ]),
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));
        $this->assertContains($product->id, $this->nodeIds($response));
    }

    /**
     * Test price filter reads the price index.
     */
    public function test_price_filter_reads_the_price_index(): void
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'TEST-PRICEIDX-'.uniqid()]);

        DB::table('product_price_indices')->updateOrInsert(
            [
                'product_id' => $product->id,
                'customer_group_id' => core()->getGuestCustomerGroup()->id,
                'channel_id' => core()->getCurrentChannel()->id,
            ], [
                'min_price' => 500,
                'regular_min_price' => 500,
                'max_price' => 500,
                'regular_max_price' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $inRange = $this->graphQL($this->productIdsQuery(), [
            'filter' => json_encode([
                'sku' => $product->sku,
                'price_from' => 400,
                'price_to' => 600,
            ]),
        ]);

        $inRange->assertSuccessful();
        $this->assertNull($inRange->json('errors'));
        $this->assertContains($product->id, $this->nodeIds($inRange));

        $attributeRange = $this->graphQL($this->productIdsQuery(), [
            'filter' => json_encode([
                'sku' => $product->sku,
                'price' => '5,15',
            ]),
        ]);

        $attributeRange->assertSuccessful();
        $this->assertNull($attributeRange->json('errors'));
        $this->assertNotContains($product->id, $this->nodeIds($attributeRange));
    }

    /**
     * Sorting by name with a channel keeps the name order instead of collapsing to NULL.
     */
    public function test_name_sort_with_channel_is_ordered(): void
    {
        $channelCode = core()->getCurrentChannel()->code;

        $response = $this->graphQL(
            'query getProducts($channel: String) { products(sortKey: "NAME", reverse: false, first: 50, channel: $channel) { edges { node { name } } } }',
            ['channel' => $channelCode]
        );

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $names = array_filter(array_column(array_column($response->json('data.products.edges') ?? [], 'node'), 'name'));
        $sorted = $names;
        sort($sorted, SORT_FLAG_CASE | SORT_STRING);

        expect(array_values($names))->toBe(array_values($sorted));
    }

    /**
     * Product ids query.
     */
    private function productIdsQuery(): string
    {
        return <<<'GQL'
            query getProducts($filter: String) {
              products(filter: $filter, first: 50) {
                edges {
                  node {
                    _id
                  }
                }
              }
            }
        GQL;
    }

    /**
     * The first option of the color attribute, skipping the test when the store has none.
     */
    private function colorOption(): object
    {
        $option = Attribute::query()->where('code', 'color')->first()?->options()->first();

        if (! $option) {
            $this->markTestSkipped('The color attribute has no options. Run Bagisto seeders for attributes.');
        }

        return $option;
    }

    /**
     * Node ids.
     */
    private function nodeIds($response): array
    {
        return collect($response->json('data.products.edges') ?? [])
            ->map(fn ($edge) => (int) $edge['node']['_id'])
            ->all();
    }
}
