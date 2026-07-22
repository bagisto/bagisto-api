<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

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
    protected function insertTaxRate(array $overrides = []): int
    {
        return \DB::table('tax_rates')->insertGetId(array_merge([
            'identifier' => 'TR-'.uniqid(),
            'is_zip' => 0,
            'zip_code' => '12345',
            'zip_from' => null,
            'zip_to' => null,
            'state' => 'CA',
            'country' => 'US',
            'tax_rate' => 8.5,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function adminPut(Admin $admin, string $url, array $data = []): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin));
    }

    protected function adminDelete(Admin $admin, string $url): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin));
    }

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
            'is_zip' => 1,
            'zip_code' => null,
            'zip_from' => '90000',
            'zip_to' => '90999',
        ]);

        $r = $this->adminGet($admin, '/api/admin/settings/tax-rates/'.$id);
        $r->assertOk();
        expect($r->json('isZip'))->toBeTrue();
        expect($r->json('zipFrom'))->toBe('90000');
        expect($r->json('zipTo'))->toBe('90999');
    }

    public function test_create_specific_zip_happy_path(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'CREATE-SP-'.uniqid(),
            'is_zip' => false,
            'zip_code' => '94103',
            'state' => 'CA',
            'country' => 'US',
            'tax_rate' => 8.5,
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
            'is_zip' => true,
            'zip_from' => '94000',
            'zip_to' => '94999',
            'state' => 'CA',
            'country' => 'US',
            'tax_rate' => 9.0,
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
            'is_zip' => false, 'zip_code' => '94103', 'country' => 'US', 'tax_rate' => 8.5,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_create_country_must_be_2_letters(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'BAD-COUNTRY-'.uniqid(),
            'is_zip' => false, 'zip_code' => '94103', 'country' => 'USA', 'tax_rate' => 8.5,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_create_tax_rate_non_numeric_rejected(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'BAD-RATE-'.uniqid(),
            'is_zip' => false, 'zip_code' => '94103', 'country' => 'US', 'tax_rate' => 'abc',
        ]);
        expect(in_array($r->getStatusCode(), [400, 422, 500], true))->toBeTrue();
        expect($r->getStatusCode())->not()->toBe(201);
    }

    public function test_create_tax_rate_out_of_range_returns_422(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'OOR-'.uniqid(),
            'is_zip' => false, 'zip_code' => '94103', 'country' => 'US', 'tax_rate' => 150,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_create_is_zip_false_requires_zip_code(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'NO-ZIP-CODE-'.uniqid(),
            'is_zip' => false,
            'country' => 'US', 'tax_rate' => 8.5,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_create_is_zip_true_requires_range(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'NO-RANGE-'.uniqid(),
            'is_zip' => true,
            'country' => 'US', 'tax_rate' => 8.5,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_create_is_zip_true_missing_zip_to_returns_422(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/settings/tax-rates', [
            'identifier' => 'NO-TO-'.uniqid(),
            'is_zip' => true,
            'zip_from' => '94000',
            'country' => 'US', 'tax_rate' => 8.5,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

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
            'tax_rate' => 9.0,
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
        $id = $this->insertTaxRate();
        $r = $this->adminPut($admin, '/api/admin/settings/tax-rates/'.$id, [
            'is_zip' => true,
            'zip_code' => null,
        ]);
        expect($r->getStatusCode())->toBe(422);
    }

    public function test_update_switch_to_zip_range_with_values_ok(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate();
        $r = $this->adminPut($admin, '/api/admin/settings/tax-rates/'.$id, [
            'is_zip' => true,
            'zip_from' => '80000',
            'zip_to' => '80999',
        ]);
        $r->assertOk();
        expect((int) \DB::table('tax_rates')->where('id', $id)->value('is_zip'))->toBe(1);
        expect(\DB::table('tax_rates')->where('id', $id)->value('zip_from'))->toBe('80000');
    }

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

        $catId = \DB::table('tax_categories')->insertGetId([
            'code' => 'CAT-'.uniqid(),
            'name' => 'Pivot Test',
            'description' => 'test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('tax_categories_tax_rates')->insert([
            'tax_category_id' => $catId,
            'tax_rate_id' => $id,
        ]);

        $this->adminDelete($admin, '/api/admin/settings/tax-rates/'.$id)->assertOk();
        expect(\DB::table('tax_categories_tax_rates')->where('tax_rate_id', $id)->exists())->toBeFalse();
        expect(\DB::table('tax_categories')->where('id', $catId)->exists())->toBeTrue();
    }

    public function test_export_returns_csv(): void
    {
        $admin = $this->createAdmin();
        $this->insertTaxRate(['identifier' => 'EXP-'.uniqid()]);

        $response = $this->get('/api/admin/settings/tax-rates/export?format=csv', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ));

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('text/csv');
        expect($response->headers->get('Content-Disposition'))->toContain('tax-rates.csv');
        expect($response->getContent())->toContain('ID,Identifier,State,Country');
    }

    public function test_export_supports_xlsx(): void
    {
        $admin = $this->createAdmin();

        $response = $this->get('/api/admin/settings/tax-rates/export?format=xlsx', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ));

        $response->assertStatus(200);

        expect($response->headers->get('Content-Type'))->toContain('spreadsheetml');
        expect($response->headers->get('Content-Disposition'))->toContain('.xlsx');
    }

    public function test_export_unsupported_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->get('/api/admin/settings/tax-rates/export?format=pdf', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(422);
    }

    public function test_export_requires_authentication(): void
    {
        $this->get('/api/admin/settings/tax-rates/export', ['Accept' => 'text/csv'])->assertStatus(401);
    }

    public function test_export_no_permission_returns_403(): void
    {
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions' => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $this->get('/api/admin/settings/tax-rates/export', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(403);
    }
}
