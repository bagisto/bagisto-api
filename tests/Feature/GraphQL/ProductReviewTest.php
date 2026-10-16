<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\ProductReview;

class ProductReviewTest extends GraphQLTestCase
{
    public function test_create_review_ignores_client_status_and_name(): void
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $product = $this->createBaseProduct('simple');

        $mutation = <<<'GQL'
            mutation createProductReview($input: createProductReviewInput!) {
              createProductReview(input: $input) {
                productReview {
                  _id
                }
              }
            }
        GQL;

        $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [
                'productId' => $product->id,
                'title' => 'Self-approved review',
                'comment' => 'Trying to publish without moderation.',
                'rating' => 5,
                'name' => 'Someone Else',
                'status' => 1,
            ],
        ])->assertOk();

        $review = ProductReview::where('title', 'Self-approved review')->firstOrFail();

        expect($review->status)->toBe('pending');
        expect((int) $review->customer_id)->toBe((int) $customer->id);
        expect($review->name)->toBe($customer->name);
    }

    public function test_create_review_fires_core_create_events(): void
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $product = $this->createBaseProduct('simple');

        $fired = [];

        Event::listen('customer.review.create.before', function ($productId) use (&$fired) {
            $fired['before'] = (int) $productId;
        });

        Event::listen('customer.review.create.after', function ($review) use (&$fired) {
            $fired['after'] = $review->title;
        });

        $mutation = <<<'GQL'
            mutation createProductReview($input: createProductReviewInput!) {
              createProductReview(input: $input) {
                productReview {
                  _id
                }
              }
            }
        GQL;

        $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [
                'productId' => $product->id,
                'title' => 'Evented GraphQL review',
                'comment' => 'Listeners should see this.',
                'rating' => 4,
            ],
        ])->assertOk();

        expect($fired['before'] ?? null)->toBe((int) $product->id);
        expect($fired['after'] ?? null)->toBe('Evented GraphQL review');
    }

    public function test_listing_and_lookup_never_show_a_pending_review(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $product = $this->createBaseProduct('simple');
        $pending = ProductReview::factory()->create(['customer_id' => $customer->id, 'product_id' => $product->id, 'status' => 'pending']);
        ProductReview::factory()->create(['customer_id' => $customer->id, 'product_id' => $product->id, 'status' => 'approved']);

        $list = $this->graphQL('query R($productId: Int) { productReviews(product_id: $productId) { edges { node { _id status } } } }', [
            'productId' => $product->id,
        ]);

        expect($list->json('errors'))->toBeNull();
        expect(array_column(array_column($list->json('data.productReviews.edges'), 'node'), 'status'))->not->toContain('pending');

        $item = $this->graphQL('query R($id: ID!) { productReview(id: $id) { _id } }', ['id' => '/api/shop/reviews/'.$pending->id]);

        expect($item->json('errors'))->not->toBeNull();
    }
}
