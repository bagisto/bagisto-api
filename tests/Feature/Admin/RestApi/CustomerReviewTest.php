<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductReview;
use Webkul\Product\Models\ProductReviewAttachment;

class CustomerReviewTest extends AdminApiTestCase
{
    /**
     * Test listing requires auth.
     */
    public function test_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/customers/reviews')->assertStatus(401);
    }

    /**
     * Test detail requires auth.
     */
    public function test_detail_requires_auth(): void
    {
        $r = $this->seedReview();
        $this->publicGet('/api/admin/customers/reviews/'.$r->id)->assertStatus(401);
    }

    /**
     * Test update requires auth.
     */
    public function test_update_requires_auth(): void
    {
        $r = $this->seedReview();
        $this->putJson('/api/admin/customers/reviews/'.$r->id, ['status' => 'approved'])->assertStatus(401);
    }

    /**
     * Test delete requires auth.
     */
    public function test_delete_requires_auth(): void
    {
        $r = $this->seedReview();
        $this->deleteJson('/api/admin/customers/reviews/'.$r->id)->assertStatus(401);
    }

    /**
     * Test listing returns envelope.
     */
    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/customers/reviews');
        $resp->assertOk();
        expect($resp->json())->toHaveKeys(['data', 'meta']);
        expect($resp->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total']);
    }

    /**
     * Test listing returns seeded review.
     */
    public function test_listing_returns_seeded_review(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $resp = $this->adminGet($admin, '/api/admin/customers/reviews?per_page=50');
        $resp->assertOk();
        $row = collect($resp->json('data'))->firstWhere('id', $r->id);
        expect($row)->not()->toBeNull();
        expect($row)->toHaveKeys(['title', 'comment', 'rating', 'status', 'product', 'customer']);
        expect($row['product'])->toHaveKeys(['id', 'name', 'sku']);
        expect($row['customer'])->toHaveKeys(['id', 'name', 'email']);
        expect($row['product']['id'])->toBe($r->product_id);
        expect($row['customer']['id'])->toBe($r->customer_id);
    }

    /**
     * Test listing filter by status.
     */
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

    /**
     * Test listing filter by rating.
     */
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

    /**
     * Test listing per page capped.
     */
    public function test_listing_per_page_capped(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/customers/reviews?per_page=9999');
        $resp->assertOk();
        expect($resp->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    /**
     * Test detail returns review.
     */
    public function test_detail_returns_review(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $resp = $this->adminGet($admin, '/api/admin/customers/reviews/'.$r->id);
        $resp->assertOk();
        expect($resp->json('id'))->toBe($r->id);
        expect($resp->json('status'))->toBe('pending');
        expect($resp->json())->toHaveKeys(['product', 'customer', 'images']);
        expect($resp->json('product'))->toHaveKeys(['id', 'name', 'sku']);
        expect($resp->json('customer'))->toHaveKeys(['id', 'name', 'email']);
        expect($resp->json('product.id'))->toBe($r->product_id);
        expect($resp->json('customer.id'))->toBe($r->customer_id);
        expect($resp->json('customer.email'))->not()->toBeNull();
        expect($resp->json('images'))->toBeArray();
    }

    /**
     * Test detail images array.
     */
    public function test_detail_images_array(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        ProductReviewAttachment::create([
            'review_id' => $r->id,
            'path' => 'review/'.$r->id.'/img.png',
            'type' => 'image',
            'mime_type' => 'image/png',
        ]);

        $resp = $this->adminGet($admin, '/api/admin/customers/reviews/'.$r->id);
        $resp->assertOk();
        $images = $resp->json('images');
        expect($images)->toBeArray();
        expect($images)->not()->toBeEmpty();
        expect($images[0])->toHaveKeys(['id', 'path', 'url']);
        expect($images[0]['path'])->toBe('review/'.$r->id.'/img.png');
    }

    /**
     * Test detail unknown returns 404.
     */
    public function test_detail_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/customers/reviews/9999999')->assertStatus(404);
    }

    /**
     * Test update status approved.
     */
    public function test_update_status_approved(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview(['status' => 'pending']);

        $resp = $this->adminPut($admin, '/api/admin/customers/reviews/'.$r->id, ['status' => 'approved']);
        $resp->assertOk();
        expect($resp->json('status'))->toBe('approved');
        expect(ProductReview::find($r->id)->status)->toBe('approved');
    }

    /**
     * Test update status disapproved.
     */
    public function test_update_status_disapproved(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview(['status' => 'pending']);

        $resp = $this->adminPut($admin, '/api/admin/customers/reviews/'.$r->id, ['status' => 'disapproved']);
        $resp->assertOk();
        expect(ProductReview::find($r->id)->status)->toBe('disapproved');
    }

    /**
     * Test update status invalid value 422.
     */
    public function test_update_status_invalid_value_422(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $resp = $this->adminPut($admin, '/api/admin/customers/reviews/'.$r->id, ['status' => 'bogus']);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test update missing status 422.
     */
    public function test_update_missing_status_422(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $resp = $this->adminPut($admin, '/api/admin/customers/reviews/'.$r->id, []);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test update unknown id 404.
     */
    public function test_update_unknown_id_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPut($admin, '/api/admin/customers/reviews/9999999', ['status' => 'approved']);
        expect($resp->getStatusCode())->toBe(404);
    }

    /**
     * Test delete review.
     */
    public function test_delete_review(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedReview();

        $resp = $this->adminDelete($admin, '/api/admin/customers/reviews/'.$r->id);
        expect(in_array($resp->getStatusCode(), [200, 204]))->toBeTrue();
        expect(ProductReview::find($r->id))->toBeNull();
    }

    /**
     * Test delete unknown 404.
     */
    public function test_delete_unknown_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminDelete($admin, '/api/admin/customers/reviews/9999999');
        expect($resp->getStatusCode())->toBe(404);
    }

    /**
     * Test mass delete.
     */
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

    /**
     * Test mass delete empty indices 422.
     */
    public function test_mass_delete_empty_indices_422(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers/reviews/mass-delete', ['indices' => []]);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test mass update status.
     */
    public function test_mass_update_status(): void
    {
        $admin = $this->createAdmin();
        $r1 = $this->seedReview(['status' => 'pending']);
        $r2 = $this->seedReview(['status' => 'pending']);

        $resp = $this->adminPost($admin, '/api/admin/customers/reviews/mass-update-status', [
            'indices' => [$r1->id, $r2->id],
            'value' => 'approved',
        ]);
        $resp->assertOk();
        expect($resp->json('value'))->toBe('approved');
        expect(ProductReview::find($r1->id)->status)->toBe('approved');
        expect(ProductReview::find($r2->id)->status)->toBe('approved');
    }

    /**
     * Test mass update status invalid value 422.
     */
    public function test_mass_update_status_invalid_value_422(): void
    {
        $admin = $this->createAdmin();
        $r1 = $this->seedReview();

        $resp = $this->adminPost($admin, '/api/admin/customers/reviews/mass-update-status', [
            'indices' => [$r1->id],
            'value' => 'bogus',
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test mass update status empty indices 422.
     */
    public function test_mass_update_status_empty_indices_422(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/customers/reviews/mass-update-status', [
            'indices' => [],
            'value' => 'approved',
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Admin put.
     */
    protected function adminPut($admin, string $url, array $data = [], ?string $token = null): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    /**
     * Admin delete.
     */
    protected function adminDelete($admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    /**
     * Test the nested product name follows the admin's locale, not the first flat row stored.
     */
    public function test_review_product_name_follows_the_admin_locale(): void
    {
        $review = $this->seedReview(['status' => 'approved']);

        $product = Product::query()->findOrFail($review->product_id);

        $flat = [
            'product_id' => $product->id,
            'channel' => 'default',
            'sku' => $product->sku,
            'type' => 'simple',
            'status' => 1,
            'attribute_family_id' => (int) (DB::table('attribute_families')->value('id') ?? 1),
        ];

        DB::table('product_flat')->where('product_id', $product->id)->delete();

        DB::table('product_flat')->insert(array_merge($flat, ['locale' => 'ar', 'name' => '[AR] Arabic name']));
        DB::table('product_flat')->insert(array_merge($flat, ['locale' => app()->getLocale(), 'name' => 'Admin locale name']));

        $admin = $this->createAdmin();

        $this->adminGet($admin, '/api/admin/customers/reviews/'.$review->id)
            ->assertOk()
            ->assertJsonPath('product.name', 'Admin locale name');
    }

    /**
     * Seed product.
     */
    protected function seedProduct(): Product
    {
        return $this->createBaseProduct('simple');
    }

    /**
     * Seed customer for review.
     */
    protected function seedCustomerForReview(): Customer
    {
        $this->seedRequiredData();
        $group = CustomerGroup::where('code', 'general')->first();

        return Customer::factory()->create([
            'customer_group_id' => $group->id,
            'status' => 1,
        ]);
    }

    /**
     * Seed review.
     */
    protected function seedReview(array $overrides = []): ProductReview
    {
        $product = $this->seedProduct();
        $customer = $this->seedCustomerForReview();

        return ProductReview::factory()->create(array_merge([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'name' => $customer->first_name.' '.$customer->last_name,
            'status' => 'pending',
            'rating' => 4,
        ], $overrides));
    }
}
