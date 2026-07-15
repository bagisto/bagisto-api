<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Str;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

class MenuTest extends AdminApiTestCase
{
    public function test_admin_menu_query_returns_tree(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              getAdminMenu {
                id
                tree
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();

        $data = $response->json('data.getAdminMenu');
        expect($data)->not->toBeNull();
        expect($data['tree'])->toBeArray()->and($data['tree'])->not->toBeEmpty();

        $keys = collect($data['tree'])->pluck('key')->all();
        expect($keys)->toContain('sales');
        expect($keys)->toContain('catalog');
    }

    public function test_admin_menu_query_filtered_to_role(): void
    {
        $role = Role::create([
            'name' => 'r-'.Str::random(6),
            'description' => 'limited',
            'permission_type' => 'custom',
            'permissions' => ['catalog', 'catalog.products'],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);
        $token = $this->adminTokenSameAsWeb($admin);

        $query = 'query { getAdminMenu { tree } }';

        $response = $this->adminGraphQL($query, [], $admin, $token);

        $response->assertOk();

        $tree = $response->json('data.getAdminMenu.tree');
        expect($tree)->toBeArray();

        $keys = collect($tree)->pluck('key')->all();
        expect($keys)->toContain('catalog');
        expect($keys)->not->toContain('sales');
    }

    public function test_admin_menu_query_requires_authentication(): void
    {
        $response = $this->adminGraphQL('query { getAdminMenu { tree } }');

        expect($response->json('data.getAdminMenu') ?? null)->toBeNull();
    }
}
