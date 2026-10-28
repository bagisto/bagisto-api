<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Webkul\Admin\Services\CacheManagerService;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSchemaResolver;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class ConfigurationTest extends AdminApiTestCase
{
    public function test_menu_requires_authentication(): void
    {
        $this->publicGet('/api/admin/configuration/menu')->assertStatus(401);
    }

    public function test_menu_returns_full_tree(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/configuration/menu');

        $response->assertOk();
        $rows = $response->json();
        expect($rows)->toBeArray()->and($rows)->not->toBeEmpty();
        $first = $rows[0];
        expect($first)->toHaveKeys(['slug', 'tree']);
        expect($first['slug'])->toBeNull();
        expect($first['tree'])->toBeArray()->and($first['tree'])->not->toBeEmpty();
        $keys = collect($first['tree'])->pluck('key')->all();
        expect($keys)->toContain('general');
        expect($keys)->toContain('sales');
    }

    public function test_menu_scoped_by_slug(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/configuration/menu?slug=sales.order_settings');

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['slug'])->toBe('sales.order_settings');
        expect($row['tree'])->toBeArray();
        $node = $row['tree'][0];
        expect($node['key'])->toBe('sales.order_settings');
        $childKeys = collect($node['children'] ?? [])->pluck('key')->all();
        expect($childKeys)->toContain('sales.order_settings.reorder');
    }

    public function test_menu_nonexistent_slug_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/configuration/menu?slug=nope.nada.zilch');

        expect($response->getStatusCode())->toBe(404);
    }

    public function test_menu_fields_carry_full_metadata(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/configuration/menu?slug=sales.order_settings');
        $response->assertOk();
        $node = $response->json('0.tree.0');
        $reorder = collect($node['children'] ?? [])->firstWhere('key', 'sales.order_settings.reorder');
        expect($reorder)->not->toBeNull();
        expect($reorder['fields'])->toBeArray()->and($reorder['fields'])->not->toBeEmpty();

        $adminField = collect($reorder['fields'])->firstWhere('name', 'admin');
        expect($adminField)->not->toBeNull();
        expect($adminField)->toHaveKeys([
            'name', 'code', 'title', 'type', 'default',
            'channelBased', 'localeBased', 'validation', 'options',
        ]);
        expect($adminField['code'])->toBe('sales.order_settings.reorder.admin');
        expect($adminField['type'])->toBe('boolean');
    }

    public function test_menu_include_values_embeds_effective_value(): void
    {
        $admin = $this->createAdmin();

        DB::table('core_config')->where('code', 'sales.order_settings.reorder.admin')->delete();
        DB::table('core_config')->insert([
            'code' => 'sales.order_settings.reorder.admin',
            'value' => '0',
            'channel_code' => null,
            'locale_code' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->adminGet($admin, '/api/admin/configuration/menu?slug=sales.order_settings&include_values=true');
        $response->assertOk();

        $node = $response->json('0.tree.0');
        $reorder = collect($node['children'] ?? [])->firstWhere('key', 'sales.order_settings.reorder');
        $adminField = collect($reorder['fields'])->firstWhere('name', 'admin');
        expect($adminField)->toHaveKey('value');
        expect((string) $adminField['value'])->toBe('0');
    }

    public function test_slugs_endpoint_lists_all_slugs(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/configuration/slugs');

        $response->assertOk();
        $first = $response->json()[0];
        expect($first)->toHaveKeys(['id', 'slugs']);
        expect($first['slugs'])->toBeArray()->and($first['slugs'])->not->toBeEmpty();

        $entry = $first['slugs'][0];
        expect($entry)->toHaveKeys(['slug', 'name', 'hasFields', 'hasChildren']);
        expect($entry['slug'])->not->toBeNull();

        $slugs = collect($first['slugs'])->pluck('slug')->all();
        expect($slugs)->toContain('general');
    }

    public function test_slugs_endpoint_requires_authentication(): void
    {
        $this->publicGet('/api/admin/configuration/slugs')->assertStatus(401);
    }

    public function test_values_requires_authentication(): void
    {
        $this->publicGet('/api/admin/configuration?slug=sales.order_settings')->assertStatus(401);
    }

    public function test_values_missing_slug_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/configuration');
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_values_nonexistent_slug_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/configuration?slug=zzz.nope');
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_values_returns_defaults_when_no_db_row(): void
    {
        $admin = $this->createAdmin();
        DB::table('core_config')->where('code', 'like', 'sales.order_settings.%')->delete();

        $response = $this->adminGet($admin, '/api/admin/configuration?slug=sales.order_settings');
        $response->assertOk();
        $row = $response->json()[0];
        expect($row)->toHaveKeys(['slug', 'channel', 'locale', 'values']);
        expect($row['slug'])->toBe('sales.order_settings');
        expect($row['values'])->toBeArray();
        expect($row['values'])->toHaveKey('sales.order_settings.reorder.admin');
    }

    public function test_values_reads_db_row(): void
    {
        $admin = $this->createAdmin();
        DB::table('core_config')->where('code', 'sales.order_settings.reorder.admin')->delete();
        DB::table('core_config')->insert([
            'code' => 'sales.order_settings.reorder.admin',
            'value' => '1',
            'channel_code' => null,
            'locale_code' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->adminGet($admin, '/api/admin/configuration?slug=sales.order_settings');
        $response->assertOk();
        $values = $response->json('0.values');
        expect((string) $values['sales.order_settings.reorder.admin'])->toBe('1');
    }

    public function test_update_requires_authentication(): void
    {
        $response = $this->publicPost('/api/admin/configuration', [
            'slug' => 'sales.order_settings',
            'values' => ['sales.order_settings.reorder.admin' => '1'],
        ]);
        expect($response->getStatusCode())->toBe(401);
    }

    public function test_update_requires_slug(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'values' => ['sales.order_settings.reorder.admin' => '1'],
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_requires_values(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'slug' => 'sales.order_settings',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_nonexistent_slug_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'slug' => 'doesnt.exist',
            'values' => ['doesnt.exist.field' => '1'],
        ]);
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_update_persists_boolean_value(): void
    {
        $admin = $this->createAdmin();
        DB::table('core_config')->where('code', 'sales.order_settings.reorder.admin')->delete();

        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'slug' => 'sales.order_settings',
            'values' => [
                'sales.order_settings.reorder.admin' => '1',
            ],
        ]);

        $response->assertOk();
        $body = $response->json();
        expect($body['success'])->toBeTrue();
        expect($body['slug'])->toBe('sales.order_settings');
        expect($body['values'])->toHaveKey('sales.order_settings.reorder.admin');

        $row = DB::table('core_config')
            ->where('code', 'sales.order_settings.reorder.admin')
            ->first();
        expect($row)->not->toBeNull();
        expect((string) $row->value)->toBe('1');
    }

    public function test_update_returns_freshly_resolved_values(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'slug' => 'sales.order_settings',
            'values' => [
                'sales.order_settings.reorder.admin' => '0',
                'sales.order_settings.reorder.shop' => '1',
            ],
        ]);

        $response->assertOk();
        $values = $response->json('values');
        expect($values['sales.order_settings.reorder.admin'])->toBe('0');
        expect($values['sales.order_settings.reorder.shop'])->toBe('1');
    }

    public function test_update_scope_escape_rejected(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'slug' => 'sales.order_settings',
            'values' => [
                'catalog.inventory.something' => '0',
            ],
        ]);
        expect($response->getStatusCode())->toBe(422);
        expect((string) $response->json('detail'))->toContain('catalog.inventory.something');
    }

    public function test_update_unknown_field_rejected(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'slug' => 'sales.order_settings',
            'values' => [
                'sales.order_settings.reorder.bogus_field_zzz' => '1',
            ],
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_validation_failure_rejected(): void
    {
        $admin = $this->createAdmin();
        $longString = str_repeat('x', 250);

        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'slug' => 'general.content',
            'values' => [
                'general.content.header_offer.title' => $longString,
            ],
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_channel_scoped_field_writes_with_channel_column(): void
    {
        $admin = $this->createAdmin();
        DB::table('core_config')->where('code', 'general.general.locale_options.weight_unit')->delete();

        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'slug' => 'general.general',
            'channel' => 'default',
            'values' => [
                'general.general.locale_options.weight_unit' => 'lbs',
            ],
        ]);

        $response->assertOk();
        $row = DB::table('core_config')
            ->where('code', 'general.general.locale_options.weight_unit')
            ->first();
        expect($row)->not->toBeNull();
        expect($row->channel_code)->toBe('default');
        expect((string) $row->value)->toBe('lbs');
    }

    public function test_update_upserts_replacing_existing_value(): void
    {
        $admin = $this->createAdmin();
        DB::table('core_config')->where('code', 'sales.order_settings.reorder.admin')->delete();
        DB::table('core_config')->insert([
            'code' => 'sales.order_settings.reorder.admin',
            'value' => '1',
            'channel_code' => null,
            'locale_code' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->adminPost($admin, '/api/admin/configuration', [
            'slug' => 'sales.order_settings',
            'values' => [
                'sales.order_settings.reorder.admin' => '0',
            ],
        ])->assertOk();

        $count = DB::table('core_config')
            ->where('code', 'sales.order_settings.reorder.admin')
            ->whereNull('channel_code')
            ->whereNull('locale_code')
            ->count();
        expect($count)->toBe(1);
        $row = DB::table('core_config')
            ->where('code', 'sales.order_settings.reorder.admin')
            ->first();
        expect((string) $row->value)->toBe('0');
    }

    public function test_update_permission_denied_for_role_without_edit(): void
    {
        $role = Role::create([
            'name' => 'limited-conf',
            'description' => 'No configuration edit',
            'permission_type' => 'custom',
            'permissions' => ['catalog.categories'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'slug' => 'sales.order_settings',
            'values' => [
                'sales.order_settings.reorder.admin' => '1',
            ],
        ]);

        expect($response->getStatusCode())->toBe(403);
    }

    public function test_update_permission_allowed_for_configuration_perm(): void
    {
        $role = Role::create([
            'name' => 'conf-only',
            'description' => 'Configuration only',
            'permission_type' => 'custom',
            'permissions' => ['configuration'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'slug' => 'sales.order_settings',
            'values' => [
                'sales.order_settings.reorder.admin' => '1',
            ],
        ]);

        $response->assertOk();
    }

    public function test_update_multipart_file_upload(): void
    {
        $admin = $this->createAdmin();
        $file = UploadedFile::fake()->image('logo.png', 50, 50);

        $resolver = app(AdminConfigurationSchemaResolver::class);
        $fileField = null;
        $itemKey = null;
        foreach ($resolver->getRoots() as $section) {
            $stack = [$section];
            while ($stack && $fileField === null) {
                $node = array_pop($stack);
                if (! empty($node->fields)) {
                    foreach ($node->fields as $f) {
                        if (in_array($f['type'] ?? null, ['image', 'file'], true)) {
                            $fileField = $node->key.'.'.$f['name'];
                            $itemKey = $node->key;
                            break 2;
                        }
                    }
                }
                foreach ($node->getChildren() as $c) {
                    $stack[] = $c;
                }
            }
            if ($fileField !== null) {
                break;
            }
        }

        if ($fileField === null) {
            $this->markTestSkipped('No file/image-type configuration field registered.');
        }

        $response = $this->call(
            'POST',
            '/api/admin/configuration',
            ['slug' => $itemKey, 'channel' => 'default'],
            [],
            ['values' => [$fileField => $file]],
            [
                'HTTP_AUTHORIZATION' => 'Bearer '.$this->adminToken($admin),
                'HTTP_ACCEPT' => 'application/json',
            ],
        );

        $response->assertOk();
        $row = DB::table('core_config')
            ->where('code', $fileField)
            ->first();
        expect($row)->not->toBeNull();
        expect((string) $row->value)->not->toBe('');
    }

    protected function createConfigurationRoleAdmin(array $permissions): Admin
    {
        $role = Role::create([
            'name' => 'conf-role-'.uniqid(),
            'description' => 'Configuration role',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    public function test_cache_action_runs_through_core_cache_manager(): void
    {
        $this->mock(CacheManagerService::class, function ($mock) {
            $mock->shouldReceive('execute')->once()->with('clear-views')->andReturn([
                'success' => true,
                'message' => 'Action "view:clear" executed successfully.',
                'output' => 'Compiled views cleared successfully.',
                'command' => 'view:clear',
            ]);
        });

        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/configuration/cache', ['action' => 'clear-views']);

        $response->assertOk();
        expect($response->json('success'))->toBeTrue();
        expect($response->json('command'))->toBe('view:clear');
    }

    public function test_cache_unknown_action_returns_422(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/configuration/cache', ['action' => 'drop-database']);

        $response->assertStatus(422);
        expect($response->json('success'))->toBeFalse();
        expect($response->json('command'))->toBe('');
    }

    public function test_cache_missing_action_returns_422(): void
    {
        $admin = $this->createAdmin();

        $this->adminPost($admin, '/api/admin/configuration/cache', [])->assertStatus(422);
    }

    public function test_cache_requires_authentication(): void
    {
        $this->publicPost('/api/admin/configuration/cache', ['action' => 'clear-views'])->assertStatus(401);
    }

    public function test_cache_requires_the_configuration_permission(): void
    {
        $admin = $this->createConfigurationRoleAdmin(['dashboard']);

        $this->adminPost($admin, '/api/admin/configuration/cache', ['action' => 'clear-views'])->assertStatus(403);
    }

    public function test_search_returns_matches_with_slug(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/configuration/search?query=reorder');

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['query'])->toBe('reorder');
        expect($row['results'])->toBeArray()->and($row['results'])->not->toBeEmpty();
        expect($row['results'][0])->toHaveKeys(['title', 'url', 'slug']);
        expect(collect($row['results'])->pluck('slug')->all())->toContain('sales.order_settings');
    }

    public function test_search_without_query_returns_422(): void
    {
        $admin = $this->createAdmin();

        $this->adminGet($admin, '/api/admin/configuration/search')->assertStatus(422);
    }

    public function test_search_requires_authentication(): void
    {
        $this->publicGet('/api/admin/configuration/search?query=reorder')->assertStatus(401);
    }

    public function test_search_requires_the_configuration_permission(): void
    {
        $admin = $this->createConfigurationRoleAdmin(['dashboard']);

        $this->adminGet($admin, '/api/admin/configuration/search?query=reorder')->assertStatus(403);
    }

    protected function configurationBinaryGet(Admin $admin, string $url): TestResponse
    {
        return $this->get($url, array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'application/octet-stream'],
        ));
    }

    public function test_download_streams_the_stored_configuration_file(): void
    {
        Storage::fake();
        Storage::put('configuration/admin-logo.png', 'png-bytes');

        DB::table('core_config')->where('code', 'general.design.admin_logo.logo_image')->delete();
        DB::table('core_config')->insert([
            'code' => 'general.design.admin_logo.logo_image',
            'value' => 'configuration/admin-logo.png',
            'channel_code' => null,
            'locale_code' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->forgetCoreConfigCache();

        $admin = $this->createAdmin();

        $response = $this->configurationBinaryGet($admin, '/api/admin/configuration/download?code=general.design.admin_logo.logo_image');

        $response->assertOk();
        expect($response->headers->get('content-disposition'))->toContain('admin-logo.png');
    }

    public function test_download_without_a_stored_file_returns_404(): void
    {
        Storage::fake();
        DB::table('core_config')->where('code', 'general.design.admin_logo.logo_image')->delete();
        $this->forgetCoreConfigCache();

        $admin = $this->createAdmin();

        $this->configurationBinaryGet($admin, '/api/admin/configuration/download?code=general.design.admin_logo.logo_image')
            ->assertStatus(404);
    }

    public function test_download_of_a_non_file_field_returns_422(): void
    {
        $admin = $this->createAdmin();

        $this->configurationBinaryGet($admin, '/api/admin/configuration/download?code=sales.order_settings.reorder.admin')
            ->assertStatus(422);
    }

    public function test_download_of_an_unknown_field_returns_404(): void
    {
        $admin = $this->createAdmin();

        $this->configurationBinaryGet($admin, '/api/admin/configuration/download?code=nope.nada.zilch')
            ->assertStatus(404);
    }

    public function test_download_requires_authentication(): void
    {
        $this->get('/api/admin/configuration/download?code=general.design.admin_logo.logo_image', ['Accept' => 'application/octet-stream'])
            ->assertStatus(401);
    }

    public function test_download_requires_the_configuration_permission(): void
    {
        $admin = $this->createConfigurationRoleAdmin(['dashboard']);

        $this->configurationBinaryGet($admin, '/api/admin/configuration/download?code=general.design.admin_logo.logo_image')
            ->assertStatus(403);
    }
}
