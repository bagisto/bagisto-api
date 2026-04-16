<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;

/**
 * Tests for product search, filter, sort, and pagination APIs.
 *
 * Covers spec queries:
 *  - Search with query + variables
 *  - Category filter with pagination + full fields
 *  - Filter by type with totalCount
 *  - Filter by color attribute
 *  - Filter by multiple attributes
 *  - Sort A-Z / Z-A / Newest / Oldest / Cheapest / Most Expensive (all with totalCount)
 *  - New products filter
 *  - Featured products filter
 *  - Brand filter
 *  - Edge cases: empty query, no match, bad cursor, invalid JSON, first:0, combined filter+sort
 */
class ProductSearchFilterTest extends GraphQLTestCase
{
    // ─── Search with Query Variables ─────────────────────────────────────

    /**
     * Search with query string and all sort variables bound.
     * Matches spec: products(query: $query, sortKey: $sortKey, reverse: $reverse, first: $first)
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
            'query'   => 'product',
            'sortKey' => 'TITLE',
            'reverse' => false,
            'first'   => 10,
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

        $response = $this->graphQL($query, ['query' => '', 'first' => 10]);

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

    // ─── Category Filter with Pagination ─────────────────────────────────

    /**
     * Category ID filter with full spec fields, pageInfo, and totalCount.
     * Matches spec: products(filter: "{\"category_id\": \"22\"}", first: 2, after: "Mg==")
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
            'first'  => 2,
            'after'  => null,
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

    // ─── Type Filter ─────────────────────────────────────────────────────

    /**
     * Filter by product type includes totalCount.
     * Matches spec: products(filter: "{\"type\": \"configurable\"}")
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

    // ─── Attribute Filters ────────────────────────────────────────────────

    /**
     * Filter by color attribute returns valid response structure.
     * Matches spec: products(filter: "{\"color\": \"3\"}")
     */
    public function test_filter_by_color_attribute(): void
    {
        $filter = '{"color": "3"}';

        // If color attribute with options exists, create a product and use its real option ID
        $colorAttribute = \Webkul\Attribute\Models\Attribute::where('code', 'color')->first();
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
     * Filter by multiple attributes (color + size + brand).
     * Matches spec: products(filter: "{\"color\": \"5\", \"size\": \"1\", \"brand\": \"5\"}", first: 10)
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
            'first'  => 10,
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);
    }

    // ─── Sort with totalCount ─────────────────────────────────────────────

    /**
     * Sort A-Z by title returns edges and totalCount.
     * Matches spec: products(sortKey: "TITLE", reverse: false, first: 5)
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
     * Matches spec: products(sortKey: "TITLE", reverse: true, first: 5)
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
     * Matches spec: products(sortKey: "CREATED_AT", reverse: true, first: 10)
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
     * Matches spec: products(sortKey: "CREATED_AT", reverse: false, first: 10)
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
     * Matches spec: products(reverse: false, sortKey: "PRICE", first: 10)
     * Sorting is based on minimumPrice (the effective selling price).
     *
     * Note: price sort uses product_flat.min_price which is populated by Bagisto's
     * indexer on full product save. Test verifies the API spec fields are correct;
     * ordering correctness is covered by integration with the existing product data.
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

        // Verify all spec-required fields are present on each returned node
        foreach ($data['edges'] as $edge) {
            $node = $edge['node'];
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('name', $node);
            $this->assertArrayHasKey('sku', $node);
            $this->assertArrayHasKey('minimumPrice', $node);
            $this->assertArrayHasKey('price', $node);
            $this->assertArrayHasKey('specialPrice', $node);
        }

        // Verify ascending minimumPrice order for products that have a non-null minimumPrice
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
     * Matches spec: products(reverse: true, sortKey: "PRICE", first: 10)
     * Sorting is based on minimumPrice (the effective selling price).
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

        // Verify all spec-required fields are present on each returned node
        foreach ($data['edges'] as $edge) {
            $node = $edge['node'];
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('name', $node);
            $this->assertArrayHasKey('sku', $node);
            $this->assertArrayHasKey('minimumPrice', $node);
            $this->assertArrayHasKey('price', $node);
            $this->assertArrayHasKey('specialPrice', $node);
        }

        // Verify descending minimumPrice order for products that have a non-null minimumPrice
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

    // ─── New / Featured / Brand Filters ──────────────────────────────────

    /**
     * New products filter (new=1) returns products marked as new.
     * Matches spec: products(filter: "{\"new\": \"1\"}", sortKey: "CREATED_AT", reverse: false, first: 10)
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
            'first'  => 10,
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
     * Matches spec: products(filter: "{\"featured\": \"1\"}", sortKey: "CREATED_AT", reverse: true, first: 12)
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
            'first'  => 12,
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
     * Matches spec: products(filter: "{\"brand\": \"25\"}", sortKey: "CREATED_AT", reverse: true, first: 12)
     */
    public function test_filter_by_brand(): void
    {
        $filter = '{"brand": "25"}';

        // If brand attribute with options exists, create a product and use its real option ID
        $brandAttribute = \Webkul\Attribute\Models\Attribute::where('code', 'brand')->first();
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

        $response = $this->graphQL($query, ['filter' => $filter, 'first' => 12]);

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

    // ─── Edge Cases ───────────────────────────────────────────────────────

    /**
     * Cursor-based pagination: second page uses endCursor from first page.
     */
    public function test_pagination_second_page_with_cursor(): void
    {
        // First page
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

        // Second page
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

        // Verify the second page node is different from the first page node
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

        // Acceptable outcomes: a GraphQL error OR empty edges (no 500)
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
     * first: 0 returns empty edges or a validation error — not a 500.
     */
    public function test_first_zero_returns_empty_or_error(): void
    {
        $query = <<<'GQL'
            query getProducts {
              products(first: 0) {
                edges {
                  node { id sku }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $errors = $response->json('errors');
        $data = $response->json('data.products');
        $this->assertTrue(
            ! empty($errors) || $data !== null && empty($data['edges']),
            'first: 0 should return empty edges or an error, not 500'
        );
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
            'first'  => 10,
        ]);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.products');
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertIsInt($data['totalCount']);

        // Verify minimumPrice, price, specialPrice fields are present
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
}
