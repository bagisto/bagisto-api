<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\Event;
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

    public function test_post_review_fires_core_create_events(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $fired = [];

        Event::listen('customer.review.create.before', function ($productId) use (&$fired) {
            $fired['before'] = (int) $productId;
        });

        Event::listen('customer.review.create.after', function ($review) use (&$fired) {
            $fired['after'] = $review->title;
        });

        $this->authenticatedPost($customer, '/api/shop/reviews', [
            'product_id' => $product->id,
            'title' => 'Evented review',
            'comment' => 'Listeners should see this.',
            'rating' => 4,
        ])->assertCreated();

        expect($fired['before'] ?? null)->toBe((int) $product->id);
        expect($fired['after'] ?? null)->toBe('Evented review');
    }

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

    public function test_get_single_review_by_id(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();

        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'title' => 'Fetch-me',
        ]);

        $response = $this->publicGet('/api/shop/reviews/'.$review->id);

        $response->assertOk();
        $json = $response->json();
        expect($json['title'])->toBe('Fetch-me');
    }

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

    private function statuses($response): array
    {
        return array_map(fn ($r) => (string) ($r['status'] ?? ''), $response->json());
    }

    private function ratings($response): array
    {
        return array_map(fn ($r) => (int) ($r['rating'] ?? 0), $response->json());
    }

    public function test_nested_reviews_default_excludes_pending(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews');

        $response->assertOk();
        expect($this->statuses($response))->not()->toContain('pending');
        expect($this->statuses($response))->not()->toContain('0');
    }

    public function test_nested_reviews_ignore_a_status_filter(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews?status=pending');

        $response->assertOk();
        expect($this->statuses($response))->not()->toBeEmpty();
        expect($this->statuses($response))->not()->toContain('pending');
    }

    public function test_nested_reviews_rating_filter(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews?rating=3');

        $response->assertOk();
        foreach ($this->ratings($response) as $r) {
            expect($r)->toBe(3);
        }
    }

    public function test_nested_reviews_per_page_limits_results(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews?per_page=2');

        $response->assertOk();
        expect(count($response->json()))->toBe(2);
    }

    public function test_nested_reviews_limit_alias_works(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews?limit=2');

        $response->assertOk();
        expect(count($response->json()))->toBe(2);
    }

    public function test_flat_reviews_default_excludes_pending_for_product(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/reviews?product_id='.$product->id);

        $response->assertOk();
        expect($this->statuses($response))->not()->toContain('pending');
    }

    public function test_flat_reviews_ignore_a_status_filter(): void
    {
        [, $product] = $this->seededReviewsForFiltering();

        $response = $this->publicGet('/api/shop/reviews?product_id='.$product->id.'&status=pending');

        $response->assertOk();
        expect($this->statuses($response))->not()->toContain('pending');
    }

    public function test_a_pending_review_is_hidden_from_other_callers(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();
        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'status' => 'pending',
        ]);

        $this->publicGet('/api/shop/reviews/'.$review->id)->assertStatus(404);
        $this->authenticatedGet($customer, '/api/shop/reviews/'.$review->id)->assertOk();
    }

    public function test_reviewer_names_are_censored_when_the_store_says_so(): void
    {
        [$customer, $product] = $this->seededCustomerAndProduct();
        ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'status' => 'approved',
            'name' => 'John Smith',
        ]);
        \DB::table('core_config')->insert([
            'code' => 'catalog.products.review.censoring_reviewer_name',
            'value' => '1',
            'channel_code' => null,
            'locale_code' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->forgetCoreConfigCache();

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/reviews');

        $response->assertOk();
        expect(array_column($response->json(), 'name'))->toContain('J*** S****');
    }
}
