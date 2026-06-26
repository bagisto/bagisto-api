<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for the admin Settings → Locales CRUD endpoints
 * (Block B Wave 1).
 *
 * Endpoints:
 *   GET    /api/admin/settings/locales
 *   GET    /api/admin/settings/locales/{id}
 *   POST   /api/admin/settings/locales
 *   PUT    /api/admin/settings/locales/{id}
 *   DELETE /api/admin/settings/locales/{id}
 *   POST   /api/admin/settings/locales/mass-delete
 */
class SettingsLocaleTest extends AdminApiTestCase
{
    protected function uniqueCode(string $prefix = 'tl'): string
    {
        return strtolower($prefix.str_replace('.', '', (string) microtime(true)).rand(10, 99));
    }

    protected function insertLocale(array $overrides = []): int
    {
        return \DB::table('locales')->insertGetId(array_merge([
            'code'       => $this->uniqueCode(),
            'name'       => 'Test Locale',
            'direction'  => 'ltr',
            'logo_path'  => null,
            'created_at' => now(),
            'updated_at' => now(),
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

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/settings/locales');
        $response->assertStatus(401);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/settings/locales', [
            'code' => $this->uniqueCode(), 'name' => 'X', 'direction' => 'ltr',
        ]);
        $response->assertStatus(401);
    }

    public function test_detail_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertLocale();
        $response = $this->publicGet('/api/admin/settings/locales/'.$id);
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/locales');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
        expect($response->json('meta.currentPage'))->toBe(1);
        expect($response->json('meta.perPage'))->toBe(10);
    }

    public function test_listing_returns_seeded_row(): void
    {
        $admin = $this->createAdmin();
        $code = $this->uniqueCode('lis');
        $id = $this->insertLocale(['code' => $code, 'name' => 'Listing Test']);

        $response = $this->adminGet($admin, '/api/admin/settings/locales?per_page=50');
        $response->assertOk();

        $row = collect($response->json('data'))->firstWhere('id', $id);
        expect($row)->not()->toBeNull();
        expect($row['code'])->toBe($code);
        expect($row['name'])->toBe('Listing Test');
        expect($row['direction'])->toBe('ltr');
        expect($row)->toHaveKeys(['id', 'code', 'name', 'direction', 'logoPath', 'logoUrl', 'createdAt', 'updatedAt']);
    }

    public function test_listing_filter_by_id(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertLocale(['code' => $this->uniqueCode('fi1')]);
        $id2 = $this->insertLocale(['code' => $this->uniqueCode('fi2')]);
        $id3 = $this->insertLocale(['code' => $this->uniqueCode('fi3')]);

        $response = $this->adminGet($admin, '/api/admin/settings/locales?id='.$id1.','.$id3.'&per_page=50');
        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->toContain($id3);
        expect($ids)->not()->toContain($id2);
    }

    public function test_listing_filter_by_code(): void
    {
        $admin = $this->createAdmin();
        $code1 = $this->uniqueCode('fc1');
        $code2 = $this->uniqueCode('fc2');
        $id1 = $this->insertLocale(['code' => $code1]);
        $id2 = $this->insertLocale(['code' => $code2]);

        $response = $this->adminGet($admin, '/api/admin/settings/locales?code='.$code1.'&per_page=50');
        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_listing_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $marker = 'FilterByName'.rand(1000, 9999);
        $id1 = $this->insertLocale(['name' => $marker.' One']);
        $id2 = $this->insertLocale(['name' => 'OtherLocaleName']);

        $response = $this->adminGet($admin, '/api/admin/settings/locales?name='.$marker.'&per_page=50');
        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_listing_filter_by_direction(): void
    {
        $admin = $this->createAdmin();
        $rtl = $this->insertLocale(['direction' => 'rtl']);
        $ltr = $this->insertLocale(['direction' => 'ltr']);

        $response = $this->adminGet($admin, '/api/admin/settings/locales?direction=rtl&per_page=50');
        $response->assertOk();

        $directions = collect($response->json('data'))->pluck('direction')->unique()->values()->all();
        expect($directions)->toBe(['rtl']);
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($rtl);
        expect($ids)->not()->toContain($ltr);
    }

    public function test_listing_sort_by_code_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertLocale(['code' => $this->uniqueCode('zz')]);
        $this->insertLocale(['code' => $this->uniqueCode('aa')]);
        $this->insertLocale(['code' => $this->uniqueCode('mm')]);

        $response = $this->adminGet($admin, '/api/admin/settings/locales?sort=code-asc&per_page=50');
        $response->assertOk();

        $codes = collect($response->json('data'))->pluck('code')->all();
        $sorted = $codes;
        sort($sorted, SORT_FLAG_CASE | SORT_STRING);
        expect($codes)->toBe($sorted);
    }

    public function test_listing_per_page_above_cap_clamped(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/locales?per_page=9999');
        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_detail_returns_full_row(): void
    {
        $admin = $this->createAdmin();
        $code = $this->uniqueCode('det');
        $id = $this->insertLocale(['code' => $code, 'name' => 'DetailLocale', 'direction' => 'rtl']);

        $response = $this->adminGet($admin, '/api/admin/settings/locales/'.$id);
        $response->assertOk();

        expect($response->json('id'))->toBe($id);
        expect($response->json('code'))->toBe($code);
        expect($response->json('name'))->toBe('DetailLocale');
        expect($response->json('direction'))->toBe('rtl');
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/locales/9999999');
        $response->assertStatus(404);
    }

    public function test_create_happy_path_returns_201(): void
    {
        $admin = $this->createAdmin();
        $code = $this->uniqueCode('cr');

        $response = $this->adminPost($admin, '/api/admin/settings/locales', [
            'code'      => $code,
            'name'      => 'CreatedLocale',
            'direction' => 'ltr',
        ]);

        $response->assertStatus(201);
        expect($response->json('id'))->toBeInt();
        expect($response->json('code'))->toBe($code);
        expect(\DB::table('locales')->where('code', $code)->exists())->toBeTrue();
    }

    public function test_create_accepts_logo_path_string(): void
    {
        $admin = $this->createAdmin();
        $code = $this->uniqueCode('lp');

        $response = $this->adminPost($admin, '/api/admin/settings/locales', [
            'code'      => $code,
            'name'      => 'WithLogo',
            'direction' => 'ltr',
            'logo_path' => 'locales/test.png',
        ]);

        $response->assertStatus(201);
        expect(\DB::table('locales')->where('code', $code)->value('logo_path'))->toBe('locales/test.png');
    }

    public function test_create_missing_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/locales', [
            'name' => 'X', 'direction' => 'ltr',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/locales', [
            'code' => $this->uniqueCode(), 'direction' => 'ltr',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_invalid_direction_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/locales', [
            'code' => $this->uniqueCode(), 'name' => 'X', 'direction' => 'sideways',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_invalid_code_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/locales', [
            'code' => 'BAD CODE!', 'name' => 'X', 'direction' => 'ltr',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_duplicate_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $code = $this->uniqueCode('dup');
        $this->insertLocale(['code' => $code]);

        $response = $this->adminPost($admin, '/api/admin/settings/locales', [
            'code' => $code, 'name' => 'X', 'direction' => 'ltr',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_changes_name(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertLocale(['name' => 'OldName']);

        $response = $this->adminPut($admin, '/api/admin/settings/locales/'.$id, [
            'name' => 'NewName',
        ]);
        $response->assertOk();
        expect(\DB::table('locales')->where('id', $id)->value('name'))->toBe('NewName');
    }

    public function test_update_same_code_excludes_self(): void
    {
        $admin = $this->createAdmin();
        $code = $this->uniqueCode('self');
        $id = $this->insertLocale(['code' => $code]);

        $response = $this->adminPut($admin, '/api/admin/settings/locales/'.$id, [
            'code'      => $code,
            'name'      => 'X',
            'direction' => 'ltr',
        ]);
        $response->assertOk();
    }

    public function test_update_duplicate_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $code1 = $this->uniqueCode('a');
        $code2 = $this->uniqueCode('b');
        $this->insertLocale(['code' => $code1]);
        $id2 = $this->insertLocale(['code' => $code2]);

        $response = $this->adminPut($admin, '/api/admin/settings/locales/'.$id2, [
            'code' => $code1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_invalid_direction_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertLocale();
        $response = $this->adminPut($admin, '/api/admin/settings/locales/'.$id, [
            'direction' => 'bogus',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/settings/locales/9999999', [
            'name' => 'X',
        ]);
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertLocale();
        $id = $this->insertLocale();

        $response = $this->adminDelete($admin, '/api/admin/settings/locales/'.$id);
        $response->assertOk();
        expect(\DB::table('locales')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_delete_last_locale_returns_400(): void
    {
        $admin = $this->createAdmin();

        $only = (int) \DB::table('locales')->orderBy('id')->value('id');
        if (! $only) {
            $only = $this->insertLocale();
        }

        \DB::table('channels')->update(['default_locale_id' => $only]);
        \DB::table('channel_locales')->where('locale_id', '!=', $only)->delete();
        \DB::table('locales')->where('id', '!=', $only)->delete();

        $response = $this->adminDelete($admin, '/api/admin/settings/locales/'.$only);
        expect($response->getStatusCode())->toBe(400);
        expect(\DB::table('locales')->where('id', $only)->exists())->toBeTrue();
    }

    public function test_delete_channel_default_returns_400(): void
    {
        $admin = $this->createAdmin();
        $this->insertLocale();
        $newDefault = $this->insertLocale();

        \DB::table('channels')->limit(1)->update(['default_locale_id' => $newDefault]);

        $response = $this->adminDelete($admin, '/api/admin/settings/locales/'.$newDefault);
        expect($response->getStatusCode())->toBe(400);
        expect(\DB::table('locales')->where('id', $newDefault)->exists())->toBeTrue();
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/locales/9999999');
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertLocale();
        $response = $this->deleteJson('/api/admin/settings/locales/'.$id);
        expect($response->getStatusCode())->toBe(401);
    }

    public function test_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertLocale();
        $id1 = $this->insertLocale();
        $id2 = $this->insertLocale();

        $response = $this->adminPost($admin, '/api/admin/settings/locales/mass-delete', [
            'indices' => [$id1, $id2],
        ]);

        $response->assertOk();
        expect($response->json('deleted'))->toBeArray();
        expect(\DB::table('locales')->where('id', $id1)->exists())->toBeFalse();
        expect(\DB::table('locales')->where('id', $id2)->exists())->toBeFalse();
    }

    public function test_mass_delete_skips_channel_default(): void
    {
        $admin = $this->createAdmin();
        $this->insertLocale();
        $protect = $this->insertLocale();
        \DB::table('channels')->limit(1)->update(['default_locale_id' => $protect]);

        $response = $this->adminPost($admin, '/api/admin/settings/locales/mass-delete', [
            'indices' => [$protect],
        ]);
        $response->assertOk();
        expect(\DB::table('locales')->where('id', $protect)->exists())->toBeTrue();

        $skipped = $response->json('skipped');
        expect(is_array($skipped) && count($skipped) >= 1)->toBeTrue();
    }

    public function test_mass_delete_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/locales/mass-delete', ['indices' => []]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_mass_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/settings/locales/mass-delete', ['indices' => [1]]);
        expect($response->getStatusCode())->toBe(401);
    }
}
