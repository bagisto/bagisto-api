<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for the admin Settings → Tax Rates CRUD endpoints (Block B Wave 3).
 *
 *   GET    /api/admin/settings/tax-rates
 *   GET    /api/admin/settings/tax-rates/{id}
 *   POST   /api/admin/settings/tax-rates
 *   PUT    /api/admin/settings/tax-rates/{id}
 *   DELETE /api/admin/settings/tax-rates/{id}
 */
class SettingsTaxRateTest extends AdminApiTestCase
{
    // -------------------------------------------------------------------------
    // Seed helpers
    // -------------------------------------------------------------------------

    protected function insertTaxRate(array $overrides = []): int
    {
        return \DB::table('tax_rates')->insertGetId(array_merge([
            'identifier' => 'TR-'.uniqid(),
            'is_zip'     => 0,
            'zip_code'   => '12345',
            'zip_from'   => null,
            'zip_to'     => null,
            'state'      => 'CA',
            'country'    => 'US',
            'tax_rate'   => 8.5,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function adminPut(\Webkul\User\Models\Admin $admin, string $url, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin));
    }

    protected function adminDelete(\Webkul\User\Models\Admin $admin, string $url): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin));
    }

    // -------------------------------------------------------------------------
    // Auth guards
    // -------------------------------------------------------------------------

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/settings/tax-rates')->assertStatus(401);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->postJson('/api/admin/settings/tax-rates', [])->assertStatus(401);
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertTaxRate();
        $this->deleteJson('/api/admin/settings/tax-rates/'.$id)->assertStatus(401);
    }

    public function test_update_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertTaxRate();
        $this->putJson('/api/admin/settings/tax-rates/'.$id, [])->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Listing — envelope, filters, sort
    // -------------------------------------------------------------------------

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminGet($admin, '/api/admin/settings/tax-rates');

        $r->assertOk();
        expect($r->json('data'))->toBeArray();
        expect($r->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total']);
        expect($r->json('meta.currentPage'))->toBe(1);
        expect($r->json('meta.perPage'))->toBe(10);
    }

    public function test_listing_returns_seeded_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate(['identifier' => 'UNIQUE-LISTING-'.uniqid()]);

        $r = $this->adminGet($admin, '/api/admin/settings/tax-rates?per_page=50');
        $r->assertOk();
        $row = collect($r->json('data'))->firstWhere('id', $id);

        expect($row)->not()->toBeNull();
        expect($row)->toHaveKeys(['id', 'identifier', 'isZip', 'zipCode', 'zipFrom', 'zipTo', 'state', 'country', 'taxRate', 'createdAt', 'updatedAt']);
        expect((float) $row['taxRate'])->toBe(8.5);
    }

    public function test_listing_filter_by_identifier(): void
    {
        $admin = $this->createAdmin();
        $token = 'IDFILTER-'.uniqid();
        $id = $this->insertTaxRate(['identifier' => $token]);
        $other = $this->insertTaxRate(['identifier' => 'OTHER-'.uniqid()]);

        $r = $this->adminGet($admin, '/api/admin/settings/tax-rates?identifier='.$token.'&per_page=50');
        $r->assertOk();
        $ids = collect($r->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
        expect($ids)->not()->toContain($other);
    }

    public function test_listing_filter_by_country(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertTaxRate(['country' => 'FR']);
        $id2 = $this->insertTaxRate(['country' => 'DE']);

        $r = $this->adminGet($admin, '/api/admin/settings/tax-rates?country=FR&per_page=50');
        $r->assertOk();
        $ids = collect($r->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_listing_filter_by_tax_rate_range(): void
    {
        $admin = $this->createAdmin();
        $idLo = $this->insertTaxRate(['tax_rate' => 1.0]);
        $idMid = $this->insertTaxRate(['tax_rate' => 10.0]);
        $idHi = $this->insertTaxRate(['tax_rate' => 50.0]);

        $r = $this->adminGet($admin, '/api/admin/settings/tax-rates?tax_rate_from=5&tax_rate_to=20&per_page=50');
        $r->assertOk();
        $ids = collect($r->json('data'))->pluck('id')->all();
        expect($ids)->toContain($idMid);
        expect($ids)->not()->toContain($idLo);
        expect($ids)->not()->toContain($idHi);
    }

    public function test_listing_sort_by_tax_rate_asc(): void
    {
        $admin = $this->createAdmin();
        // Reduce noise by using deterministic identifiers and filtering by a fresh country.
        $country = 'Z'.chr(rand(65, 90));
        $this->insertTaxRate(['country' => $country, 'tax_rate' => 9.99]);
        $this->insertTaxRate(['country' => $country, 'tax_rate' => 0.11]);
        $this->insertTaxRate(['country' => $country, 'tax_rate' => 4.44]);

        $r = $this->adminGet($admin, '/api/admin/settings/tax-rates?country='.$country.'&sort=tax_rate-asc&per_page=50');
        $r->assertOk();
        $rates = collect($r->json('data'))->pluck('taxRate')->map(fn ($v) => (float) $v)->all();
        $sorted = $rates;
        sort($sorted);
        expect($rates)->toBe($sorted);
    }

    public function test_listing_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminGet($admin, '/api/admin/settings/tax-rates?per_page=9999');
        $r->assertOk();
        expect($r->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function test_detail_returns_full_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate(['identifier' => 'DETAIL-'.uniqid(), 'tax_rate' => 7.25]);

        $r = $this->adminGet($admin, '/api/admin/settings/tax-rates/'.$id);
        $r->assertOk();
        expect($r->json('id'))->toBe($id);
        expect((float) $r->json('taxRate'))->toBe(7.25);
        expect($r->json('isZip'))->toBeFalse();
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/settings/tax-rates/9999999')->assertStatus(404);
    }

    public function test_detail_zip_range_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate([
            'is_zip'   => 1,
            'zip_code' => null,
            'zip_from' => '90000',
            'zip_to'   => '90999',
        ]);

        $r = $this->adminGet($admin, '/api/admin/settings/tax-rates/'.$id);
        $r->assertOk();
        expect($r->json('isZip'))->toBeTrue();
        expect($r->json('zipFrom'))->toBe('90000');
        expect($r->json('zipTo'))->toBe('90999');
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_specific_zip_happy_path(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'CREATE-SP-'.uniqid(),
            'is_zip'     => false,
            'zip_code'   => '94103',
            'state'      => 'CA',
            'country'    => 'US',
            'tax_rate'   => 8.5,
        ]);
        $r->assertStatus(201);
        expect($r->json('id'))->toBeInt();
        expect($r->json('isZip'))->toBeFalse();
        expect($r->json('zipCode'))->toBe('94103');
        expect(\DB::table('tax_rates')->where('id', $r->json('id'))->exists())->toBeTrue();
    }

    public function test_create_zip_range_happy_path(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'CREATE-RG-'.uniqid(),
            'is_zip'     => true,
            'zip_from'   => '94000',
            'zip_to'     => '94999',
            'state'      => 'CA',
            'country'    => 'US',
            'tax_rate'   => 9.0,
        ]);
        $r->assertStatus(201);
        expect($r->json('isZip'))->toBeTrue();
        expect($r->json('zipFrom'))->toBe('94000');
        expect($r->json('zipTo'))->toBe('94999');
    }

    public function test_create_missing_identifier_returns_422(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'is_zip' => false, 'zip_code' => '94103', 'country' => 'US', 'tax_rate' => 8.5,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_create_duplicate_identifier_returns_422(): void
    {
        $admin = $this->createAdmin();
        $dup = 'DUP-'.uniqid();
        $this->insertTaxRate(['identifier' => $dup]);

        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => $dup,
            'is_zip'     => false, 'zip_code' => '94103', 'country' => 'US', 'tax_rate' => 8.5,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_create_country_must_be_2_letters(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'BAD-COUNTRY-'.uniqid(),
            'is_zip'     => false, 'zip_code' => '94103', 'country' => 'USA', 'tax_rate' => 8.5,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_create_tax_rate_non_numeric_rejected(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'BAD-RATE-'.uniqid(),
            'is_zip'     => false, 'zip_code' => '94103', 'country' => 'US', 'tax_rate' => 'abc',
        ]);
        // Either 422 (validator) or 400/500 (DTO denormalization rejects non-numeric).
        expect(in_array($r->getStatusCode(), [400, 422, 500], true))->toBeTrue();
        expect($r->getStatusCode())->not()->toBe(201);
    }

    public function test_create_tax_rate_out_of_range_returns_422(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'OOR-'.uniqid(),
            'is_zip'     => false, 'zip_code' => '94103', 'country' => 'US', 'tax_rate' => 150,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_create_is_zip_false_requires_zip_code(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'NO-ZIP-CODE-'.uniqid(),
            'is_zip'     => false,
            'country'    => 'US', 'tax_rate' => 8.5,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_create_is_zip_true_requires_range(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'NO-RANGE-'.uniqid(),
            'is_zip'     => true,
            'country'    => 'US', 'tax_rate' => 8.5,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_create_is_zip_true_missing_zip_to_returns_422(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'NO-TO-'.uniqid(),
            'is_zip'     => true,
            'zip_from'   => '94000',
            'country'    => 'US', 'tax_rate' => 8.5,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_changes_tax_rate(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate();
        $r = $this->adminPut($admin, '/api/admin/settings/tax-rates/'.$id, ['tax_rate' => 12.34]);
        $r->assertOk();
        expect((float) \DB::table('tax_rates')->where('id', $id)->value('tax_rate'))->toBe(12.34);
    }

    public function test_update_identifier_uniqueness_excludes_self(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate(['identifier' => 'KEEP-'.uniqid()]);
        $r = $this->adminPut($admin, '/api/admin/settings/tax-rates/'.$id, [
            'identifier' => \DB::table('tax_rates')->where('id', $id)->value('identifier'),
            'tax_rate'   => 9.0,
        ]);
        $r->assertOk();
    }

    public function test_update_identifier_duplicate_returns_422(): void
    {
        $admin = $this->createAdmin();
        $dup = 'DUP-UPDATE-'.uniqid();
        $this->insertTaxRate(['identifier' => $dup]);
        $id2 = $this->insertTaxRate();
        $r = $this->adminPut($admin, '/api/admin/settings/tax-rates/'.$id2, ['identifier' => $dup]);
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPut($admin, '/api/admin/settings/tax-rates/9999999', ['tax_rate' => 5]);
        expect($r->getStatusCode())->toBe(404);
    }

    public function test_update_switch_to_zip_range_requires_range(): void
    {
        $admin = $this->createAdmin();
        // Existing row is specific-zip. Switch to is_zip=true without supplying range.
        $id = $this->insertTaxRate();
        $r = $this->adminPut($admin, '/api/admin/settings/tax-rates/'.$id, [
            'is_zip'   => true,
            'zip_code' => null,
        ]);
        // existing zip_from/zip_to are null, so this must fail.
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_update_switch_to_zip_range_with_values_ok(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate();
        $r = $this->adminPut($admin, '/api/admin/settings/tax-rates/'.$id, [
            'is_zip'   => true,
            'zip_from' => '80000',
            'zip_to'   => '80999',
        ]);
        $r->assertOk();
        expect((int) \DB::table('tax_rates')->where('id', $id)->value('is_zip'))->toBe(1);
        expect(\DB::table('tax_rates')->where('id', $id)->value('zip_from'))->toBe('80000');
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate();
        $r = $this->adminDelete($admin, '/api/admin/settings/tax-rates/'.$id);
        $r->assertOk();
        expect(\DB::table('tax_rates')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminDelete($admin, '/api/admin/settings/tax-rates/9999999');
        expect($r->getStatusCode())->toBe(404);
    }

    public function test_delete_cascades_pivot_to_tax_categories(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate();

        // Manually insert a tax category and pivot row.
        $catId = \DB::table('tax_categories')->insertGetId([
            'code'        => 'CAT-'.uniqid(),
            'name'        => 'Pivot Test',
            'description' => 'test',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        \DB::table('tax_categories_tax_rates')->insert([
            'tax_category_id' => $catId,
            'tax_rate_id'     => $id,
        ]);

        $this->adminDelete($admin, '/api/admin/settings/tax-rates/'.$id)->assertOk();
        expect(\DB::table('tax_categories_tax_rates')->where('tax_rate_id', $id)->exists())->toBeFalse();
        expect(\DB::table('tax_categories')->where('id', $catId)->exists())->toBeTrue();
    }
}
