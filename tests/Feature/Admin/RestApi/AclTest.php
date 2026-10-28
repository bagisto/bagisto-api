<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

class AclTest extends AdminApiTestCase
{
    public function test_returns_the_acl_tree(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/acl');

        $response->assertOk();
        $tree = $response->json('0.tree');
        expect($tree)->toBeArray()->and($tree)->not->toBeEmpty();
        expect($tree[0])->toHaveKeys(['key', 'name', 'route', 'sort', 'children']);

        $keys = collect($tree)->pluck('key')->all();
        expect($keys)->toContain('dashboard')->and($keys)->toContain('settings');

        $sorts = collect($tree)->pluck('sort')->all();
        $sorted = $sorts;
        sort($sorted);
        expect($sorts)->toBe($sorted);

        $sales = collect($tree)->firstWhere('key', 'sales');
        expect(collect($sales['children'])->pluck('key')->all())->toContain('sales.orders');
    }

    public function test_requires_authentication(): void
    {
        $this->publicGet('/api/admin/acl')->assertStatus(401);
    }

    public function test_requires_the_roles_permission(): void
    {
        $role = Role::create([
            'name' => 'no-roles',
            'description' => 'No roles access',
            'permission_type' => 'custom',
            'permissions' => ['dashboard'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $this->adminGet($admin, '/api/admin/acl')->assertStatus(403);
    }

    public function test_allowed_with_the_roles_permission(): void
    {
        $role = Role::create([
            'name' => 'roles-only',
            'description' => 'Roles access',
            'permission_type' => 'custom',
            'permissions' => ['settings', 'settings.roles'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $this->adminGet($admin, '/api/admin/acl')->assertOk();
    }
}
