<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\ProductReview;

class ProductReviewTest extends RestApiTestCase
{
    private function seededCustomerAndProduct(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $product = $this->createBaseProduct('simple');

        return [$customer, $product];
    }

    // ── POST /reviews (Customer Review) ───────────────────────

    public function test_post_review_creates_review(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $response = $this->authenticatedPost($customer, '/api/shop/reviews', [
            'product_id' => $product->id,
            'title'      => 'Great product',
            'comment'    => 'Really enjoyed using this product.',
            'rating'     => 5,
            'name'       => 'John Doe',
        ]);

        $response->assertCreated();

        $json = $response->json();
        expect($json['title'])->toBe('Great product');
        expect($json['rating'])->toBe(5);

        expect(ProductReview::where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->where('title', 'Great product')
            ->exists())->toBeTrue();
    }

    public function test_post_review_with_nonexistent_product_returns_error(): void
    {
        [$customer] = $this->seededCustomerAndProduct();

        $response = $this->authenticatedPost($customer, '/api/shop/reviews', [
            'product_id' => 999999,
            'title'      => 'X',
            'comment'    => 'Y',
            'rating'     => 4,
            'name'       => 'John',
        ]);

        expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
    }

    public function test_post_review_with_invalid_rating_returns_error(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $response = $this->authenticatedPost($customer, '/api/shop/reviews', [
            'product_id' => $product->id,
            'title'      => 'X',
            'comment'    => 'Y',
            'rating'     => 99,
            'name'       => 'John',
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422]);
    }

    // ── GET /reviews (Product tag — collection) ──────────────

    public function test_get_reviews_collection_is_public(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'status'      => 'approved',
        ]);

        $response = $this->publicGet('/api/shop/reviews');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_get_single_review_by_id(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'title'       => 'Fetch-me',
        ]);

        $response = $this->publicGet('/api/shop/reviews/'.$review->id);

        $response->assertOk();
        $json = $response->json();
        expect($json['title'])->toBe('Fetch-me');
    }

    // ── PATCH /reviews/{id} (Customer Review) ────────────────

    public function test_patch_review_updates_fields(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'title'       => 'Original',
            'comment'     => 'Original comment',
            'rating'      => 3,
        ]);

        $response = $this->call(
            'PATCH',
            '/api/shop/reviews/'.$review->id,
            [],
            [],
            [],
            $this->transformHeadersToServerVars([
                ...$this->authHeaders($customer),
                'Content-Type' => 'application/merge-patch+json',
                'Accept'       => 'application/json',
            ]),
            json_encode([
                'title'   => 'Updated title',
                'comment' => 'Updated comment',
                'rating'  => 4,
            ])
        );

        expect($response->getStatusCode())->toBeIn([200, 201]);

        $fresh = ProductReview::find($review->id);
        expect($fresh->title)->toBe('Updated title');
        expect($fresh->rating)->toBe(4);
    }

    // ── DELETE /reviews/{id} (Customer Review) ───────────────

    public function test_delete_review(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
        ]);

        $response = $this->authenticatedDelete($customer, '/api/shop/reviews/'.$review->id);

        expect($response->getStatusCode())->toBeIn([200, 204]);
        expect(ProductReview::where('id', $review->id)->exists())->toBeFalse();
    }
}
