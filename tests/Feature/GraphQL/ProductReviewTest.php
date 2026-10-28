<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
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

    public function test_reviews_carry_the_reviewer_image_and_the_default_image_for_a_guest(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        DB::table('customers')->where('id', $customer->id)->update(['image' => 'customer/'.$customer->id.'/avatar.webp']);
        $product = $this->createBaseProduct('simple');

        $withImage = ProductReview::factory()->create(['customer_id' => $customer->id, 'product_id' => $product->id, 'status' => 'approved']);
        $guest = ProductReview::factory()->create(['customer_id' => null, 'name' => 'Guest Reviewer', 'product_id' => $product->id, 'status' => 'approved']);

        $list = $this->graphQL('query R($productId: Int) { productReviews(product_id: $productId) { edges { node { _id name customerImageUrl } } } }', [
            'productId' => $product->id,
        ]);

        expect($list->json('errors'))->toBeNull();

        $byId = collect($list->json('data.productReviews.edges'))->pluck('node')->keyBy('_id');

        expect($byId[$withImage->id]['customerImageUrl'])->toContain('customer/'.$customer->id.'/avatar.webp');
        expect($byId[$guest->id]['customerImageUrl'])->toBe(bagisto_asset('images/user-placeholder.png', 'shop'));

        $item = $this->graphQL('query R($id: ID!) { productReview(id: $id) { _id customerImageUrl } }', ['id' => '/api/shop/reviews/'.$guest->id]);

        expect($item->json('errors'))->toBeNull();
        expect($item->json('data.productReview.customerImageUrl'))->toBe(bagisto_asset('images/user-placeholder.png', 'shop'));

        $connection = $this->graphQL('query P($id: ID!) { product(id: $id) { reviews { edges { node { _id customerImageUrl } } } } }', [
            'id' => '/api/shop/products/'.$product->id,
        ]);

        expect($connection->json('errors'))->toBeNull();

        $nodes = collect($connection->json('data.product.reviews.edges'))->pluck('node')->keyBy('_id');

        expect($nodes[$withImage->id]['customerImageUrl'])->toContain('avatar.webp');
        expect($nodes[$guest->id]['customerImageUrl'])->toBe(bagisto_asset('images/user-placeholder.png', 'shop'));
    }
}
