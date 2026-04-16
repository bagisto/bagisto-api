<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\Product;

/**
 * Products GraphQL API Test Cases
 *
 * Organized by test categories:
 * - Products - Sorting
 * - Products - Search
 * - Products - Single Product
 * - Products - Variants
 * - Products - Full Details
 */
class ProductTest extends GraphQLTestCase
{
    protected int $testProductId = 0;

    protected string $testProductSku = '';

    protected function setUp(): void
    {
        parent::setUp();

        $product = $this->createBaseProduct('simple', [
            'sku' => 'TEST-PRODUCT-FIXTURE-'.uniqid(),
        ]);
        $this->ensureInventory($product, 50);

        $this->testProductId = (int) $product->id;
        $this->testProductSku = (string) $product->sku;
    }

    /**
     * Test: Query products sorted A-Z
     */
    public function test_get_products_sorted_az(): void
    {
        $query = <<<'GQL'
            query getProductsSorted {
              products(reverse: false, sortKey: "TITLE", first: 10) {
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

        $response = $this->graphQL($query);

        $response->assertOk();

        $productNode = $response->json('data.products.edges.0.node');

        expect($productNode)->toHaveKeys([
            'id',
            'name',
            'sku',
            'price',
        ]);
    }

    /**
     * Test: Query products sorted Z to A
     */
    public function test_get_products_sorted_za(): void
    {
        $query = <<<'GQL'
            query getProductsSorted {
              products(reverse: true, sortKey: "TITLE", first: 10) {
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

        $response = $this->graphQL($query);

        $response->assertOk();

        $productNode = $response->json('data.products.edges.0.node');

        expect($productNode)->toHaveKeys([
            'id',
            'name',
            'sku',
            'price',
        ]);
    }

    /**
     * Test: Query products sorted by newest first
     */
    public function test_get_products_newest_first(): void
    {
        $query = <<<'GQL'
            query getProductsSorted {
              products(reverse: true, sortKey: "CREATED_AT", first: 10) {
                edges {
                  node {
                    id
                    name
                    sku
                    price
                    createdAt
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();

        $productNode = $response->json('data.products.edges.0.node');

        expect($productNode)->toHaveKeys([
            'id',
            'name',
            'sku',
            'price',
            'createdAt',
        ]);
    }

    /**
     * Test: Query products sorted by oldest first
     */
    public function test_get_products_oldest_first(): void
    {
        $query = <<<'GQL'
            query getProductsSorted {
              products(reverse: false, sortKey: "CREATED_AT", first: 10) {
                edges {
                  node {
                    id
                    name
                    sku
                    price
                    createdAt
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();

        $productNode = $response->json('data.products.edges.0.node');

        expect($productNode)->toHaveKeys([
            'id',
            'name',
            'sku',
            'price',
            'createdAt',
        ]);
    }

    /**
     * Test: Query products sorted by cheapest first
     */
    public function test_get_products_cheapest_first(): void
    {
        $query = <<<'GQL'
            query getProductsSorted {
              products(reverse: false, sortKey: "PRICE", first: 10) {
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

        $response = $this->graphQL($query);

        $response->assertOk();

        $productNode = $response->json('data.products.edges.0.node');

        expect($productNode)->toHaveKeys([
            'id',
            'name',
            'sku',
            'price',
        ]);
    }

    /**
     * Test: Query products sorted by most expensive first
     */
    public function test_get_products_most_expensive_first(): void
    {
        $query = <<<'GQL'
            query getProductsSorted {
              products(reverse: true, sortKey: "PRICE", first: 10) {
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

        $response = $this->graphQL($query);

        $response->assertOk();

        $productNode = $response->json('data.products.edges.0.node');

        expect($productNode)->toHaveKeys([
            'id',
            'name',
            'sku',
            'price',
        ]);
    }

    /**
     * Test: Search products with query filter
     */
    public function test_search_products(): void
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

        $variables = [
            'query'   => 'shirt',
            'sortKey' => 'TITLE',
            'reverse' => false,
            'first'   => 10,
        ];

        $response = $this->graphQL($query, $variables);

        $response->assertOk();

        // Check if products exist (search might return empty)
        $edges = $response->json('data.products.edges');

        if (! empty($edges)) {
            $productNode = $response->json('data.products.edges.0.node');

            expect($productNode)->toHaveKeys([
                'id',
                'name',
                'sku',
                'price',
            ]);
        }
    }

    /**
     * Test: Get product by ID
     */
    public function test_get_product_by_id(): void
    {
        $query = <<<'GQL'
            query getProduct($id: ID!) {
              product(id: $id) {
                id
                name
                sku
                urlKey
                price
              }
            }
        GQL;

        $variables = [
            'id' => (string) $this->testProductId,
        ];

        $response = $this->graphQL($query, $variables);

        $response->assertOk();

        $product = $response->json('data.product');

        expect($product)->toHaveKeys([
            'id',
            'name',
            'sku',
            'urlKey',
            'price',
        ]);
    }

    /**
     * Test: Search product by SKU
     */
    public function test_search_product_by_sku(): void
    {
        $query = <<<'GQL'
            query getProduct($sku: String!) {
              product(sku: $sku) {
                id
                name
                sku
                urlKey
                price
              }
            }
        GQL;

        $variables = [
            'sku' => 'COASTALBREEZEMENSHOODIE',
        ];

        $response = $this->graphQL($query, $variables);

        $response->assertOk();

        // Check if product exists
        $product = $response->json('data.product');

        if ($product) {
            expect($product)->toHaveKeys([
                'id',
                'name',
                'sku',
                'urlKey',
                'price',
            ]);
        }
    }

    /**
     * Test: Get product with variants
     */
    public function test_get_product_with_variants(): void
    {
        $query = <<<'GQL'
            query getProduct($id: ID!) {
              product(id: $id) {
                id
                name
                sku
                urlKey
                price
                variants {
                  edges {
                    node {
                      id
                      name
                      sku
                      price
                      attributeValues {
                        edges {
                          node {
                            value
                            attribute {
                              code
                              adminName
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $variables = [
            'id' => (string) $this->testProductId,
        ];

        $response = $this->graphQL($query, $variables);

        $response->assertOk();

        $product = $response->json('data.product');

        expect($product)->toHaveKeys([
            'id',
            'name',
            'sku',
            'urlKey',
            'price',
            'variants',
        ]);

        // Check variants if they exist
        $variants = $response->json('data.product.variants.edges');

        if (! empty($variants)) {
            $variantNode = $response->json('data.product.variants.edges.0.node');

            expect($variantNode)->toHaveKeys([
                'id',
                'name',
                'sku',
                'price',
                'attributeValues',
            ]);

            $attributeValues = $response->json('data.product.variants.edges.0.node.attributeValues.edges');

            if (! empty($attributeValues)) {
                $attributeValueNode = $response->json('data.product.variants.edges.0.node.attributeValues.edges.0.node');

                expect($attributeValueNode)->toHaveKeys([
                    'value',
                    'attribute',
                ]);

                expect($attributeValueNode['attribute'])->toHaveKeys([
                    'code',
                    'adminName',
                ]);
            }
        }
    }

    /**
     * Test: Get full product details
     */
    public function test_get_full_product_details(): void
    {
        $query = <<<'GQL'
            query getProduct($id: ID!) {
              product(id: $id) {
                id
                name
                sku
                urlKey
                description
                shortDescription
                price
                specialPrice
                images {
                  edges {
                    node {
                      id
                      publicPath
                      position
                    }
                  }
                }
                attributes {
                  code
                  value
                }
                variants {
                  edges {
                    node {
                      id
                      name
                      sku
                      price
                      attributeValues {
                        edges {
                          node {
                            value
                            attribute {
                              code
                              adminName
                            }
                          }
                        }
                      }
                    }
                  }
                }
                categories {
                  edges {
                    node {
                      id
                      translation {
                        name
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $variables = [
            'id' => (string) $this->testProductId,
        ];

        $response = $this->graphQL($query, $variables);

        $response->assertOk();

        $product = $response->json('data.product');

        if ($product) {
            expect($product)->toHaveKeys([
                'id',
                'name',
                'sku',
                'urlKey',
                'description',
                'shortDescription',
                'price',
                'specialPrice',
                'images',
                'attributes',
                'variants',
                'categories',
            ]);

            // Check images if they exist
            $images = $response->json('data.product.images.edges');
            if (! empty($images)) {
                $imageNode = $response->json('data.product.images.edges.0.node');
                expect($imageNode)->toHaveKeys([
                    'id',
                    'publicPath',
                    'position',
                ]);
            }

            // Check attributes if they exist
            $attributes = $response->json('data.product.attributes');
            if (! empty($attributes)) {
                $attribute = $response->json('data.product.attributes.0');
                expect($attribute)->toHaveKeys([
                    'code',
                    'value',
                ]);
            }

            // Check variants if they exist
            $variants = $response->json('data.product.variants.edges');
            if (! empty($variants)) {
                $variantNode = $response->json('data.product.variants.edges.0.node');
                expect($variantNode)->toHaveKeys([
                    'id',
                    'name',
                    'sku',
                    'price',
                    'attributeValues',
                ]);
            }

            // Check categories if they exist
            $categories = $response->json('data.product.categories.edges');
            if (! empty($categories)) {
                $categoryNode = $response->json('data.product.categories.edges.0.node');
                expect($categoryNode)->toHaveKeys([
                    'id',
                    'translation',
                ]);

                expect($categoryNode['translation'])->toHaveKeys(['name']);
            }
        }
    }

    /**
     * Test: Search Products with Filter - With Test Data
     *
     * This test creates a specific product and verifies the search functionality works.
     * Note: The search uses SKU and attribute text_value for matching.
     */
    public function test_search_products_with_filter_and_test_data(): void
    {
        // Seed required data first
        $this->seedRequiredData();

        // Create test product with 'shirt' in the SKU
        $product = $this->createShirtProduct();

        // Verify product was created
        $this->assertNotNull($product->id, 'Product should be created');
        $this->assertEquals('SHIRT-001', $product->sku);

        // Query all products first to verify the product exists
        $allProductsQuery = <<<'GQL'
            query getAllProducts($first: Int) {
              products(first: $first) {
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

        $response = $this->graphQL($allProductsQuery, ['first' => 50]);
        $response->assertSuccessful();

        // Verify product is in the list
        $edges = $response->json('data.products.edges');
        $this->assertNotEmpty($edges, 'Products list should not be empty');

        // Now test the search query
        $searchQuery = <<<'GQL'
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

        // Test with SKU search - use the SKU of the product we just created
        $variables = [
            'query'   => 'SHIRT-001',
            'sortKey' => 'TITLE',
            'reverse' => false,
            'first'   => 10,
        ];

        $response = $this->graphQL($searchQuery, $variables);

        $response->assertSuccessful();

        // Verify the response structure
        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('products', $response->json('data'));
        $this->assertArrayHasKey('edges', $response->json('data.products'));

        // Verify that at least one product is returned when searching by SKU
        $edges = $response->json('data.products.edges');
        $this->assertNotEmpty($edges, 'Search should return at least one product with SKU "SHIRT-001"');

        // Verify the first product has expected fields
        $productNode = $response->json('data.products.edges.0.node');
        $this->assertArrayHasKey('id', $productNode);
        $this->assertArrayHasKey('name', $productNode);
        $this->assertArrayHasKey('sku', $productNode);
        $this->assertArrayHasKey('price', $productNode);

        // Verify the product SKU matches
        $this->assertEquals('SHIRT-001', $productNode['sku']);
    }

    /**
     * Test: Search Products by Category ID with Pagination
     *
     * This test verifies that products can be filtered by category ID
     * with proper pagination support.
     */
    public function test_search_products_by_category_id_with_pagination(): void
    {
        // Seed required data first
        $this->seedRequiredData();

        $query = <<<'GQL'
            query getProductsByCategory($filter: String, $first: Int, $after: String) {
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

        // Test with category filter
        $variables = [
            'filter' => '{"category_id": "22"}',
            'first'  => 2,
            'after'  => 'Mg==',
        ];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        // Verify the response structure
        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('products', $response->json('data'));
        $this->assertArrayHasKey('edges', $response->json('data.products'));
        $this->assertArrayHasKey('pageInfo', $response->json('data.products'));
        $this->assertArrayHasKey('totalCount', $response->json('data.products'));

        // Verify pageInfo structure
        $pageInfo = $response->json('data.products.pageInfo');
        $this->assertArrayHasKey('hasNextPage', $pageInfo);
        $this->assertArrayHasKey('hasPreviousPage', $pageInfo);
        $this->assertArrayHasKey('startCursor', $pageInfo);
        $this->assertArrayHasKey('endCursor', $pageInfo);

        // Verify edges structure
        $edges = $response->json('data.products.edges');
        if (! empty($edges)) {
            $firstProduct = $response->json('data.products.edges.0.node');
            $this->assertArrayHasKey('id', $firstProduct);
            $this->assertArrayHasKey('sku', $firstProduct);
            $this->assertArrayHasKey('price', $firstProduct);
            $this->assertArrayHasKey('name', $firstProduct);
            $this->assertArrayHasKey('urlKey', $firstProduct);
            $this->assertArrayHasKey('baseImageUrl', $firstProduct);
            $this->assertArrayHasKey('description', $firstProduct);
            $this->assertArrayHasKey('shortDescription', $firstProduct);
            $this->assertArrayHasKey('specialPrice', $firstProduct);
        }
    }

    /**
     * Test: Filter Products by Type
     *
     * This test verifies that products can be filtered by type (e.g., configurable, simple, etc.)
     */
    public function test_filter_products_by_type(): void
    {
        // Seed required data first
        $this->seedRequiredData();

        $query = <<<'GQL'
            query getProductsByType($filter: String) {
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

        // Test with type filter - configurable products
        $variables = [
            'filter' => '{"type": "configurable"}',
        ];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        // Verify the response structure
        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('products', $response->json('data'));
        $this->assertArrayHasKey('edges', $response->json('data.products'));
        $this->assertArrayHasKey('totalCount', $response->json('data.products'));

        // Verify edges structure
        $edges = $response->json('data.products.edges');
        if (! empty($edges)) {
            $firstProduct = $response->json('data.products.edges.0.node');
            $this->assertArrayHasKey('id', $firstProduct);
            $this->assertArrayHasKey('sku', $firstProduct);
        }

        // Verify totalCount is an integer
        $totalCount = $response->json('data.products.totalCount');
        $this->assertIsInt($totalCount);
    }

    /**
     * Get products sorted with all formatted price fields.
     */
    public function test_get_products_sorted_with_formatted_prices(): void
    {
        $query = <<<'GQL'
            query getProductsSorted {
              products(reverse: false, sortKey: "TITLE", first: 10) {
                edges {
                  node {
                    id
                    name
                    sku
                    price
                    formattedPrice
                    specialPrice
                    formattedSpecialPrice
                    minimumPrice
                    formattedMinimumPrice
                    maximumPrice
                    formattedMaximumPrice
                    regularMinimumPrice
                    formattedRegularMinimumPrice
                    regularMaximumPrice
                    formattedRegularMaximumPrice
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();
        $this->assertNull($response->json('errors'));

        $edges = $response->json('data.products.edges');
        $this->assertNotNull($edges);
        $this->assertNotEmpty($edges, 'Expected at least one product');

        $node = $edges[0]['node'];

        // Verify numeric price fields
        $this->assertArrayHasKey('price', $node);
        $this->assertArrayHasKey('specialPrice', $node);
        $this->assertArrayHasKey('minimumPrice', $node);
        $this->assertArrayHasKey('maximumPrice', $node);
        $this->assertArrayHasKey('regularMinimumPrice', $node);
        $this->assertArrayHasKey('regularMaximumPrice', $node);

        // Verify formatted price fields exist and are strings (or null for specialPrice)
        $this->assertArrayHasKey('formattedPrice', $node);
        $this->assertArrayHasKey('formattedSpecialPrice', $node);
        $this->assertArrayHasKey('formattedMinimumPrice', $node);
        $this->assertArrayHasKey('formattedMaximumPrice', $node);
        $this->assertArrayHasKey('formattedRegularMinimumPrice', $node);
        $this->assertArrayHasKey('formattedRegularMaximumPrice', $node);

        // formattedPrice should be a currency string when price is non-zero
        if ((float) $node['price'] > 0) {
            $this->assertIsString($node['formattedPrice']);
            $this->assertNotEmpty($node['formattedPrice']);
        }

        // formattedMinimumPrice should always be a string
        $this->assertIsString($node['formattedMinimumPrice']);
        $this->assertIsString($node['formattedMaximumPrice']);
        $this->assertIsString($node['formattedRegularMinimumPrice']);
        $this->assertIsString($node['formattedRegularMaximumPrice']);
    }

    /**
     * Helper method to create a shirt product for testing
     */
    protected function createShirtProduct()
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'SHIRT-001',
        ]);
        $this->ensureInventory($product, 50);

        return $product;
    }
}
