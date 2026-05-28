<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for Admin Settings → Currencies CRUD (Block B Wave 1).
 *
 * Endpoints:
 *   GET    /api/admin/settings/currencies
 *   GET    /api/admin/settings/currencies/{id}
 *   POST   /api/admin/settings/currencies
 *   PUT    /api/admin/settings/currencies/{id}
 *   DELETE /api/admin/settings/currencies/{id}
 *   POST   /api/admin/settings/currencies/mass-delete
 */
class SettingsCurrencyTest extends AdminApiTestCase
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function insertCurrency(array $overrides = []): int
    {
        return \DB::table('currencies')->insertGetId(array_merge([
            'code'              => strtoupper(substr('T'.uniqid(), 0, 3)),
            'name'              => 'Test Currency',
            'symbol'            => 'T$',
            'decimal'           => 2,
            'group_separator'   => ',',
            'decimal_separator' => '.',
            'currency_position' => 'left',
            'created_at'        => now(),
            'updated_at'        => now(),
        ], $overrides));
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
            'description'     => 'no currency perms',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    // -------------------------------------------------------------------------
    // Listing
    // -------------------------------------------------------------------------

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/settings/currencies');
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertCurrency();

        $response = $this->adminGet($admin, '/api/admin/settings/currencies');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertCurrency();

        $response = $this->adminGet($admin, '/api/admin/settings/currencies?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys([
            'id', 'code', 'name', 'symbol', 'decimal',
            'groupSeparator', 'decimalSeparator', 'currencyPosition',
            'createdAt', 'updatedAt',
        ]);
    }

    public function test_filter_by_code(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCurrency(['code' => 'XYZ', 'name' => 'XYZ Coin']);
        $this->insertCurrency(['code' => 'ABC', 'name' => 'ABC Coin']);

        $response = $this->adminGet($admin, '/api/admin/settings/currencies?code=XYZ');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCurrency(['code' => 'AAA', 'name' => 'AlphaNamedCurrency']);
        $this->insertCurrency(['code' => 'BBB', 'name' => 'Beta Coin']);

        $response = $this->adminGet($admin, '/api/admin/settings/currencies?name=AlphaNamed');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_filter_by_symbol(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCurrency(['code' => 'SYM', 'symbol' => '@@']);
        $this->insertCurrency(['code' => 'ZZZ', 'symbol' => '##']);

        $response = $this->adminGet($admin, '/api/admin/settings/currencies?symbol=@@');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_sort_by_code_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertCurrency(['code' => 'ZZA', 'name' => 'Z One']);
        $this->insertCurrency(['code' => 'AAA', 'name' => 'A One']);

        $response = $this->adminGet($admin, '/api/admin/settings/currencies?sort=code&order=asc');

        $response->assertOk();
        $codes = collect($response->json('data'))->pluck('code')->all();
        $idxA = array_search('AAA', $codes, true);
        $idxZ = array_search('ZZA', $codes, true);

        if ($idxA !== false && $idxZ !== false) {
            expect($idxA)->toBeLessThan($idxZ);
        }
    }

    public function test_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/currencies?per_page=999');

        $response->assertOk();
        expect((int) $response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function test_detail_returns_full_payload(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCurrency(['code' => 'DET', 'name' => 'Detail']);

        $response = $this->adminGet($admin, '/api/admin/settings/currencies/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('code'))->toBe('DET');
        expect($response->json('name'))->toBe('Detail');
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/currencies/999999');
        $response->assertStatus(404);
    }

    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertCurrency();
        $response = $this->publicGet('/api/admin/settings/currencies/'.$id);
        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_happy_path(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/settings/currencies', [
            'code'              => 'NEW',
            'name'              => 'New Currency',
            'symbol'            => 'N$',
            'decimal'           => 2,
            'group_separator'   => ',',
            'decimal_separator' => '.',
            'currency_position' => 'left',
        ]);

        $response->assertStatus(201);
        expect($response->json('code'))->toBe('NEW');
        expect($response->json('name'))->toBe('New Currency');
        $this->assertDatabaseHas('currencies', ['code' => 'NEW']);
    }

    public function test_create_code_is_uppercased(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/settings/currencies', [
            'code' => 'low',
            'name' => 'Lower Code',
        ]);

        $response->assertStatus(201);
        expect($response->json('code'))->toBe('LOW');
    }

    public function test_create_missing_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/currencies', ['name' => 'No Code']);
        $response->assertStatus(422);
    }

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/currencies', ['code' => 'ABC']);
        $response->assertStatus(422);
    }

    public function test_create_bad_code_length_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/currencies', ['code' => 'TOOLONG', 'name' => 'X']);
        $response->assertStatus(422);
    }

    public function test_create_non_alpha_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/currencies', ['code' => 'A1B', 'name' => 'X']);
        $response->assertStatus(422);
    }

    public function test_create_duplicate_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->insertCurrency(['code' => 'DUP']);

        $response = $this->adminPost($admin, '/api/admin/settings/currencies', [
            'code' => 'DUP',
            'name' => 'Duplicate',
        ]);

        $response->assertStatus(422);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->publicPost('/api/admin/settings/currencies', ['code' => 'AUT', 'name' => 'X']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_create_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $response = $this->adminPost($admin, '/api/admin/settings/currencies', ['code' => 'NOP', 'name' => 'X']);
        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCurrency(['code' => 'UPD', 'name' => 'Before']);

        $response = $this->adminPut($admin, '/api/admin/settings/currencies/'.$id, [
            'name'   => 'After',
            'symbol' => 'U$',
        ]);

        $response->assertOk();
        expect($response->json('name'))->toBe('After');
        $this->assertDatabaseHas('currencies', ['id' => $id, 'name' => 'After']);
    }

    public function test_update_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCurrency();
        $response = $this->adminPut($admin, '/api/admin/settings/currencies/'.$id, []);
        $response->assertStatus(422);
    }

    public function test_update_code_field_is_ignored(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCurrency(['code' => 'KEP']);

        $response = $this->adminPut($admin, '/api/admin/settings/currencies/'.$id, [
            'code' => 'XXX',
            'name' => 'Updated',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('currencies', ['id' => $id, 'code' => 'KEP']);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/settings/currencies/999999', ['name' => 'X']);
        $response->assertStatus(404);
    }

    public function test_update_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertCurrency();
        $response = $this->putJson('/api/admin/settings/currencies/'.$id, ['name' => 'X']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_update_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertCurrency();
        $response = $this->adminPut($admin, '/api/admin/settings/currencies/'.$id, ['name' => 'X']);
        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        // Make sure there are at least 2 currencies so deletion is allowed.
        $this->insertCurrency(['code' => 'KP1']);
        $id = $this->insertCurrency(['code' => 'DEL']);

        $response = $this->adminDelete($admin, '/api/admin/settings/currencies/'.$id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('currencies', ['id' => $id]);
    }

    public function test_delete_last_currency_returns_400(): void
    {
        $admin = $this->createAdmin();
        // Seed the survivor first, point every channel at it, then wipe siblings so it is the last row.
        $id = $this->insertCurrency(['code' => 'ONE']);
        \DB::table('channels')->update(['base_currency_id' => $id]);
        \DB::table('currencies')->where('id', '!=', $id)->delete();
        // Reset the Core facade's cached channel so middleware reloads with the new base_currency_id.
        \Webkul\Core\Facades\Core::clearResolvedInstances();

        $response = $this->adminDelete($admin, '/api/admin/settings/currencies/'.$id);

        $response->assertStatus(400);
        $this->assertDatabaseHas('currencies', ['id' => $id]);
    }

    public function test_delete_channel_base_currency_returns_400(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCurrency(['code' => 'BSE']);
        // Point one channel at this currency.
        \DB::table('channels')->limit(1)->update(['base_currency_id' => $id]);
        // Also add a sibling so we don't trip the last-currency guard first.
        $this->insertCurrency(['code' => 'SIB']);

        $response = $this->adminDelete($admin, '/api/admin/settings/currencies/'.$id);

        $response->assertStatus(400);
        $this->assertDatabaseHas('currencies', ['id' => $id]);
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/currencies/999999');
        $response->assertStatus(404);
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertCurrency();
        $response = $this->deleteJson('/api/admin/settings/currencies/'.$id);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_delete_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $this->insertCurrency(['code' => 'KP2']);
        $id = $this->insertCurrency(['code' => 'NOD']);
        $response = $this->adminDelete($admin, '/api/admin/settings/currencies/'.$id);
        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Mass-delete
    // -------------------------------------------------------------------------

    public function test_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $keep = $this->insertCurrency(['code' => 'KP3']);
        $id1 = $this->insertCurrency(['code' => 'M1A']);
        $id2 = $this->insertCurrency(['code' => 'M2B']);

        $response = $this->adminPost($admin, '/api/admin/settings/currencies/mass-delete', [
            'indices' => [$id1, $id2],
        ]);

        $response->assertStatus(200);
        $deleted = $response->json('deleted');
        expect($deleted)->toContain($id1);
        expect($deleted)->toContain($id2);
        $this->assertDatabaseMissing('currencies', ['id' => $id1]);
        $this->assertDatabaseMissing('currencies', ['id' => $id2]);
        $this->assertDatabaseHas('currencies', ['id' => $keep]);
    }

    public function test_mass_delete_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/currencies/mass-delete', ['indices' => []]);
        $response->assertStatus(422);
    }

    public function test_mass_delete_would_empty_table_returns_400(): void
    {
        $admin = $this->createAdmin();
        // Seed the two doomed rows, point all channels at one of them so wiping siblings is safe,
        // then drop everything that isn't one of these two so deleting both would empty the table.
        $id1 = $this->insertCurrency(['code' => 'EE1']);
        $id2 = $this->insertCurrency(['code' => 'EE2']);
        \DB::table('channels')->update(['base_currency_id' => $id1]);
        \DB::table('currencies')->whereNotIn('id', [$id1, $id2])->delete();
        \Webkul\Core\Facades\Core::clearResolvedInstances();

        $response = $this->adminPost($admin, '/api/admin/settings/currencies/mass-delete', [
            'indices' => [$id1, $id2],
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseHas('currencies', ['id' => $id1]);
        $this->assertDatabaseHas('currencies', ['id' => $id2]);
    }

    public function test_mass_delete_channel_base_returns_400(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCurrency(['code' => 'CBB']);
        \DB::table('channels')->limit(1)->update(['base_currency_id' => $id]);
        $other = $this->insertCurrency(['code' => 'OTH']);

        $response = $this->adminPost($admin, '/api/admin/settings/currencies/mass-delete', [
            'indices' => [$id, $other],
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseHas('currencies', ['id' => $id]);
        $this->assertDatabaseHas('currencies', ['id' => $other]);
    }
}
