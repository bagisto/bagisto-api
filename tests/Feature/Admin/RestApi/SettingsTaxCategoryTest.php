<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for Admin Settings → Tax Categories CRUD (Block B Wave 3).
 */
class SettingsTaxCategoryTest extends AdminApiTestCase
{
    protected function insertTaxCategory(array $overrides = []): int
    {
        return \DB::table('tax_categories')->insertGetId(array_merge([
            'code'        => 'tc-'.uniqid(),
            'name'        => 'Test Tax Category',
            'description' => 'Test description',
            'created_at'  => now(),
            'updated_at'  => now(),
        ], $overrides));
    }

    protected function insertTaxRate(array $overrides = []): int
    {
        return \DB::table('tax_rates')->insertGetId(array_merge([
            'identifier' => 'tr-'.uniqid(),
            'is_zip'     => 0,
            'zip_code'   => '00000',
            'state'      => 'CA',
            'country'    => 'US',
            'tax_rate'   => 5.0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function attachRate(int $categoryId, int $rateId): void
    {
        \DB::table('tax_categories_tax_rates')->insert([
            'tax_category_id' => $categoryId,
            'tax_rate_id'     => $rateId,
        ]);
    }

    protected function adminPut(\Webkul\User\Models\Admin $admin, string $url, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete(\Webkul\User\Models\Admin $admin, string $url, ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function createAdminWithoutPermissions(): \Webkul\User\Models\Admin
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'Limited '.uniqid(),
            'description'     => 'no tax-category perms',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/settings/tax-categories');
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertTaxCategory();

        $response = $this->adminGet($admin, '/api/admin/settings/tax-categories');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertTaxCategory();

        $response = $this->adminGet($admin, '/api/admin/settings/tax-categories?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'code', 'name', 'description', 'createdAt', 'updatedAt']);
    }

    public function test_filter_by_code(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxCategory(['code' => 'fltcode-x']);
        $this->insertTaxCategory(['code' => 'other-x']);

        $response = $this->adminGet($admin, '/api/admin/settings/tax-categories?code=fltcode');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxCategory(['name' => 'UniqueNamedCategory']);
        $this->insertTaxCategory(['name' => 'Other Name']);

        $response = $this->adminGet($admin, '/api/admin/settings/tax-categories?name=UniqueNamed');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_sort_by_code_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertTaxCategory(['code' => 'zzz-srt']);
        $this->insertTaxCategory(['code' => 'aaa-srt']);

        $response = $this->adminGet($admin, '/api/admin/settings/tax-categories?sort=code&order=asc');

        $response->assertOk();
        $codes = collect($response->json('data'))->pluck('code')->all();
        $idxA = array_search('aaa-srt', $codes, true);
        $idxZ = array_search('zzz-srt', $codes, true);

        if ($idxA !== false && $idxZ !== false) {
            expect($idxA)->toBeLessThan($idxZ);
        }
    }

    public function test_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/tax-categories?per_page=999');

        $response->assertOk();
        expect((int) $response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_detail_returns_payload_with_tax_rates(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxCategory(['code' => 'det-tc', 'name' => 'Detail TC']);
        $r1 = $this->insertTaxRate();
        $r2 = $this->insertTaxRate();
        $this->attachRate($id, $r1);
        $this->attachRate($id, $r2);

        $response = $this->adminGet($admin, '/api/admin/settings/tax-categories/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('code'))->toBe('det-tc');
        $rates = $response->json('taxRates');
        expect($rates)->toBeArray();
        expect(count($rates))->toBe(2);
        $rateIds = collect($rates)->pluck('id')->all();
        expect($rateIds)->toContain($r1);
        expect($rateIds)->toContain($r2);
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/tax-categories/999999');
        $response->assertStatus(404);
    }

    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertTaxCategory();
        $response = $this->publicGet('/api/admin/settings/tax-categories/'.$id);
        $response->assertStatus(401);
    }

    public function test_create_happy_path_with_tax_rates(): void
    {
        $admin = $this->createAdmin();
        $r1 = $this->insertTaxRate();
        $r2 = $this->insertTaxRate();

        $response = $this->adminPost($admin, '/api/admin/settings/tax-categories', [
            'code'        => 'newtc-1',
            'name'        => 'New TC',
            'description' => 'Brand new',
            'taxrates'    => [$r1, $r2],
        ]);

        $response->assertStatus(201);
        expect($response->json('code'))->toBe('newtc-1');

        $id = $response->json('id');
        $this->assertDatabaseHas('tax_categories', ['id' => $id, 'code' => 'newtc-1']);
        $this->assertDatabaseHas('tax_categories_tax_rates', ['tax_category_id' => $id, 'tax_rate_id' => $r1]);
        $this->assertDatabaseHas('tax_categories_tax_rates', ['tax_category_id' => $id, 'tax_rate_id' => $r2]);

        $rates = $response->json('taxRates');
        expect($rates)->toBeArray();
        expect(count($rates))->toBe(2);
    }

    public function test_create_missing_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $r1 = $this->insertTaxRate();
        $response = $this->adminPost($admin, '/api/admin/settings/tax-categories', [
            'name'        => 'X',
            'description' => 'X',
            'taxrates'    => [$r1],
        ]);
        $response->assertStatus(422);
    }

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $r1 = $this->insertTaxRate();
        $response = $this->adminPost($admin, '/api/admin/settings/tax-categories', [
            'code'        => 'noname-tc',
            'description' => 'X',
            'taxrates'    => [$r1],
        ]);
        $response->assertStatus(422);
    }

    public function test_create_missing_description_returns_422(): void
    {
        $admin = $this->createAdmin();
        $r1 = $this->insertTaxRate();
        $response = $this->adminPost($admin, '/api/admin/settings/tax-categories', [
            'code'     => 'nodesc-tc',
            'name'     => 'X',
            'taxrates' => [$r1],
        ]);
        $response->assertStatus(422);
    }

    public function test_create_missing_taxrates_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/tax-categories', [
            'code'        => 'norate-tc',
            'name'        => 'X',
            'description' => 'X',
        ]);
        $response->assertStatus(422);
    }

    public function test_create_duplicate_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->insertTaxCategory(['code' => 'dup-tc']);
        $r1 = $this->insertTaxRate();

        $response = $this->adminPost($admin, '/api/admin/settings/tax-categories', [
            'code'        => 'dup-tc',
            'name'        => 'Dup',
            'description' => 'Dup',
            'taxrates'    => [$r1],
        ]);
        $response->assertStatus(422);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->publicPost('/api/admin/settings/tax-categories', ['code' => 'a', 'name' => 'b']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_create_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $r1 = $this->insertTaxRate();
        $response = $this->adminPost($admin, '/api/admin/settings/tax-categories', [
            'code'        => 'noperm-tc',
            'name'        => 'X',
            'description' => 'X',
            'taxrates'    => [$r1],
        ]);
        $response->assertStatus(403);
    }

    public function test_update_happy_path_syncs_rates(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxCategory(['code' => 'upd-tc', 'name' => 'Before']);
        $r1 = $this->insertTaxRate();
        $r2 = $this->insertTaxRate();
        $r3 = $this->insertTaxRate();
        $this->attachRate($id, $r1);
        $this->attachRate($id, $r2);

        $response = $this->adminPut($admin, '/api/admin/settings/tax-categories/'.$id, [
            'code'        => 'upd-tc',
            'name'        => 'After',
            'description' => 'After desc',
            'taxrates'    => [$r3],
        ]);

        $response->assertOk();
        expect($response->json('name'))->toBe('After');
        $this->assertDatabaseHas('tax_categories', ['id' => $id, 'name' => 'After']);
        $this->assertDatabaseHas('tax_categories_tax_rates', ['tax_category_id' => $id, 'tax_rate_id' => $r3]);
        $this->assertDatabaseMissing('tax_categories_tax_rates', ['tax_category_id' => $id, 'tax_rate_id' => $r1]);
        $this->assertDatabaseMissing('tax_categories_tax_rates', ['tax_category_id' => $id, 'tax_rate_id' => $r2]);
    }

    public function test_update_duplicate_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->insertTaxCategory(['code' => 'taken-up']);
        $id = $this->insertTaxCategory(['code' => 'mine-up']);
        $r1 = $this->insertTaxRate();

        $response = $this->adminPut($admin, '/api/admin/settings/tax-categories/'.$id, [
            'code'        => 'taken-up',
            'name'        => 'X',
            'description' => 'X',
            'taxrates'    => [$r1],
        ]);
        $response->assertStatus(422);
    }

    public function test_update_same_code_self_is_ok(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxCategory(['code' => 'same-up']);
        $r1 = $this->insertTaxRate();

        $response = $this->adminPut($admin, '/api/admin/settings/tax-categories/'.$id, [
            'code'        => 'same-up',
            'name'        => 'Same',
            'description' => 'Same',
            'taxrates'    => [$r1],
        ]);
        $response->assertOk();
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $r1 = $this->insertTaxRate();
        $response = $this->adminPut($admin, '/api/admin/settings/tax-categories/999999', [
            'code'        => 'gh', 'name' => 'g', 'description' => 'g', 'taxrates' => [$r1],
        ]);
        $response->assertStatus(404);
    }

    public function test_update_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertTaxCategory();
        $response = $this->putJson('/api/admin/settings/tax-categories/'.$id, ['name' => 'X']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_update_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertTaxCategory();
        $r1 = $this->insertTaxRate();
        $response = $this->adminPut($admin, '/api/admin/settings/tax-categories/'.$id, [
            'code' => 'x', 'name' => 'x', 'description' => 'x', 'taxrates' => [$r1],
        ]);
        $response->assertStatus(403);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxCategory(['code' => 'del-tc']);

        $response = $this->adminDelete($admin, '/api/admin/settings/tax-categories/'.$id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tax_categories', ['id' => $id]);
    }

    public function test_delete_with_attached_rates_returns_400(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxCategory(['code' => 'inuse-tc']);
        $r1 = $this->insertTaxRate();
        $this->attachRate($id, $r1);

        $response = $this->adminDelete($admin, '/api/admin/settings/tax-categories/'.$id);

        $response->assertStatus(400);
        $this->assertDatabaseHas('tax_categories', ['id' => $id]);
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/tax-categories/999999');
        $response->assertStatus(404);
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertTaxCategory();
        $response = $this->deleteJson('/api/admin/settings/tax-categories/'.$id);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_delete_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertTaxCategory();
        $response = $this->adminDelete($admin, '/api/admin/settings/tax-categories/'.$id);
        $response->assertStatus(403);
    }
}
