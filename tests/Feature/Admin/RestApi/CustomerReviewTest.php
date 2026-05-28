<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductReview;

/**
 * REST coverage for Admin Customer Reviews moderation (Block C C3).
 */
class CustomerReviewTest extends AdminApiTestCase
{
    protected function adminPut($admin, string $url, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete($admin, string $url, ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function seedProduct(): Product
    {
        return $this->createBaseProduct('simple');
    }

    protected function seedCustomerForReview(): Customer
    {
        $this->seedRequiredData();
        $group = CustomerGroup::where('code', 'general')->first();

        return Customer::factory()->create([
            'customer_group_id' => $group->id,
            'status'            => 1,
        ]);
    }

    protected function seedReview(array $overrides = []): ProductReview
    {
        $product = $this->seedProduct();
        $customer = $this->seedCustomerForReview();

        return ProductReview::factory()->create(array_merge([
            'product_id'  => $product->id,
            'customer_id' => $customer->id,
            'name'        => $customer->first_name.' '.$customer->last_name,
            'status'      => 'pending',
            'rating'      => 4,
        ], $overrides));
    }

    public function test_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/customers/reviews')->assertStatus(401);
    }

    public function test_detail_requires_auth(): void
    {
        $r = $this->seedReview();
        $this->publicGet('/api/admin/customers/reviews/'.$r->id)->assertStatus(401);
    }

    public function test_update_requires_auth(): void
    {
        $r = $this->seedReview();
        $this->putJson('/api/admin/customers/reviews/'.$r->id, ['status' => 'approved'])->assertStatus(401);
    }

    public function test_delete_requires_auth(): void
    {
        $r = $this->seedReview();
        $this->deleteJson('/api/admin/customers/reviews/'.$r->id)->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/customers/reviews');
        $resp->assertOk();
        expect($resp->json())->toHaveKeys(['data', 'meta']);
        expect($resp->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total']);
    }

    public function test_listing_returns_seeded_review(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $resp = $this->adminGet($admin, '/api/admin/customers/reviews?per_page=50');
        $resp->assertOk();
        $row = collect($resp->json('data'))->firstWhere('id', $r->id);
        expect($row)->not()->toBeNull();
        expect($row)->toHaveKeys(['title', 'comment', 'rating', 'status', 'productId', 'customerId']);
    }

    public function test_listing_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        $pending = $this->seedReview(['status' => 'pending']);
        $approved = $this->seedReview(['status' => 'approved']);

        $resp = $this->adminGet($admin, '/api/admin/customers/reviews?status=approved&per_page=50');
        $resp->assertOk();
        $ids = collect($resp->json('data'))->pluck('id')->all();
        expect($ids)->toContain($approved->id);
        expect($ids)->not()->toContain($pending->id);
    }

    public function test_listing_filter_by_rating(): void
    {
        $admin = $this->createAdmin();
        $r3 = $this->seedReview(['rating' => 3]);
        $r5 = $this->seedReview(['rating' => 5]);

        $resp = $this->adminGet($admin, '/api/admin/customers/reviews?rating=5&per_page=50');
        $resp->assertOk();
        $ids = collect($resp->json('data'))->pluck('id')->all();
        expect($ids)->toContain($r5->id);
        expect($ids)->not()->toContain($r3->id);
    }

    public function test_listing_per_page_capped(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/customers/reviews?per_page=9999');
        $resp->assertOk();
        expect($resp->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_detail_returns_review(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $resp = $this->adminGet($admin, '/api/admin/customers/reviews/'.$r->id);
        $resp->assertOk();
        expect($resp->json('id'))->toBe($r->id);
        expect($resp->json('status'))->toBe('pending');
        expect($resp->json())->toHaveKeys(['productId', 'customerId', 'productSku', 'customerEmail']);
    }

    public function test_detail_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/customers/reviews/9999999')->assertStatus(404);
    }

    public function test_update_status_approved(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview(['status' => 'pending']);

        $resp = $this->adminPut($admin, '/api/admin/customers/reviews/'.$r->id, ['status' => 'approved']);
        $resp->assertOk();
        expect($resp->json('status'))->toBe('approved');
        expect(ProductReview::find($r->id)->status)->toBe('approved');
    }

    public function test_update_status_disapproved(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview(['status' => 'pending']);

        $resp = $this->adminPut($admin, '/api/admin/customers/reviews/'.$r->id, ['status' => 'disapproved']);
        $resp->assertOk();
        expect(ProductReview::find($r->id)->status)->toBe('disapproved');
    }

    public function test_update_status_invalid_value_422(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $resp = $this->adminPut($admin, '/api/admin/customers/reviews/'.$r->id, ['status' => 'bogus']);
        expect($resp->getStatusCode())->toBe(422);
    }

    public function test_update_missing_status_422(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $resp = $this->adminPut($admin, '/api/admin/customers/reviews/'.$r->id, []);
        expect($resp->getStatusCode())->toBe(422);
    }

    public function test_update_unknown_id_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPut($admin, '/api/admin/customers/reviews/9999999', ['status' => 'approved']);
        expect($resp->getStatusCode())->toBe(404);
    }

    public function test_delete_review(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $resp = $this->adminDelete($admin, '/api/admin/customers/reviews/'.$r->id);
        expect(in_array($resp->getStatusCode(), [200, 204]))->toBeTrue();
        expect(ProductReview::find($r->id))->toBeNull();
    }

    public function test_delete_unknown_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminDelete($admin, '/api/admin/customers/reviews/9999999');
        expect($resp->getStatusCode())->toBe(404);
    }

    public function test_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $r1 = $this->seedReview();
        $r2 = $this->seedReview();

        $resp = $this->adminPost($admin, '/api/admin/customers/reviews/mass-delete', [
            'indices' => [$r1->id, $r2->id],
        ]);
        $resp->assertOk();
        expect($resp->json('deleted'))->toBeArray();
        expect(ProductReview::find($r1->id))->toBeNull();
        expect(ProductReview::find($r2->id))->toBeNull();
    }

    public function test_mass_delete_empty_indices_422(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers/reviews/mass-delete', ['indices' => []]);
        expect($resp->getStatusCode())->toBe(422);
    }

    public function test_mass_update_status(): void
    {
        $admin = $this->createAdmin();
        $r1 = $this->seedReview(['status' => 'pending']);
        $r2 = $this->seedReview(['status' => 'pending']);

        $resp = $this->adminPost($admin, '/api/admin/customers/reviews/mass-update-status', [
            'indices' => [$r1->id, $r2->id],
            'value'   => 'approved',
        ]);
        $resp->assertOk();
        expect($resp->json('value'))->toBe('approved');
        expect(ProductReview::find($r1->id)->status)->toBe('approved');
        expect(ProductReview::find($r2->id)->status)->toBe('approved');
    }

    public function test_mass_update_status_invalid_value_422(): void
    {
        $admin = $this->createAdmin();
        $r1 = $this->seedReview();

        $resp = $this->adminPost($admin, '/api/admin/customers/reviews/mass-update-status', [
            'indices' => [$r1->id],
            'value'   => 'bogus',
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }

    public function test_mass_update_status_empty_indices_422(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers/reviews/mass-update-status', [
            'indices' => [],
            'value'   => 'approved',
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }
}
