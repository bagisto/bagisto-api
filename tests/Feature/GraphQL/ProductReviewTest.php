<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

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
}
