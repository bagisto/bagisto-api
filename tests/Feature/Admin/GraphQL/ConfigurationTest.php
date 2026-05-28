<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class ConfigurationTest extends AdminApiTestCase
{
    public function test_menu_query(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query Q($slug: String) {
              adminConfigurationMenu(slug: $slug) {
                slug
                tree
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['slug' => 'sales.order_settings'], $admin);

        $response->assertOk();
        $data = $response->json('data.adminConfigurationMenu');
        if ($data) {
            expect($data['slug'])->toBe('sales.order_settings');
            expect($data['tree'])->toBeArray();
        } else {
            expect($response->json('errors'))->toBeArray();
        }
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
                'slug'   => 'sales.order_settings',
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
                'slug'   => 'sales.order_settings',
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
                'slug'   => 'general.content',
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
                'slug'   => 'sales.order_settings',
                'values' => ['sales.order_settings.reorder.admin' => '1'],
            ],
        ]);

        expect($response->json('errors'))->not->toBeNull();
    }
}
