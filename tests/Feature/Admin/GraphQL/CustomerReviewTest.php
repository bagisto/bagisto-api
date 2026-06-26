<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductReview;

/**
 * GraphQL coverage for Admin Customer Reviews moderation (Block C C3).
 */
class CustomerReviewTest extends AdminApiTestCase
{
    protected function seedProduct(): Product
    {
        return $this->createBaseProduct('simple');
    }

    protected function seedReview(array $overrides = []): ProductReview
    {
        $this->seedRequiredData();
        $group = CustomerGroup::where('code', 'general')->first();
        $customer = Customer::factory()->create([
            'customer_group_id' => $group->id,
            'status'            => 1,
        ]);
        $product = $this->seedProduct();

        return ProductReview::factory()->create(array_merge([
            'product_id'  => $product->id,
            'customer_id' => $customer->id,
            'name'        => $customer->first_name.' '.$customer->last_name,
            'status'      => 'pending',
            'rating'      => 4,
        ], $overrides));
    }

    public function test_listing(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $query = <<<'GQL'
            query {
              adminCustomerReviews(first: 50) {
                edges { node { id _id status } }
                totalCount
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, [], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $resp->json('data.adminCustomerReviews.edges') ?? []);
        expect($ids)->toContain($r->id);
    }

    public function test_listing_resolves_guest_review_with_null_customer(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview(['customer_id' => null, 'name' => 'Guest Reviewer']);

        $query = <<<'GQL'
            query {
              adminCustomerReviews(first: 50) {
                edges {
                  node {
                    _id
                    name
                    product {
                      _id
                      name
                      sku
                    }
                    customer {
                      _id
                      name
                      email
                    }
                  }
                }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, [], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        $node = collect($resp->json('data.adminCustomerReviews.edges'))
            ->first(fn ($e) => ($e['node']['_id'] ?? null) === $r->id);
        expect($node)->not->toBeNull();
        expect($node['node']['customer'])->toBeNull();
        expect($node['node']['product'])->not->toBeNull();
    }

    public function test_listing_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        $pending = $this->seedReview(['status' => 'pending']);
        $approved = $this->seedReview(['status' => 'approved']);

        $query = <<<'GQL'
            query($status: String) {
              adminCustomerReviews(first: 50, status: $status) {
                edges { node { _id } }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['status' => 'approved'], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $resp->json('data.adminCustomerReviews.edges') ?? []);
        expect($ids)->toContain($approved->id);
        expect($ids)->not()->toContain($pending->id);
    }

    public function test_listing_requires_auth(): void
    {
        $query = 'query { adminCustomerReviews(first: 5) { edges { node { _id } } } }';
        $resp = $this->adminGraphQL($query);
        $resp->assertOk();
        expect($resp->json('errors'))->not()->toBeNull();
    }

    public function test_detail(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $query = <<<'GQL'
            query($id: ID!) { adminCustomerReview(id: $id) { id _id } }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/customers/reviews/'.$r->id], $admin);
        $resp->assertOk();
        expect($resp->json('data.adminCustomerReview._id'))->toBe($r->id);
    }

    public function test_detail_resolves_nested_objects_and_connection(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $query = <<<'GQL'
            query($id: ID!) {
              adminCustomerReview(id: $id) {
                _id
                title
                rating
                status
                product { _id name sku }
                customer { _id name email }
                images { edges { node { _id path url } } }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/customers/reviews/'.$r->id], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();

        $node = $resp->json('data.adminCustomerReview');
        expect($node['_id'])->toBe($r->id);
        expect($node['product']['_id'])->toBe($r->product_id);
        expect($node['product']['sku'])->not()->toBeNull();
        expect($node['customer']['_id'])->toBe($r->customer_id);
        expect($node['customer']['email'])->not()->toBeNull();
        expect($node['images'])->toHaveKey('edges');
    }

    public function test_detail_resolves_image_connection_nodes(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        \Webkul\Product\Models\ProductReviewAttachment::create([
            'review_id' => $r->id,
            'path'      => 'review/'.$r->id.'/img.png',
            'type'      => 'image',
            'mime_type' => 'image/png',
        ]);

        $query = <<<'GQL'
            query($id: ID!) {
              adminCustomerReview(id: $id) {
                images { edges { node { _id path url } } }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/customers/reviews/'.$r->id], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();

        $edges = $resp->json('data.adminCustomerReview.images.edges') ?? [];
        expect($edges)->not()->toBeEmpty();
        expect($edges[0]['node']['path'])->toBe('review/'.$r->id.'/img.png');
        expect($edges[0]['node']['_id'])->not()->toBeNull();
    }

    public function test_detail_unknown(): void
    {
        $admin = $this->createAdmin();
        $query = <<<'GQL'
            query($id: ID!) { adminCustomerReview(id: $id) { id _id } }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/customers/reviews/99999999'], $admin);
        $resp->assertOk();
        $errors = $resp->json('errors');
        $dataNull = $resp->json('data.adminCustomerReview') === null;
        expect($errors !== null || $dataNull)->toBeTrue();
    }

    public function test_mutation_update_status(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview(['status' => 'pending']);

        $mutation = <<<'GQL'
            mutation($input: updateAdminCustomerReviewInput!) {
              updateAdminCustomerReview(input: $input) {
                adminCustomerReview { _id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/customers/reviews/'.$r->id, 'status' => 'approved'],
        ], $admin);
        $resp->assertOk();
        expect(ProductReview::find($r->id)->status)->toBe('approved');
    }

    public function test_mutation_update_invalid_status(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $mutation = <<<'GQL'
            mutation($input: updateAdminCustomerReviewInput!) {
              updateAdminCustomerReview(input: $input) {
                adminCustomerReview { _id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/customers/reviews/'.$r->id, 'status' => 'bogus'],
        ], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_delete(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCustomerReviewInput!) {
              deleteAdminCustomerReview(input: $input) {
                adminCustomerReview {
                  id
                  _id
                  status
                  message
                }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/customers/reviews/'.$r->id],
        ], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        expect((int) $resp->json('data.deleteAdminCustomerReview.adminCustomerReview._id'))->toBe($r->id);
        expect($resp->json('data.deleteAdminCustomerReview.adminCustomerReview.message'))->not()->toBeNull();
        expect(ProductReview::find($r->id))->toBeNull();
    }

    public function test_mutation_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $r1 = $this->seedReview();
        $r2 = $this->seedReview();

        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerReviewMassDeleteInput!) {
              createAdminCustomerReviewMassDelete(input: $input) {
                adminCustomerReviewMassDelete { _id deleted }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['indices' => [$r1->id, $r2->id]],
        ], $admin);
        $resp->assertOk();
        expect(ProductReview::find($r1->id))->toBeNull();
        expect(ProductReview::find($r2->id))->toBeNull();
    }

    public function test_mutation_mass_update_status(): void
    {
        $admin = $this->createAdmin();
        $r1 = $this->seedReview(['status' => 'pending']);
        $r2 = $this->seedReview(['status' => 'pending']);

        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerReviewMassUpdateStatusInput!) {
              createAdminCustomerReviewMassUpdateStatus(input: $input) {
                adminCustomerReviewMassUpdateStatus { _id value }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['indices' => [$r1->id, $r2->id], 'value' => 'approved'],
        ], $admin);
        $resp->assertOk();
        expect(ProductReview::find($r1->id)->status)->toBe('approved');
        expect(ProductReview::find($r2->id)->status)->toBe('approved');
    }
}
