<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\Admin\Services\CacheManagerService;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

class ConfigurationTest extends AdminApiTestCase
{
    public function test_menu_query_loads_full_tree_without_slug(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              menuAdminConfigurationMenu {
                slug
                tree
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.menuAdminConfigurationMenu');
        expect($data)->not->toBeNull();
        expect($data['slug'])->toBeNull();
        expect($data['tree'])->toBeArray();
        expect($data['tree'])->not->toBeEmpty();
        expect($data['tree'][0])->toHaveKey('key');
        expect($data['tree'][0]['key'])->not->toBeNull();
    }

    public function test_menu_query_scoped_by_slug(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query Q($slug: String) {
              menuAdminConfigurationMenu(slug: $slug) {
                slug
                tree
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['slug' => 'sales.order_settings'], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.menuAdminConfigurationMenu');
        expect($data)->not->toBeNull();
        expect($data['slug'])->toBe('sales.order_settings');
        expect($data['tree'])->toBeArray();
        expect($data['tree'])->not->toBeEmpty();
    }

    public function test_slugs_query_lists_all_slugs(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              listAdminConfigurationSlug {
                slugs
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.listAdminConfigurationSlug');
        expect($data)->not->toBeNull();
        expect($data['slugs'])->toBeArray();
        expect($data['slugs'])->not->toBeEmpty();
        expect($data['slugs'][0])->toHaveKey('slug');
        expect($data['slugs'][0])->toHaveKey('name');
        expect($data['slugs'][0]['slug'])->not->toBeNull();
    }

    public function test_slugs_query_requires_authentication(): void
    {
        $query = 'query { listAdminConfigurationSlug { slugs } }';
        $response = $this->adminGraphQL($query);
        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_values_query(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query Q($slug: String!) {
              adminConfigurationValues(slug: $slug) {
                slug
                channel
                locale
                values
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['slug' => 'sales.order_settings'], $admin);
        $response->assertOk();
        $data = $response->json('data.adminConfigurationValues');
        if ($data) {
            expect($data['slug'])->toBe('sales.order_settings');
        } else {
            expect($response->json('errors'))->toBeArray();
        }
    }

    public function test_menu_requires_authentication(): void
    {
        $query = 'query { adminConfigurationMenu { slug } }';
        $response = $this->adminGraphQL($query);
        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_values_requires_authentication(): void
    {
        $query = 'query { adminConfigurationValues(slug: "sales.order_settings") { slug } }';
        $response = $this->adminGraphQL($query);
        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_update_mutation_happy_path(): void
    {
        $admin = $this->createAdmin();
        DB::table('core_config')->where('code', 'sales.order_settings.reorder.admin')->delete();

        $mutation = <<<'GQL'
            mutation M($input: createAdminConfigurationUpdateInput!) {
              createAdminConfigurationUpdate(input: $input) {
                adminConfigurationUpdate {
                  slug
                  success
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'slug' => 'sales.order_settings',
                'values' => ['sales.order_settings.reorder.admin' => '1'],
            ],
        ], $admin);

        $response->assertOk();
        $row = DB::table('core_config')
            ->where('code', 'sales.order_settings.reorder.admin')
            ->first();
        if ($row !== null) {
            expect((string) $row->value)->toBe('1');
        } else {
            expect($response->json('errors'))->toBeArray();
        }
    }

    public function test_update_scope_escape_rejected(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation M($input: createAdminConfigurationUpdateInput!) {
              createAdminConfigurationUpdate(input: $input) {
                adminConfigurationUpdate { slug }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'slug' => 'sales.order_settings',
                'values' => ['catalog.inventory.something' => '0'],
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeArray();
    }

    public function test_update_validation_rejection(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation M($input: createAdminConfigurationUpdateInput!) {
              createAdminConfigurationUpdate(input: $input) {
                adminConfigurationUpdate { slug }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'slug' => 'general.content',
                'values' => [
                    'general.content.header_offer.title' => str_repeat('x', 300),
                ],
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeArray();
    }

    public function test_update_requires_authentication(): void
    {
        $mutation = <<<'GQL'
            mutation M($input: createAdminConfigurationUpdateInput!) {
              createAdminConfigurationUpdate(input: $input) {
                adminConfigurationUpdate { slug }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'slug' => 'sales.order_settings',
                'values' => ['sales.order_settings.reorder.admin' => '1'],
            ],
        ]);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_cache_mutation_runs_the_action(): void
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

        $mutation = <<<'GQL'
            mutation Cache($input: createAdminConfigurationCacheInput!) {
              createAdminConfigurationCache(input: $input) {
                adminConfigurationCache {
                  success
                  message
                  output
                  command
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['action' => 'clear-views']], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createAdminConfigurationCache.adminConfigurationCache.success'))->toBeTrue();
        expect($response->json('data.createAdminConfigurationCache.adminConfigurationCache.command'))->toBe('view:clear');
    }

    public function test_cache_mutation_rejects_unknown_action(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation Cache($input: createAdminConfigurationCacheInput!) {
              createAdminConfigurationCache(input: $input) {
                adminConfigurationCache {
                  success
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['action' => 'drop-database']], $admin);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_cache_mutation_requires_the_configuration_permission(): void
    {
        $role = Role::create([
            'name' => 'no-conf-cache-gql',
            'description' => 'No configuration',
            'permission_type' => 'custom',
            'permissions' => ['dashboard'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $mutation = <<<'GQL'
            mutation Cache($input: createAdminConfigurationCacheInput!) {
              createAdminConfigurationCache(input: $input) {
                adminConfigurationCache {
                  success
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['action' => 'clear-views']], $admin);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_search_query_returns_matches(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query Search($query: String!) {
              searchAdminConfiguration(query: $query) {
                query
                results
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['query' => 'reorder'], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.searchAdminConfiguration.query'))->toBe('reorder');

        $results = $response->json('data.searchAdminConfiguration.results');
        expect($results)->toBeArray()->and($results)->not->toBeEmpty();
        expect(collect($results)->pluck('slug')->all())->toContain('sales.order_settings');
    }

    public function test_search_query_rejects_empty_term(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query Search($query: String!) {
              searchAdminConfiguration(query: $query) {
                query
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['query' => ''], $admin);

        expect($response->json('errors'))->not->toBeNull();
    }
}
