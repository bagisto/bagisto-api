<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\ProductReview;

class ProductReviewTest extends GraphQLTestCase
{
    /**
     * A created review stays pending and takes the customer's id and name, ignoring the client's status and name.
     */
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

    /**
     * The single-review query never returns a review that is not approved.
     */
    public function test_pending_review_is_not_readable_by_id(): void
    {
        $this->seedRequiredData();

        $review = ProductReview::factory()->create([
            'product_id' => $this->createBaseProduct('simple')->id,
            'status' => 'pending',
        ]);

        $response = $this->graphQL(<<<GQL
            query {
              productReview(id: "/api/shop/reviews/{$review->id}") {
                _id
              }
            }
        GQL);

        expect($response->json('data.productReview'))->toBeNull();
    }
}
