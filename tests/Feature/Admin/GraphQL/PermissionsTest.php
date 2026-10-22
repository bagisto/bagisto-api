<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Str;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

class PermissionsTest extends AdminApiTestCase
{
    /**
     * Test permissions query returns effective set.
     */
    public function test_permissions_query_returns_effective_set(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              getAdminPermissions {
                permissionType
                permissions
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();

        $data = $response->json('data.getAdminPermissions');
        expect($data)->not->toBeNull();
        expect($data['permissionType'])->toBe('all');
        expect($data['permissions'])->toBe(['*']);
    }

    /**
     * Test permissions query custom role.
     */
    public function test_permissions_query_custom_role(): void
    {
        $role = Role::create([
            'name' => 'r-'.Str::random(6),
            'description' => 'limited',
            'permission_type' => 'custom',
            'permissions' => ['catalog', 'catalog.products'],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);
        $token = $this->adminAccessToken($admin);

        $query = 'query { getAdminPermissions { permissionType permissions } }';

        $response = $this->adminGraphQL($query, [], $admin, $token);

        $response->assertOk();

        $data = $response->json('data.getAdminPermissions');
        expect($data['permissionType'])->toBe('custom');
        expect($data['permissions'])->toContain('catalog.products');
    }

    /**
     * Test permissions query requires authentication.
     */
    public function test_permissions_query_requires_authentication(): void
    {
        $response = $this->adminGraphQL('query { getAdminPermissions { permissionType } }');

        expect($response->json('data.getAdminPermissions') ?? null)->toBeNull();
    }
}
