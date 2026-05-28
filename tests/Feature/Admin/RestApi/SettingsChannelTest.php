<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for Admin Settings → Channels CRUD (Block B Wave 2).
 *
 * Endpoints:
 *   GET    /api/admin/settings/channels
 *   GET    /api/admin/settings/channels/{id}
 *   POST   /api/admin/settings/channels
 *   PUT    /api/admin/settings/channels/{id}
 *   DELETE /api/admin/settings/channels/{id}
 */
class SettingsChannelTest extends AdminApiTestCase
{
    protected function ensureSupportRows(): array
    {
        $this->seedRequiredData();

        $localeId = \DB::table('locales')->value('id');
        if (! $localeId) {
            $localeId = \DB::table('locales')->insertGetId([
                'code'       => 'en',
                'name'       => 'English',
                'direction'  => 'ltr',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $currencyId = \DB::table('currencies')->value('id');
        if (! $currencyId) {
            $currencyId = \DB::table('currencies')->insertGetId([
                'code'       => 'USD',
                'name'       => 'US Dollar',
                'symbol'     => '$',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $sourceId = \DB::table('inventory_sources')->value('id');
        if (! $sourceId) {
            $sourceId = \DB::table('inventory_sources')->insertGetId([
                'code'           => 'default',
                'name'           => 'Default',
                'contact_name'   => 'Default',
                'contact_email'  => 'def@example.com',
                'contact_number' => '0',
                'country'        => 'US',
                'state'          => 'CA',
                'city'           => 'LA',
                'street'         => 'X',
                'postcode'       => '90001',
                'priority'       => 0,
                'status'         => 1,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        $rootCategoryId = \DB::table('categories')->value('id');
        if (! $rootCategoryId) {
            $rootCategoryId = \DB::table('categories')->insertGetId([
                '_lft'       => 1,
                '_rgt'       => 2,
                'parent_id'  => null,
                'status'     => 1,
                'position'   => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'locale_id'         => (int) $localeId,
            'currency_id'       => (int) $currencyId,
            'source_id'         => (int) $sourceId,
            'root_category_id'  => (int) $rootCategoryId,
        ];
    }

    protected function insertChannel(array $overrides = []): int
    {
        $support = $this->ensureSupportRows();

        $code = $overrides['code'] ?? ('ch'.uniqid());

        $id = \DB::table('channels')->insertGetId(array_merge([
            'code'              => $code,
            'theme'             => null,
            'hostname'          => $code.'.example.com',
            'logo'              => null,
            'favicon'           => null,
            'home_seo'          => null,
            'is_maintenance_on' => 0,
            'allowed_ips'       => null,
            'root_category_id'  => $support['root_category_id'],
            'default_locale_id' => $support['locale_id'],
            'base_currency_id'  => $support['currency_id'],
            'created_at'        => now(),
            'updated_at'        => now(),
        ], array_diff_key($overrides, ['name' => 1, 'description' => 1])));

        $localeCode = (string) \DB::table('locales')->where('id', $support['locale_id'])->value('code');
        \DB::table('channel_translations')->insert([
            'channel_id'  => $id,
            'locale'      => $localeCode,
            'name'        => $overrides['name'] ?? 'Channel '.$id,
            'description' => $overrides['description'] ?? null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        \DB::table('channel_locales')->insertOrIgnore([
            'channel_id' => $id,
            'locale_id'  => $support['locale_id'],
        ]);
        \DB::table('channel_currencies')->insertOrIgnore([
            'channel_id'  => $id,
            'currency_id' => $support['currency_id'],
        ]);
        \DB::table('channel_inventory_sources')->insertOrIgnore([
            'channel_id'          => $id,
            'inventory_source_id' => $support['source_id'],
        ]);

        return $id;
    }

    protected function validPayload(array $overrides = []): array
    {
        $support = $this->ensureSupportRows();

        return array_merge([
            'code'              => 'ch'.rand(1000, 9999),
            'name'              => 'Test Channel',
            'description'       => 'A channel.',
            'hostname'          => 'h'.rand(1000, 9999).'.example.com',
            'theme'             => 'default',
            'timezone'          => 'UTC',
            'locales'           => [$support['locale_id']],
            'default_locale_id' => $support['locale_id'],
            'currencies'        => [$support['currency_id']],
            'base_currency_id'  => $support['currency_id'],
            'inventory_sources' => [$support['source_id']],
            'root_category_id'  => $support['root_category_id'],
            'seo_title'         => 'SEO Title',
            'seo_description'   => 'SEO Desc',
            'seo_keywords'      => 'seo,key',
            'is_maintenance_on' => false,
        ], $overrides);
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
            'description'     => 'no channel perms',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/settings/channels');
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertChannel();

        $response = $this->adminGet($admin, '/api/admin/settings/channels');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertChannel();

        $response = $this->adminGet($admin, '/api/admin/settings/channels?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'code', 'name', 'hostname']);
    }

    public function test_filter_by_code(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertChannel(['code' => 'zztarget']);
        $this->insertChannel(['code' => 'zzother']);

        $response = $this->adminGet($admin, '/api/admin/settings/channels?code=zztarget');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $unique = 'AlphaXNamed'.uniqid();
        $id = $this->insertChannel(['code' => 'aaa'.uniqid(), 'name' => $unique]);
        $this->insertChannel(['code' => 'bbb'.uniqid(), 'name' => 'BetaThing']);

        $support = $this->ensureSupportRows();
        $localeCode = (string) \DB::table('locales')->where('id', $support['locale_id'])->value('code');

        $response = $this->adminGet($admin, '/api/admin/settings/channels?name='.$unique.'&per_page=50&locale='.$localeCode);

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_filter_by_hostname(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertChannel(['code' => 'hh'.uniqid(), 'hostname' => 'targethost.test']);
        $this->insertChannel(['code' => 'oo'.uniqid(), 'hostname' => 'otherhost.test']);

        $response = $this->adminGet($admin, '/api/admin/settings/channels?hostname=targethost');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_sort_by_code_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertChannel(['code' => 'zzz_sort']);
        $this->insertChannel(['code' => 'aaa_sort']);

        $response = $this->adminGet($admin, '/api/admin/settings/channels?sort=code&order=asc');

        $response->assertOk();
        $codes = collect($response->json('data'))->pluck('code')->all();
        $idxA = array_search('aaa_sort', $codes, true);
        $idxZ = array_search('zzz_sort', $codes, true);

        if ($idxA !== false && $idxZ !== false) {
            expect($idxA)->toBeLessThan($idxZ);
        }
    }

    public function test_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/channels?per_page=999');
        $response->assertOk();
        expect((int) $response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_detail_returns_full_payload(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertChannel(['code' => 'detail'.uniqid(), 'name' => 'Detail Chan']);

        $response = $this->adminGet($admin, '/api/admin/settings/channels/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('localeIds'))->toBeArray();
        expect($response->json('currencyIds'))->toBeArray();
        expect($response->json('inventorySourceIds'))->toBeArray();
        expect($response->json('translations'))->toBeArray();
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/channels/999999');
        $response->assertStatus(404);
    }

    public function test_detail_requires_admin_token(): void
    {
        $id = $this->insertChannel();
        $response = $this->publicGet('/api/admin/settings/channels/'.$id);
        $response->assertStatus(401);
    }

    public function test_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload(['code' => 'createok']);

        $response = $this->adminPost($admin, '/api/admin/settings/channels', $payload);

        $response->assertStatus(201);
        expect($response->json('code'))->toBe('createok');
        $this->assertDatabaseHas('channels', ['code' => 'createok']);
    }

    public function test_create_missing_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload();
        unset($payload['code']);

        $response = $this->adminPost($admin, '/api/admin/settings/channels', $payload);
        $response->assertStatus(422);
    }

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload();
        unset($payload['name']);

        $response = $this->adminPost($admin, '/api/admin/settings/channels', $payload);
        $response->assertStatus(422);
    }

    public function test_create_duplicate_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->insertChannel(['code' => 'dupcode']);

        $response = $this->adminPost($admin, '/api/admin/settings/channels', $this->validPayload(['code' => 'dupcode']));
        $response->assertStatus(422);
    }

    public function test_create_duplicate_hostname_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->insertChannel(['code' => 'h1'.uniqid(), 'hostname' => 'duphost.example.com']);

        $response = $this->adminPost($admin, '/api/admin/settings/channels', $this->validPayload([
            'code'     => 'h2'.uniqid(),
            'hostname' => 'duphost.example.com',
        ]));
        $response->assertStatus(422);
    }

    public function test_create_default_locale_not_in_locales_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload();
        $payload['default_locale_id'] = 999999;

        $response = $this->adminPost($admin, '/api/admin/settings/channels', $payload);
        $response->assertStatus(422);
    }

    public function test_create_base_currency_not_in_currencies_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload();
        $payload['base_currency_id'] = 999999;

        $response = $this->adminPost($admin, '/api/admin/settings/channels', $payload);
        $response->assertStatus(422);
    }

    public function test_create_unknown_root_category_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload(['root_category_id' => 999999]);

        $response = $this->adminPost($admin, '/api/admin/settings/channels', $payload);
        $response->assertStatus(422);
    }

    public function test_create_unknown_locale_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload();
        $payload['locales'] = [999999];
        $payload['default_locale_id'] = 999999;

        $response = $this->adminPost($admin, '/api/admin/settings/channels', $payload);
        $response->assertStatus(422);
    }

    public function test_create_empty_locales_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload();
        $payload['locales'] = [];

        $response = $this->adminPost($admin, '/api/admin/settings/channels', $payload);
        $response->assertStatus(422);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->publicPost('/api/admin/settings/channels', ['code' => 'X']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_create_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $response = $this->adminPost($admin, '/api/admin/settings/channels', $this->validPayload(['code' => 'nopcre']));
        $response->assertStatus(403);
    }

    public function test_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertChannel(['code' => 'updch']);
        $support = $this->ensureSupportRows();

        $response = $this->adminPut($admin, '/api/admin/settings/channels/'.$id, [
            'code'              => 'updch',
            'hostname'          => 'updated.example.com',
            'locales'           => [$support['locale_id']],
            'default_locale_id' => $support['locale_id'],
            'currencies'        => [$support['currency_id']],
            'base_currency_id'  => $support['currency_id'],
            'inventory_sources' => [$support['source_id']],
            'root_category_id'  => $support['root_category_id'],
            'translations'      => [
                'en' => [
                    'name'        => 'After Update',
                    'description' => 'New desc',
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('channels', ['id' => $id, 'hostname' => 'updated.example.com']);
    }

    /**
     * Regression — partial PUT (only `name`) must not crash with
     * `ErrorException: Undefined array key "locales"`. `ChannelRepository::update`
     * calls `$channel->locales()->sync($data['locales'])` unconditionally, so
     * the processor now backfills `locales` / `currencies` / `inventory_sources`
     * from the current channel before forwarding to the repository.
     */
    public function test_partial_update_with_only_name_succeeds(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertChannel(['code' => 'partch']);
        $support = $this->ensureSupportRows();
        \Illuminate\Support\Facades\DB::table('channel_locales')
            ->insertOrIgnore([['channel_id' => $id, 'locale_id' => $support['locale_id']]]);
        \Illuminate\Support\Facades\DB::table('channel_currencies')
            ->insertOrIgnore([['channel_id' => $id, 'currency_id' => $support['currency_id']]]);
        \Illuminate\Support\Facades\DB::table('channel_inventory_sources')
            ->insertOrIgnore([['channel_id' => $id, 'inventory_source_id' => $support['source_id']]]);

        $response = $this->adminPut($admin, '/api/admin/settings/channels/'.$id, [
            'name' => 'Only the name changed',
        ]);

        expect($response->getStatusCode())->not->toBe(500);
        $response->assertOk();
        expect(\Illuminate\Support\Facades\DB::table('channel_locales')
            ->where('channel_id', $id)->count())->toBeGreaterThan(0);
        expect(\Illuminate\Support\Facades\DB::table('channel_currencies')
            ->where('channel_id', $id)->count())->toBeGreaterThan(0);
    }

    public function test_update_duplicate_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $idA = $this->insertChannel(['code' => 'updA']);
        $idB = $this->insertChannel(['code' => 'updB']);
        $support = $this->ensureSupportRows();

        $response = $this->adminPut($admin, '/api/admin/settings/channels/'.$idB, [
            'code'              => 'updA',
            'locales'           => [$support['locale_id']],
            'default_locale_id' => $support['locale_id'],
            'currencies'        => [$support['currency_id']],
            'base_currency_id'  => $support['currency_id'],
            'inventory_sources' => [$support['source_id']],
            'root_category_id'  => $support['root_category_id'],
        ]);

        $response->assertStatus(422);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/settings/channels/999999', ['code' => 'xx']);
        $response->assertStatus(404);
    }

    public function test_update_requires_auth(): void
    {
        $id = $this->insertChannel();
        $response = $this->putJson('/api/admin/settings/channels/'.$id, ['code' => 'x']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_update_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertChannel();
        $response = $this->adminPut($admin, '/api/admin/settings/channels/'.$id, ['code' => 'x']);
        $response->assertStatus(403);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertChannel(['code' => 'keep'.uniqid()]);
        $id = $this->insertChannel(['code' => 'doomed'.uniqid()]);

        $response = $this->adminDelete($admin, '/api/admin/settings/channels/'.$id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('channels', ['id' => $id]);
    }

    public function test_delete_last_channel_returns_400(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertChannel(['code' => 'only'.uniqid()]);
        \DB::table('channels')->where('id', '!=', $id)->delete();

        $response = $this->adminDelete($admin, '/api/admin/settings/channels/'.$id);

        $response->assertStatus(400);
        $this->assertDatabaseHas('channels', ['id' => $id]);
    }

    public function test_delete_default_app_channel_returns_400(): void
    {
        $admin = $this->createAdmin();
        $defaultCode = (string) config('app.channel', 'default');

        $existing = \DB::table('channels')->where('code', $defaultCode)->value('id');
        if (! $existing) {
            $existing = $this->insertChannel(['code' => $defaultCode]);
        }
        $this->insertChannel(['code' => 'sib'.uniqid()]);

        $response = $this->adminDelete($admin, '/api/admin/settings/channels/'.$existing);
        $response->assertStatus(400);
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/channels/999999');
        $response->assertStatus(404);
    }

    public function test_delete_requires_auth(): void
    {
        $id = $this->insertChannel();
        $response = $this->deleteJson('/api/admin/settings/channels/'.$id);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_delete_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $this->insertChannel(['code' => 'k'.uniqid()]);
        $id = $this->insertChannel(['code' => 'np'.uniqid()]);
        $response = $this->adminDelete($admin, '/api/admin/settings/channels/'.$id);
        $response->assertStatus(403);
    }
}
