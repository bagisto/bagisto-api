<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\MagicAI\Facades\MagicAI;
use Webkul\Product\Models\ProductReview;

class ProductReviewTest extends GraphQLTestCase
{
    /**
     * Test translate approved review through graphql.
     */
    public function test_translate_approved_review_through_graphql(): void
    {
        $this->seedRequiredData();
        $this->enableReviewTranslation();
        $product = $this->createBaseProduct('simple');
        $review = ProductReview::factory()->create([
            'product_id' => $product->id,
            'status' => 'approved',
            'comment' => 'A very nice product.',
        ]);

        MagicAI::shouldReceive('translate')
            ->once()
            ->andReturn('Un très bon produit.');

        $response = $this->graphQL(<<<'GQL'
            query ($productId: Int!, $reviewId: Int!) {
              productReviewTranslation(productId: $productId, reviewId: $reviewId) {
                content
              }
            }
            GQL, [
            'productId' => $product->id,
            'reviewId' => $review->id,
        ]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.productReviewTranslation.content'))->toBe('Un très bon produit.');
    }

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

    /**
     * The owner can update a review's title, comment and rating, while its moderation status stays put.
     */
    public function test_update_own_review_changes_its_fields_but_not_its_status(): void
    {
        $customer = $this->createCustomer();
        $review = $this->createReviewFor($customer, 'pending');

        $response = $this->authenticatedGraphQL($customer, $this->updateReviewMutation(), [
            'input' => [
                'id' => '/api/shop/reviews/'.$review->id,
                'title' => 'Updated title',
                'comment' => 'Updated comment',
                'rating' => 4,
                'status' => 1,
            ],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.updateProductReview.productReview.title'))->toBe('Updated title');

        $review->refresh();

        expect($review->title)->toBe('Updated title');
        expect($review->comment)->toBe('Updated comment');
        expect((int) $review->rating)->toBe(4);
        expect($review->status)->toBe('pending');
    }

    /**
     * A customer cannot update another customer's review.
     */
    public function test_update_another_customers_review_is_refused(): void
    {
        $owner = $this->createCustomer();
        $review = $this->createReviewFor($owner, 'approved');
        $intruder = $this->createCustomer();

        $response = $this->authenticatedGraphQL($intruder, $this->updateReviewMutation(), [
            'input' => [
                'id' => '/api/shop/reviews/'.$review->id,
                'title' => 'Hijacked',
            ],
        ]);

        expect($response->json('errors'))->not->toBeEmpty();
        expect($review->fresh()->title)->not->toBe('Hijacked');
    }

    /**
     * A rating outside one to five is rejected and the review is left unchanged.
     */
    public function test_update_review_rejects_an_out_of_range_rating(): void
    {
        $customer = $this->createCustomer();
        $review = $this->createReviewFor($customer, 'pending');
        $rating = (int) $review->rating;

        $response = $this->authenticatedGraphQL($customer, $this->updateReviewMutation(), [
            'input' => [
                'id' => '/api/shop/reviews/'.$review->id,
                'rating' => 9,
            ],
        ]);

        expect($response->json('errors'))->not->toBeEmpty();
        expect((int) $review->fresh()->rating)->toBe($rating);
    }

    /**
     * The owner can delete their review.
     */
    public function test_delete_own_review_removes_it(): void
    {
        $customer = $this->createCustomer();
        $review = $this->createReviewFor($customer, 'approved');

        $response = $this->authenticatedGraphQL($customer, $this->deleteReviewMutation(), [
            'input' => ['id' => '/api/shop/reviews/'.$review->id],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        $this->assertDatabaseMissing('product_reviews', ['id' => $review->id]);
    }

    /**
     * Deleting a review returns the deleted review, so its fields can be selected.
     */
    public function test_delete_own_review_returns_the_deleted_review(): void
    {
        $customer = $this->createCustomer();
        $review = $this->createReviewFor($customer, 'approved');

        $mutation = <<<'GQL'
            mutation deleteProductReview($input: deleteProductReviewInput!) {
              deleteProductReview(input: $input) {
                productReview { id _id title }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['id' => '/api/shop/reviews/'.$review->id],
        ]);

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.deleteProductReview.productReview'))->toBe([
            'id' => '/api/shop/reviews/'.$review->id,
            '_id' => $review->id,
            'title' => $review->title,
        ]);
        $this->assertDatabaseMissing('product_reviews', ['id' => $review->id]);
    }

    /**
     * Deleting a review that does not exist reports it as not found, not as someone else's review.
     */
    public function test_delete_missing_review_is_reported_as_not_found(): void
    {
        $customer = $this->createCustomer();
        $iri = '/api/shop/reviews/999999999';

        $response = $this->authenticatedGraphQL($customer, $this->deleteReviewMutation(), [
            'input' => ['id' => $iri],
        ]);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.graphql.product-review.item-not-found', ['id' => $iri]));
        expect($response->json('data.deleteProductReview'))->toBeNull();
    }

    /**
     * A customer cannot delete another customer's review.
     */
    public function test_delete_another_customers_review_is_refused(): void
    {
        $owner = $this->createCustomer();
        $review = $this->createReviewFor($owner, 'approved');
        $intruder = $this->createCustomer();

        $response = $this->authenticatedGraphQL($intruder, $this->deleteReviewMutation(), [
            'input' => ['id' => '/api/shop/reviews/'.$review->id],
        ]);

        expect($response->json('errors'))->not->toBeEmpty();
        $this->assertDatabaseHas('product_reviews', ['id' => $review->id]);
    }

    /**
     * A guest cannot delete a review.
     */
    public function test_delete_review_requires_authentication(): void
    {
        $owner = $this->createCustomer();
        $review = $this->createReviewFor($owner, 'approved');

        $response = $this->graphQL($this->deleteReviewMutation(), [
            'input' => ['id' => '/api/shop/reviews/'.$review->id],
        ]);

        expect($response->json('errors'))->not->toBeEmpty();
        $this->assertDatabaseHas('product_reviews', ['id' => $review->id]);
    }

    /**
     * The reviews query lists the approved reviews of the requested product only.
     */
    public function test_product_reviews_lists_only_approved_reviews_of_the_product(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createBaseProduct('simple');
        $otherProduct = $this->createBaseProduct('simple');

        $approved = $this->createReviewFor($customer, 'approved', $product->id);
        $pending = $this->createReviewFor($customer, 'pending', $product->id);
        $foreign = $this->createReviewFor($customer, 'approved', $otherProduct->id);

        $response = $this->graphQL(<<<'GQL'
            query productReviews($productId: Int) {
              productReviews(product_id: $productId, first: 50) {
                edges {
                  node {
                    _id
                    title
                    status
                  }
                }
              }
            }
        GQL, ['productId' => $product->id]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $ids = collect($response->json('data.productReviews.edges'))->pluck('node._id')->map(fn ($id) => (int) $id)->all();

        expect($ids)->toBe([$approved->id]);
        expect($ids)->not->toContain($pending->id);
        expect($ids)->not->toContain($foreign->id);
    }

    /**
     * Last without before returns the final approved reviews of a product.
     */
    public function test_product_reviews_last_returns_the_final_reviews(): void
    {
        [$product, , $second] = $this->createTwoApprovedReviews();

        $response = $this->graphQL(
            'query productReviews($productId: Int) { productReviews(product_id: $productId, last: 1) { edges { cursor node { _id } } pageInfo { hasPreviousPage } } }',
            ['productId' => $product->id]
        );

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and(array_map(fn ($edge) => (int) $edge['node']['_id'], $response->json('data.productReviews.edges')))->toBe([$second->id])
            ->and(array_column($response->json('data.productReviews.edges'), 'cursor'))->toBe([base64_encode('1')])
            ->and($response->json('data.productReviews.pageInfo.hasPreviousPage'))->toBeTrue();
    }

    /**
     * Last with before returns only the reviews that precede the cursor.
     */
    public function test_product_reviews_last_before_stops_at_the_cursor(): void
    {
        [$product, $first] = $this->createTwoApprovedReviews();

        $response = $this->graphQL(
            'query productReviews($productId: Int, $before: String) { productReviews(product_id: $productId, last: 5, before: $before) { edges { node { _id } } } }',
            ['productId' => $product->id, 'before' => base64_encode('1')]
        );

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and(array_map(fn ($edge) => (int) $edge['node']['_id'], $response->json('data.productReviews.edges')))->toBe([$first->id]);
    }

    /**
     * A zero page size returns an empty review page that still reports the total.
     */
    public function test_product_reviews_zero_page_size_returns_an_empty_page(): void
    {
        [$product] = $this->createTwoApprovedReviews();

        $response = $this->graphQL(
            'query productReviews($productId: Int) { productReviews(product_id: $productId, first: 0) { edges { node { _id } } totalCount } }',
            ['productId' => $product->id]
        );

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.productReviews.edges'))->toBe([])
            ->and($response->json('data.productReviews.totalCount'))->toBe(2);
    }

    /**
     * Enable review translation.
     */
    private function enableReviewTranslation(): void
    {
        foreach ([
            'magic_ai.general.settings.enabled' => '1',
            'magic_ai.storefront_features.review_translation.enabled' => '1',
        ] as $code => $value) {
            DB::table('core_config')->where('code', $code)->delete();
            DB::table('core_config')->insert([
                'code' => $code,
                'value' => $value,
                'channel_code' => core()->getRequestedChannelCode(),
                'locale_code' => core()->getRequestedLocaleCode(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->forgetCoreConfigCache();
    }

    /**
     * Create a review owned by the customer.
     */
    private function createReviewFor(Customer $customer, string $status, ?int $productId = null): ProductReview
    {
        return ProductReview::factory()->create([
            'product_id' => $productId ?? $this->createBaseProduct('simple')->id,
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'title' => 'Original title',
            'comment' => 'Original comment',
            'rating' => 3,
            'status' => $status,
        ]);
    }

    /**
     * Create a product with two approved reviews, returning the product and both reviews.
     */
    private function createTwoApprovedReviews(): array
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer();
        $product = $this->createBaseProduct('simple');

        return [
            $product,
            $this->createReviewFor($customer, 'approved', $product->id),
            $this->createReviewFor($customer, 'approved', $product->id),
        ];
    }

    /**
     * The update review mutation.
     */
    private function updateReviewMutation(): string
    {
        return <<<'GQL'
            mutation updateProductReview($input: updateProductReviewInput!) {
              updateProductReview(input: $input) {
                productReview {
                  _id
                  title
                  rating
                  status
                }
              }
            }
        GQL;
    }

    /**
     * The delete review mutation.
     */
    private function deleteReviewMutation(): string
    {
        return <<<'GQL'
            mutation deleteProductReview($input: deleteProductReviewInput!) {
              deleteProductReview(input: $input) {
                productReview {
                  id
                }
              }
            }
        GQL;
    }
}
