<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\ProductReview;

class ProductReviewTest extends RestApiTestCase
{
    /**
     * Test post review creates review.
     */
    public function test_post_review_creates_review(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $response = $this->authenticatedPost($customer, '/api/shop/reviews', [
            'product_id' => $product->id,
            'title' => 'Great product',
            'comment' => 'Really enjoyed using this product.',
            'rating' => 5,
            'name' => 'John Doe',
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

    /**
     * Test post review ignores client status and owner.
     */
    public function test_post_review_ignores_client_status_and_owner(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $victim = $this->createCustomer([
            'email' => 'victim-'.uniqid().'@example.com',
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $response = $this->authenticatedPost($customer, '/api/shop/reviews', [
            'product_id' => $product->id,
            'title' => 'Spoofed review',
            'comment' => 'Trying to self-approve as someone else.',
            'rating' => 5,
            'name' => 'Someone Else',
            'status' => 'approved',
            'customer_id' => $victim->id,
        ]);

        $response->assertCreated();

        $review = ProductReview::where('title', 'Spoofed review')->firstOrFail();

        expect($review->status)->toBe('pending');
        expect((int) $review->customer_id)->toBe((int) $customer->id);
        expect($review->name)->toBe($customer->name);
    }

    /**
     * Test post review without title is rejected.
     */
    public function test_post_review_without_title_is_rejected(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $response = $this->authenticatedPost($customer, '/api/shop/reviews', [
            'product_id' => $product->id,
            'comment' => 'Body without a title.',
            'rating' => 4,
            'name' => 'John Doe',
        ]);

        expect($response->getStatusCode())->toBe(400);
        expect(ProductReview::where('product_id', $product->id)->exists())->toBeFalse();
    }

    /**
     * Test post review as guest is refused when guest reviews are off.
     */
    public function test_post_review_as_guest_is_refused_when_guest_reviews_are_off(): void
    {
        [, $product] = $this->seededCustomerAndProduct();

        $response = $this->publicPost('/api/shop/reviews', [
            'product_id' => $product->id,
            'title' => 'Guest attempt',
            'comment' => 'Should not be stored.',
            'rating' => 5,
            'name' => 'Guest',
        ]);

        expect($response->getStatusCode())->toBe(403);
        expect(ProductReview::where('product_id', $product->id)->exists())->toBeFalse();
    }

    /**
     * Test post review with nonexistent product returns error.
     */
    public function test_post_review_with_nonexistent_product_returns_error(): void
    {
        [$customer] = $this->seededCustomerAndProduct();

        $response = $this->authenticatedPost($customer, '/api/shop/reviews', [
            'product_id' => 999999,
            'title' => 'X',
            'comment' => 'Y',
            'rating' => 4,
            'name' => 'John',
        ]);

        expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
    }

    /**
     * Test post review with invalid rating returns error.
     */
    public function test_post_review_with_invalid_rating_returns_error(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $response = $this->authenticatedPost($customer, '/api/shop/reviews', [
            'product_id' => $product->id,
            'title' => 'X',
            'comment' => 'Y',
            'rating' => 99,
            'name' => 'John',
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422]);
    }

    /**
     * Test get reviews collection is public.
     */
    public function test_get_reviews_collection_is_public(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'status' => 'approved',
        ]);

        $response = $this->publicGet('/api/shop/reviews');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test get single review by id.
     */
    public function test_get_single_review_by_id(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'title' => 'Fetch-me',
            'status' => 'approved',
        ]);

        $response = $this->publicGet('/api/shop/reviews/'.$review->id);

        $response->assertOk();
        $json = $response->json();
        expect($json['title'])->toBe('Fetch-me');
    }

    /**
     * Test get single pending review is not public.
     */
    public function test_get_single_pending_review_is_not_public(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'status' => 'pending',
        ]);

        $this->publicGet('/api/shop/reviews/'.$review->id)->assertNotFound();
    }

    /**
     * Test patch review updates fields.
     */
    public function test_patch_review_updates_fields(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'title' => 'Original',
            'comment' => 'Original comment',
            'rating' => 3,
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
                'Accept' => 'application/json',
            ]),
            json_encode([
                'title' => 'Updated title',
                'comment' => 'Updated comment',
                'rating' => 4,
            ])
        );

        expect($response->getStatusCode())->toBeIn([200, 201]);

        $fresh = ProductReview::find($review->id);
        expect($fresh->title)->toBe('Updated title');
        expect($fresh->rating)->toBe(4);
    }

    /**
     * Test delete review.
     */
    public function test_delete_review(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $response = $this->authenticatedDelete($customer, '/api/shop/reviews/'.$review->id);

        expect($response->getStatusCode())->toBeIn([200, 204]);
        expect(ProductReview::where('id', $review->id)->exists())->toBeFalse();
    }

    /**
     * Test delete review owned by another customer is refused.
     */
    public function test_delete_review_owned_by_another_customer_is_refused(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $owner = $this->createCustomer([
            'email' => 'owner-'.uniqid().'@example.com',
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $review = ProductReview::factory()->create([
            'customer_id' => $owner->id,
            'product_id' => $product->id,
        ]);

        $response = $this->authenticatedDelete($customer, '/api/shop/reviews/'.$review->id);

        expect($response->getStatusCode())->toBeIn([401, 403]);
        expect(ProductReview::where('id', $review->id)->exists())->toBeTrue();
    }

    /**
     * Test delete guest review is refused.
     */
    public function test_delete_guest_review_is_refused(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $review = ProductReview::factory()->create([
            'customer_id' => null,
            'product_id' => $product->id,
        ]);

        $response = $this->authenticatedDelete($customer, '/api/shop/reviews/'.$review->id);

        expect($response->getStatusCode())->toBeIn([401, 403]);
        expect(ProductReview::where('id', $review->id)->exists())->toBeTrue();
    }

    /**
     * Test patch review owned by another customer is refused.
     */
    public function test_patch_review_owned_by_another_customer_is_refused(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $owner = $this->createCustomer([
            'email' => 'owner-'.uniqid().'@example.com',
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $review = ProductReview::factory()->create([
            'customer_id' => $owner->id,
            'product_id' => $product->id,
            'title' => 'Original title',
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
                'Accept' => 'application/json',
            ]),
            json_encode(['title' => 'Hijacked title'])
        );

        expect($response->getStatusCode())->toBeIn([401, 403]);
        expect(ProductReview::find($review->id)->title)->toBe('Original title');
    }

    /**
     * Test nested reviews default excludes pending.
     */
    public function test_nested_reviews_default_excludes_pending(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews');

        $response->assertOk();
        expect($this->statuses($response))->not()->toContain('pending');
        expect($this->statuses($response))->not()->toContain('0');
    }

    /**
     * Test nested reviews ignore a client supplied status.
     */
    public function test_nested_reviews_ignore_a_client_supplied_status(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews?status=pending');

        $response->assertOk();
        $statuses = $this->statuses($response);
        expect($statuses)->not()->toBeEmpty();
        expect($statuses)->not()->toContain('pending');
    }

    /**
     * Test nested reviews rating filter.
     */
    public function test_nested_reviews_rating_filter(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews?rating=3');

        $response->assertOk();
        foreach ($this->ratings($response) as $r) {
            expect($r)->toBe(3);
        }
    }

    /**
     * Test nested reviews per page limits results.
     */
    public function test_nested_reviews_per_page_limits_results(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews?per_page=2');

        $response->assertOk();
        expect(count($response->json()))->toBe(2);
    }

    /**
     * Test nested reviews limit alias works.
     */
    public function test_nested_reviews_limit_alias_works(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews?limit=2');

        $response->assertOk();
        expect(count($response->json()))->toBe(2);
    }

    /**
     * Test flat reviews default excludes pending for product.
     */
    public function test_flat_reviews_default_excludes_pending_for_product(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/reviews?product_id='.$product->id);

        $response->assertOk();
        expect($this->statuses($response))->not()->toContain('pending');
    }

    /**
     * Test flat reviews ignore a client supplied status.
     */
    public function test_flat_reviews_ignore_a_client_supplied_status(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/reviews?product_id='.$product->id.'&status=pending');

        $response->assertOk();
        $statuses = $this->statuses($response);
        expect($statuses)->not()->toBeEmpty();
        expect($statuses)->not()->toContain('pending');
    }

    /**
     * Test the reviewer name is censored as the storefront censors it, by default.
     */
    public function test_reviewer_name_is_censored_by_default(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'name' => 'John Doe',
            'status' => 'approved',
        ]);

        $this->publicGet('/api/shop/reviews/'.$review->id)->assertOk()->assertJsonPath('name', 'J*** D**');

        $names = array_column($this->publicGet('/api/shop/products/'.$product->id.'/reviews')->json(), 'name');

        expect($names)->toBe(['J*** D**']);
    }

    /**
     * Test the author reads their own name uncensored.
     */
    public function test_reviewer_name_is_not_censored_for_its_author(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'name' => 'John Doe',
            'status' => 'approved',
        ]);

        $this->authenticatedGet($customer, '/api/shop/reviews/'.$review->id)->assertOk()->assertJsonPath('name', 'John Doe');
    }

    /**
     * Test the reviewer name is shown in full when the store turns censoring off.
     */
    public function test_reviewer_name_is_shown_when_censoring_is_off(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        DB::table('core_config')->where('code', 'catalog.products.review.censoring_reviewer_name')->delete();

        DB::table('core_config')->insert([
            'code' => 'catalog.products.review.censoring_reviewer_name',
            'value' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->forgetCoreConfigCache();

        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'name' => 'John Doe',
            'status' => 'approved',
        ]);

        $this->publicGet('/api/shop/reviews/'.$review->id)->assertOk()->assertJsonPath('name', 'John Doe');
    }

    /**
     * Seeded customer and product.
     */
    private function seededCustomerAndProduct(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $product = $this->createBaseProduct('simple');

        return [$customer, $product];
    }

    /**
     * Seeded reviews for filtering.
     */
    private function seededReviewsForFiltering(): array
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        foreach ([['approved', 5], ['approved', 5], ['approved', 3], ['pending', 5], ['pending', 5]] as [$status, $rating]) {
            ProductReview::factory()->create([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'status' => $status,
                'rating' => $rating,
            ]);
        }

        return [$customer, $product];
    }

    /**
     * Statuses.
     */
    private function statuses($response): array
    {
        return array_map(fn ($r) => (string) ($r['status'] ?? ''), $response->json());
    }

    /**
     * Ratings.
     */
    private function ratings($response): array
    {
        return array_map(fn ($r) => (int) ($r['rating'] ?? 0), $response->json());
    }
}
