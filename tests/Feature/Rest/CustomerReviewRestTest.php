<?php

namespace Tests\Feature\BagistoApi\Rest;

use Tests\Feature\BagistoApi\RestApiTestCase;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductReview;

class CustomerReviewRestTest extends RestApiTestCase
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

    // ── Collection ────────────────────────────────────────────

    /**
     * Test: GET /api/shop/customer-reviews returns collection
     */
    public function test_get_customer_reviews_collection(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-reviews');

        $response->assertOk();
        $json = $response->json();

        expect($json)->toBeArray();
        expect(count($json))->toBeGreaterThanOrEqual(2);
    }

    /**
     * Test: GET /api/shop/customer-reviews without auth returns error
     */
    public function test_get_customer_reviews_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/customer-reviews');

        expect(in_array($response->getStatusCode(), [401, 403, 500]))->toBeTrue();
    }

    /**
     * Test: Customer only sees own reviews
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

        $response = $this->authenticatedGet($testData['customer'], '/api/shop/customer-reviews');

        $response->assertOk();
        $json = $response->json();

        /** Should only see the 2 reviews belonging to testData customer */
        expect(count($json))->toBe(2);
    }

    /**
     * Test: Customer with no reviews returns empty collection
     */
    public function test_customer_with_no_reviews_returns_empty(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/customer-reviews');

        $response->assertOk();
        $json = $response->json();

        expect($json)->toBeArray();
        expect(count($json))->toBe(0);
    }

    // ── Single Item ───────────────────────────────────────────

    /**
     * Test: GET /api/shop/customer-reviews/{id} returns single review
     */
    public function test_get_single_customer_review(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-reviews/'.$testData['review1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json)->toHaveKey('id');
        expect($json)->toHaveKey('title');
        expect($json)->toHaveKey('comment');
        expect($json)->toHaveKey('rating');
        expect($json)->toHaveKey('status');
        expect($json)->toHaveKey('product');
        expect($json)->toHaveKey('customer');
        expect($json)->toHaveKey('createdAt');
        expect($json)->toHaveKey('updatedAt');
        expect($json['id'])->toBe($testData['review1']->id);
        expect($json['title'])->toBe('Great product');
        expect($json['rating'])->toBe(5);
    }

    /**
     * Test: GET /api/shop/customer-reviews/{id} with invalid id returns 404
     */
    public function test_get_customer_review_not_found(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/customer-reviews/999999');

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    /**
     * Test: Cannot access another customer's review by ID
     */
    public function test_cannot_access_other_customers_review(): void
    {
        $testData = $this->createTestData();
        $otherCustomer = $this->createCustomer();

        $response = $this->authenticatedGet(
            $otherCustomer,
            '/api/shop/customer-reviews/'.$testData['review1']->id
        );

        /** Should return 404/500 because the review doesn't belong to otherCustomer */
        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    /**
     * Test: Single review without auth returns error
     */
    public function test_get_single_review_requires_auth(): void
    {
        $testData = $this->createTestData();

        $response = $this->publicGet(
            '/api/shop/customer-reviews/'.$testData['review1']->id
        );

        expect(in_array($response->getStatusCode(), [401, 403, 500]))->toBeTrue();
    }

    // ── Filtering (via query params) ──────────────────────────

    /**
     * Test: Filter reviews by status via query parameter
     */
    public function test_filter_reviews_by_status(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-reviews?status=approved'
        );

        $response->assertOk();
        $json = $response->json();

        /** Only the approved review should be returned */
        foreach ($json as $review) {
            expect($review['status'])->toBe('approved');
        }
    }

    /**
     * Test: Filter reviews by rating via query parameter
     */
    public function test_filter_reviews_by_rating(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-reviews?rating=5'
        );

        $response->assertOk();
        $json = $response->json();

        /** Only the 5-star review should be returned */
        foreach ($json as $review) {
            expect($review['rating'])->toBe(5);
        }
    }

    // ── Response format assertions ────────────────────────────

    /**
     * Test: Review includes product relationship data
     */
    public function test_review_includes_product_relationship(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-reviews/'.$testData['review1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json)->toHaveKey('product');
        expect($json['product'])->not()->toBeNull();
    }

    /**
     * Test: Review includes customer relationship data
     */
    public function test_review_includes_customer_relationship(): void
    {
        $testData = $this->createTestData();

        $response = $this->authenticatedGet(
            $testData['customer'],
            '/api/shop/customer-reviews/'.$testData['review1']->id
        );

        $response->assertOk();
        $json = $response->json();

        expect($json)->toHaveKey('customer');
        expect($json['customer'])->not()->toBeNull();
    }
}
