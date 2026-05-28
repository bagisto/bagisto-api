<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

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
            'code'         => 'sales.order_settings.reorder.admin',
            'value'        => '0',
            'channel_code' => null,
            'locale_code'  => null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $response = $this->adminGet($admin, '/api/admin/configuration/menu?slug=sales.order_settings&include_values=true');
        $response->assertOk();

        $node = $response->json('0.tree.0');
        $reorder = collect($node['children'] ?? [])->firstWhere('key', 'sales.order_settings.reorder');
        $adminField = collect($reorder['fields'])->firstWhere('name', 'admin');
        expect($adminField)->toHaveKey('value');
        expect((string) $adminField['value'])->toBe('0');
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
            'code'         => 'sales.order_settings.reorder.admin',
            'value'        => '1',
            'channel_code' => null,
            'locale_code'  => null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $response = $this->adminGet($admin, '/api/admin/configuration?slug=sales.order_settings');
        $response->assertOk();
        $values = $response->json('0.values');
        expect((string) $values['sales.order_settings.reorder.admin'])->toBe('1');
    }

    public function test_update_requires_authentication(): void
    {
        $response = $this->publicPost('/api/admin/configuration', [
            'slug'   => 'sales.order_settings',
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
            'slug'   => 'doesnt.exist',
            'values' => ['doesnt.exist.field' => '1'],
        ]);
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_update_persists_boolean_value(): void
    {
        $admin = $this->createAdmin();
        DB::table('core_config')->where('code', 'sales.order_settings.reorder.admin')->delete();

        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'slug'   => 'sales.order_settings',
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
            'slug'   => 'sales.order_settings',
            'values' => [
                'sales.order_settings.reorder.admin' => '0',
                'sales.order_settings.reorder.shop'  => '1',
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
            'slug'   => 'sales.order_settings',
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
            'slug'   => 'sales.order_settings',
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
            'slug'   => 'general.content',
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
            'slug'    => 'general.general',
            'channel' => 'default',
            'values'  => [
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
            'code'         => 'sales.order_settings.reorder.admin',
            'value'        => '1',
            'channel_code' => null,
            'locale_code'  => null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->adminPost($admin, '/api/admin/configuration', [
            'slug'   => 'sales.order_settings',
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
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'limited-conf',
            'description'     => 'No configuration edit',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.categories'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'slug'   => 'sales.order_settings',
            'values' => [
                'sales.order_settings.reorder.admin' => '1',
            ],
        ]);

        expect($response->getStatusCode())->toBe(403);
    }

    public function test_update_permission_allowed_for_configuration_perm(): void
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'conf-only',
            'description'     => 'Configuration only',
            'permission_type' => 'custom',
            'permissions'     => ['configuration'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $response = $this->adminPost($admin, '/api/admin/configuration', [
            'slug'   => 'sales.order_settings',
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

        $resolver = app(\Webkul\BagistoApi\Admin\State\AdminConfigurationSchemaResolver::class);
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
                'HTTP_ACCEPT'        => 'application/json',
            ],
        );

        $response->assertOk();
        $row = DB::table('core_config')
            ->where('code', $fileField)
            ->first();
        expect($row)->not->toBeNull();
        expect((string) $row->value)->not->toBe('');
    }
}
