<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for Admin Settings → Roles CRUD (Block B Wave 2).
 */
class SettingsRoleTest extends AdminApiTestCase
{
    protected function insertRole(array $overrides = []): int
    {
        $perms = $overrides['permissions'] ?? ['catalog.products'];
        unset($overrides['permissions']);

        return \DB::table('roles')->insertGetId(array_merge([
            'name'            => 'Test Role '.uniqid(),
            'description'     => 'Test description',
            'permission_type' => 'custom',
            'permissions'     => json_encode($perms),
            'created_at'      => now(),
            'updated_at'      => now(),
        ], $overrides));
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
            'description'     => 'no role perms',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/settings/roles');
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertRole();

        $response = $this->adminGet($admin, '/api/admin/settings/roles');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertRole();

        $response = $this->adminGet($admin, '/api/admin/settings/roles?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'name', 'description', 'permissionType', 'permissions', 'createdAt', 'updatedAt']);
    }

    public function test_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRole(['name' => 'AlphaRoleXYZ']);
        $this->insertRole(['name' => 'BetaRole']);

        $response = $this->adminGet($admin, '/api/admin/settings/roles?name=AlphaRoleXYZ');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_filter_by_permission_type(): void
    {
        $admin = $this->createAdmin();
        $idAll = $this->insertRole(['name' => 'AllRole '.uniqid(), 'permission_type' => 'all', 'permissions' => []]);
        $this->insertRole(['name' => 'CustomRole '.uniqid(), 'permission_type' => 'custom']);

        $response = $this->adminGet($admin, '/api/admin/settings/roles?permission_type=all');

        $response->assertOk();
        $rows = $response->json('data');
        foreach ($rows as $row) {
            expect($row['permissionType'])->toBe('all');
        }
        expect(collect($rows)->pluck('id')->all())->toContain($idAll);
    }

    public function test_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/roles?per_page=999');
        $response->assertOk();
        expect((int) $response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_detail_returns_full_payload(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRole(['name' => 'DetailRole', 'permissions' => ['catalog.products', 'catalog.categories']]);

        $response = $this->adminGet($admin, '/api/admin/settings/roles/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('name'))->toBe('DetailRole');
        expect($response->json('permissions'))->toBe(['catalog.products', 'catalog.categories']);
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/roles/999999');
        $response->assertStatus(404);
    }

    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertRole();
        $response = $this->publicGet('/api/admin/settings/roles/'.$id);
        $response->assertStatus(401);
    }

    public function test_create_happy_path_custom(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name'            => 'Catalog Manager',
            'description'     => 'Manages catalog',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products', 'catalog.categories'],
        ]);

        $response->assertStatus(201);
        expect($response->json('name'))->toBe('Catalog Manager');
        expect($response->json('permissionType'))->toBe('custom');
        expect($response->json('permissions'))->toBe(['catalog.products', 'catalog.categories']);
        $this->assertDatabaseHas('roles', ['name' => 'Catalog Manager']);
    }

    public function test_create_happy_path_all(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name'            => 'Super '.uniqid(),
            'description'     => 'Full access',
            'permission_type' => 'all',
        ]);

        $response->assertStatus(201);
        expect($response->json('permissionType'))->toBe('all');
    }

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'description'     => 'no name',
            'permission_type' => 'all',
        ]);
        $response->assertStatus(422);
    }

    public function test_create_missing_description_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name'            => 'X',
            'permission_type' => 'all',
        ]);
        $response->assertStatus(422);
    }

    public function test_create_missing_permission_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name'        => 'X',
            'description' => 'd',
        ]);
        $response->assertStatus(422);
    }

    public function test_create_invalid_permission_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name'            => 'X',
            'description'     => 'd',
            'permission_type' => 'somethingelse',
        ]);
        $response->assertStatus(422);
    }

    public function test_create_custom_without_permissions_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name'            => 'X',
            'description'     => 'd',
            'permission_type' => 'custom',
        ]);
        $response->assertStatus(422);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->publicPost('/api/admin/settings/roles', ['name' => 'X', 'description' => 'd', 'permission_type' => 'all']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_create_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name'            => 'X',
            'description'     => 'd',
            'permission_type' => 'all',
        ]);
        $response->assertStatus(403);
    }

    public function test_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRole(['name' => 'Before', 'description' => 'old']);

        $response = $this->adminPut($admin, '/api/admin/settings/roles/'.$id, [
            'name'            => 'After',
            'description'     => 'new',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products'],
        ]);

        $response->assertOk();
        expect($response->json('name'))->toBe('After');
        $this->assertDatabaseHas('roles', ['id' => $id, 'name' => 'After']);
    }

    public function test_update_to_all_clears_permissions(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRole(['permission_type' => 'custom', 'permissions' => ['catalog.products']]);

        $response = $this->adminPut($admin, '/api/admin/settings/roles/'.$id, [
            'name'            => 'After',
            'description'     => 'd',
            'permission_type' => 'all',
        ]);

        $response->assertOk();
        expect($response->json('permissionType'))->toBe('all');
    }

    public function test_update_invalid_permission_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRole();
        $response = $this->adminPut($admin, '/api/admin/settings/roles/'.$id, [
            'name'            => 'X',
            'description'     => 'd',
            'permission_type' => 'bogus',
        ]);
        $response->assertStatus(422);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/settings/roles/999999', [
            'name'            => 'X',
            'description'     => 'd',
            'permission_type' => 'all',
        ]);
        $response->assertStatus(404);
    }

    public function test_update_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertRole();
        $response = $this->putJson('/api/admin/settings/roles/'.$id, ['name' => 'X', 'description' => 'd', 'permission_type' => 'all']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_update_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertRole();
        $response = $this->adminPut($admin, '/api/admin/settings/roles/'.$id, [
            'name'            => 'X',
            'description'     => 'd',
            'permission_type' => 'all',
        ]);
        $response->assertStatus(403);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertRole();
        $id = $this->insertRole(['name' => 'ToDelete '.uniqid()]);

        $response = $this->adminDelete($admin, '/api/admin/settings/roles/'.$id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('roles', ['id' => $id]);
    }

    public function test_delete_in_use_returns_400(): void
    {
        $admin = $this->createAdmin();
        $roleId = $this->insertRole(['name' => 'InUseRole '.uniqid()]);
        $this->createAdmin(['role_id' => $roleId]);
        $this->insertRole();

        $response = $this->adminDelete($admin, '/api/admin/settings/roles/'.$roleId);

        $response->assertStatus(400);
        $this->assertDatabaseHas('roles', ['id' => $roleId]);
    }

    public function test_delete_last_role_returns_400(): void
    {
        $admin = $this->createAdmin();
        $survivorId = $admin->role_id;
        \DB::table('admins')->where('id', '!=', $admin->id)->delete();
        \DB::table('roles')->where('id', '!=', $survivorId)->delete();

        $response = $this->adminDelete($admin, '/api/admin/settings/roles/'.$survivorId);

        $response->assertStatus(400);
        $this->assertDatabaseHas('roles', ['id' => $survivorId]);
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/roles/999999');
        $response->assertStatus(404);
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertRole();
        $response = $this->deleteJson('/api/admin/settings/roles/'.$id);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_delete_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $this->insertRole();
        $id = $this->insertRole(['name' => 'NoPermDel '.uniqid()]);

        $response = $this->adminDelete($admin, '/api/admin/settings/roles/'.$id);
        $response->assertStatus(403);
    }
}
