<?php

namespace Tests\Feature\BagistoApi\GraphQL;

use Tests\Feature\BagistoApi\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductReview;

class CustomerReviewTest extends GraphQLTestCase
{
    /**
     * Create test data — customer with reviews on multiple products
     */
    private function createTestData(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $review1 = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product1->id,
            'title'       => 'Great product',
            'comment'     => 'Really enjoyed using this product.',
            'rating'      => 5,
            'status'      => 'approved',
            'name'        => $customer->first_name,
        ]);

        $review2 = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product2->id,
            'title'       => 'Average product',
            'comment'     => 'It was okay, nothing special.',
            'rating'      => 3,
            'status'      => 'pending',
            'name'        => $customer->first_name,
        ]);

        return compact('customer', 'product1', 'product2', 'review1', 'review2');
    }

    // ── Collection Queries ────────────────────────────────────

    /**
     * Test: Query all customer reviews collection
     */
    public function test_get_customer_reviews_collection(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerReviews {
              customerReviews {
                edges {
                  cursor
                  node {
                    id
                    _id
                    title
                    comment
                    rating
                    status
                    name
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
        $data = $response->json('data.customerReviews');

        expect($data['totalCount'])->toBeGreaterThanOrEqual(2);
        expect($data['edges'])->not()->toBeEmpty();
    }

    /**
     * Test: Unauthenticated request returns error
     */
    public function test_get_customer_reviews_requires_authentication(): void
    {
        $query = <<<'GQL'
            query getCustomerReviews {
              customerReviews(first: 5) {
                edges {
                  node {
                    _id
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        $errors = $response->json('errors');

        expect($errors)->not()->toBeEmpty();
    }

    /**
     * Test: Customer only sees their own reviews
     */
    public function test_customer_only_sees_own_reviews(): void
    {
        $testData = $this->createTestData();

        /** Create another customer with their own review */
        $otherCustomer = $this->createCustomer();
        $product = Product::factory()->create();

        ProductReview::factory()->create([
            'customer_id' => $otherCustomer->id,
            'product_id'  => $product->id,
            'status'      => 'approved',
        ]);

        $query = <<<'GQL'
            query getCustomerReviews {
              customerReviews(first: 50) {
                edges {
                  node {
                    _id
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerReviews');

        /** Should only see the 2 reviews belonging to testData customer */
        expect($data['totalCount'])->toBe(2);
    }

    // ── Single Item Query ─────────────────────────────────────

    /**
     * Test: Query single customer review by ID
     */
    public function test_get_customer_review_by_id(): void
    {
        $testData = $this->createTestData();
        $reviewId = "/api/shop/customer-reviews/{$testData['review1']->id}";

        $query = <<<GQL
            query getCustomerReview {
              customerReview(id: "{$reviewId}") {
                id
                _id
                title
                comment
                rating
                status
                name
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
        $data = $response->json('data.customerReview');

        expect($data['_id'])->toBe($testData['review1']->id);
        expect($data['title'])->toBe('Great product');
        expect($data['rating'])->toBe(5);
        expect($data['product'])->toHaveKey('id');
        expect($data['customer'])->toHaveKey('id');
    }

    /**
     * Test: Query with invalid ID returns null/error
     */
    public function test_invalid_customer_review_id_returns_error(): void
    {
        $query = <<<'GQL'
            query getCustomerReview {
              customerReview(id: "/api/shop/customer-reviews/99999") {
                id
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();
        expect($response->json('data.customerReview'))->toBeNull();
    }

    // ── Filtering ─────────────────────────────────────────────

    /**
     * Test: Filter reviews by status
     */
    public function test_filter_reviews_by_status(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerReviews {
              customerReviews(first: 10, status: "approved") {
                edges {
                  node {
                    _id
                    status
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerReviews');

        expect($data['totalCount'])->toBe(1);

        $statuses = collect($data['edges'])->pluck('node.status')->unique()->toArray();
        expect($statuses)->toBe(['approved']);
    }

    /**
     * Test: Filter reviews by rating
     */
    public function test_filter_reviews_by_rating(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerReviews {
              customerReviews(first: 10, rating: 5) {
                edges {
                  node {
                    _id
                    rating
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerReviews');

        expect($data['totalCount'])->toBe(1);

        $ratings = collect($data['edges'])->pluck('node.rating')->unique()->toArray();
        expect($ratings)->toBe([5]);
    }

    /**
     * Test: Filter with no matching results returns empty collection
     */
    public function test_filter_with_no_matches_returns_empty(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerReviews {
              customerReviews(first: 10, status: "rejected") {
                edges {
                  node {
                    _id
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($testData['customer'], $query);

        $response->assertOk();
        $data = $response->json('data.customerReviews');

        expect($data['totalCount'])->toBe(0);
        expect($data['edges'])->toBeEmpty();
    }

    // ── Pagination ────────────────────────────────────────────

    /**
     * Test: Pagination with first parameter
     */
    public function test_pagination_first(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerReviews {
              customerReviews(first: 1) {
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
        $data = $response->json('data.customerReviews');

        expect($data['edges'])->toHaveCount(1);
    }

    /**
     * Test: Cursor-based pagination with after parameter
     */
    public function test_pagination_with_cursor(): void
    {
        $testData = $this->createTestData();

        /** Fetch first page to get cursor */
        $firstQuery = <<<'GQL'
            query getCustomerReviews {
              customerReviews(first: 1) {
                edges {
                  cursor
                  node {
                    _id
                  }
                }
              }
            }
        GQL;

        $firstResponse = $this->authenticatedGraphQL($testData['customer'], $firstQuery);
        $cursor = $firstResponse->json('data.customerReviews.edges.0.cursor');

        /** Fetch second page using cursor */
        $secondQuery = <<<GQL
            query getCustomerReviews {
              customerReviews(first: 1, after: "{$cursor}") {
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

    // ── Field & Format Assertions ─────────────────────────────

    /**
     * Test: All expected fields are present on the review node
     */
    public function test_query_all_fields(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerReviews {
              customerReviews(first: 1) {
                edges {
                  node {
                    id
                    _id
                    title
                    comment
                    rating
                    status
                    name
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
        $node = $response->json('data.customerReviews.edges.0.node');

        expect($node)->toHaveKeys([
            'id', '_id', 'title', 'comment', 'rating',
            'status', 'name', 'product', 'customer',
            'createdAt', 'updatedAt',
        ]);
    }

    /**
     * Test: Timestamps are returned in ISO8601 format
     */
    public function test_timestamps_are_iso8601_format(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerReviews {
              customerReviews(first: 1) {
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
        $node = $response->json('data.customerReviews.edges.0.node');

        expect($node['createdAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
        expect($node['updatedAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
    }

    /**
     * Test: Numeric ID (_id) is an integer
     */
    public function test_numeric_id_is_integer(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerReviews {
              customerReviews(first: 1) {
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
        $node = $response->json('data.customerReviews.edges.0.node');

        expect($node['_id'])->toBeInt();
    }

    // ── Schema Introspection ──────────────────────────────────

    /**
     * Test: CustomerReview type exists in GraphQL schema
     */
    public function test_introspection_query(): void
    {
        $query = <<<'GQL'
            {
              __type(name: "CustomerReview") {
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

        expect($type['name'])->toBe('CustomerReview');
        expect($type['kind'])->toBe('OBJECT');

        $fieldNames = collect($type['fields'])->pluck('name')->toArray();
        expect($fieldNames)->toContain('id', '_id', 'title', 'comment', 'rating', 'status', 'name');
    }

    // ── Edge Cases ────────────────────────────────────────────

    /**
     * Test: Customer with no reviews returns empty collection
     */
    public function test_customer_with_no_reviews_returns_empty(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $query = <<<'GQL'
            query getCustomerReviews {
              customerReviews(first: 10) {
                edges {
                  node {
                    _id
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $query);

        $response->assertOk();
        $data = $response->json('data.customerReviews');

        expect($data['totalCount'])->toBe(0);
        expect($data['edges'])->toBeEmpty();
    }

    /**
     * Test: Reviews include product relationship data
     */
    public function test_reviews_include_product_relationship(): void
    {
        $testData = $this->createTestData();

        $query = <<<'GQL'
            query getCustomerReviews {
              customerReviews(first: 1) {
                edges {
                  node {
                    _id
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
        $node = $response->json('data.customerReviews.edges.0.node');

        expect($node)->toHaveKey('product');
        expect($node['product'])->toHaveKey('id');
    }
}
