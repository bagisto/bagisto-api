<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Str;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

class MenuTest extends AdminApiTestCase
{
    public function test_menu_requires_authentication(): void
    {
        $this->publicGet('/api/admin/menu')->assertStatus(401);
    }

    public function test_menu_returns_tree(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/menu');

        $response->assertOk();

        $rows = $response->json();
        expect($rows)->toBeArray()->and($rows)->not->toBeEmpty();

        $payload = $rows[0];
        expect($payload)->toHaveKeys(['id', 'tree']);
        expect($payload['tree'])->toBeArray()->and($payload['tree'])->not->toBeEmpty();

        $keys = collect($payload['tree'])->pluck('key')->all();
        expect($keys)->toContain('sales');
        expect($keys)->toContain('catalog');

        $sales = collect($payload['tree'])->firstWhere('key', 'sales');
        expect($sales)->toHaveKeys(['key', 'label', 'icon', 'sort', 'permission', 'apiResource', 'children']);
        expect($sales['children'])->toBeArray()->and($sales['children'])->not->toBeEmpty();
    }

    public function test_leaf_nodes_carry_api_resource_mapping(): void
    {
        $admin = $this->createAdmin();

        $tree = $this->adminGet($admin, '/api/admin/menu')->json('0.tree');

        $sales = collect($tree)->firstWhere('key', 'sales');
        $orders = collect($sales['children'])->firstWhere('key', 'sales.orders');

        expect($orders['apiResource'])->toBe(['rest' => '/api/admin/orders', 'graphql' => 'adminOrders']);
    }

    public function test_api_resource_is_derived_dynamically_from_metadata(): void
    {
        $admin = $this->createAdmin();

        $tree = $this->adminGet($admin, '/api/admin/menu')->json('0.tree');

        $find = function (array $nodes, string $key) use (&$find) {
            foreach ($nodes as $node) {
                if ($node['key'] === $key) {
                    return $node;
                }

                if ($hit = $find($node['children'] ?? [], $key)) {
                    return $hit;
                }
            }

            return null;
        };

        $families = $find($tree, 'catalog.families');
        expect($families['apiResource'])->toBe(['rest' => '/api/admin/catalog/families', 'graphql' => 'adminAttributeFamilies']);

        $groups = $find($tree, 'customers.groups');
        expect($groups['apiResource'])->toBe(['rest' => '/api/admin/customers/groups', 'graphql' => 'adminCustomerGroups']);

        $dashboard = $find($tree, 'dashboard');
        expect($dashboard['apiResource'])->toBe(['rest' => '/api/admin/dashboard/stats', 'graphql' => 'statsAdminDashboard']);
    }

    public function test_group_headers_have_null_api_resource(): void
    {
        $admin = $this->createAdmin();

        $tree = $this->adminGet($admin, '/api/admin/menu')->json('0.tree');

        $sales = collect($tree)->firstWhere('key', 'sales');
        expect($sales['apiResource'])->toBeNull();
    }

    public function test_menu_is_filtered_to_role_permissions(): void
    {
        $role = Role::create([
            'name'            => 'r-'.Str::random(6),
            'description'     => 'limited',
            'permission_type' => 'custom',
            'permissions'     => ['catalog', 'catalog.products'],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);
        $token = $this->adminTokenSameAsWeb($admin);

        $tree = $this->adminGet($admin, '/api/admin/menu', $token)->json('0.tree');

        $keys = collect($tree)->pluck('key')->all();
        expect($keys)->toContain('catalog');
        expect($keys)->not->toContain('sales');

        $catalog = collect($tree)->firstWhere('key', 'catalog');
        $childKeys = collect($catalog['children'])->pluck('key')->all();
        expect($childKeys)->toContain('catalog.products');
        expect($childKeys)->not->toContain('catalog.categories');
    }
}
