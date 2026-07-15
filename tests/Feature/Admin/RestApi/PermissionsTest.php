<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Str;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

class PermissionsTest extends AdminApiTestCase
{
    public function test_permissions_requires_authentication(): void
    {
        $this->publicGet('/api/admin/permissions')->assertStatus(401);
    }

    public function test_all_access_admin_gets_wildcard(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/permissions');

        $response->assertOk();

        $payload = $response->json('0');
        expect($payload)->toHaveKeys(['id', 'permissionType', 'permissions']);
        expect($payload['permissionType'])->toBe('all');
        expect($payload['permissions'])->toBe(['*']);
    }

    public function test_custom_role_returns_granted_keys(): void
    {
        $role = Role::create([
            'name' => 'r-'.Str::random(6),
            'description' => 'limited',
            'permission_type' => 'custom',
            'permissions' => ['catalog', 'catalog.products'],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);
        $token = $this->adminTokenSameAsWeb($admin);

        $payload = $this->adminGet($admin, '/api/admin/permissions', $token)->json('0');

        expect($payload['permissionType'])->toBe('custom');
        expect($payload['permissions'])->toContain('catalog.products');
        expect($payload['permissions'])->not->toContain('sales.orders');
    }
}
