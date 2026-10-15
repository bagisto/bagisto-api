<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class SettingsRoleTest extends AdminApiTestCase
{
    /**
     * The roles listing rejects a request without an admin token.
     */
    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/settings/roles');
        $response->assertStatus(401);
    }

    /**
     * The roles listing returns data with pagination meta.
     */
    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertRole();

        $response = $this->adminGet($admin, '/api/admin/settings/roles');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    /**
     * Each roles listing row carries the expected fields.
     */
    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertRole();

        $response = $this->adminGet($admin, '/api/admin/settings/roles?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'name', 'description', 'permissionType', 'permissions', 'createdAt', 'updatedAt']);
    }

    /**
     * The roles listing filters by name.
     */
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

    /**
     * The roles listing filters by permission type.
     */
    public function test_filter_by_permission_type(): void
    {
        $admin = $this->createAdmin();
        $idAll = $this->insertRole([
            'name' => 'AllRole '.uniqid(),
            'permission_type' => 'all',
            'permissions' => [],
        ]);
        $this->insertRole([
            'name' => 'CustomRole '.uniqid(),
            'permission_type' => 'custom',
        ]);

        $response = $this->adminGet($admin, '/api/admin/settings/roles?permission_type=all');

        $response->assertOk();
        $rows = $response->json('data');
        foreach ($rows as $row) {
            expect($row['permissionType'])->toBe('all');
        }
        expect(collect($rows)->pluck('id')->all())->toContain($idAll);
    }

    /**
     * The roles listing caps per_page at 50.
     */
    public function test_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/roles?per_page=999');
        $response->assertOk();
        expect((int) $response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    /**
     * The role detail returns the role with its permissions.
     */
    public function test_detail_returns_full_payload(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRole([
            'name' => 'DetailRole',
            'permissions' => ['catalog.products', 'catalog.categories'],
        ]);

        $response = $this->adminGet($admin, '/api/admin/settings/roles/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('name'))->toBe('DetailRole');
        expect($response->json('permissions'))->toBe(['catalog.products', 'catalog.categories']);
    }

    /**
     * An unknown role id returns a 404 on detail.
     */
    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/roles/999999');
        $response->assertStatus(404);
    }

    /**
     * The role detail rejects a request without an admin token.
     */
    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertRole();
        $response = $this->publicGet('/api/admin/settings/roles/'.$id);
        $response->assertStatus(401);
    }

    /**
     * A custom role is created with its permissions.
     */
    public function test_create_happy_path_custom(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name' => 'Catalog Manager',
            'description' => 'Manages catalog',
            'permission_type' => 'custom',
            'permissions' => ['catalog.products', 'catalog.categories'],
        ]);

        $response->assertStatus(201);
        expect($response->json('name'))->toBe('Catalog Manager');
        expect($response->json('permissionType'))->toBe('custom');
        expect($response->json('permissions'))->toBe(['catalog.products', 'catalog.categories']);
        $this->assertDatabaseHas('roles', ['name' => 'Catalog Manager']);
    }

    /**
     * An all-access role is created.
     */
    public function test_create_happy_path_all(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name' => 'Super '.uniqid(),
            'description' => 'Full access',
            'permission_type' => 'all',
        ]);

        $response->assertStatus(201);
        expect($response->json('permissionType'))->toBe('all');
    }

    /**
     * Creating a role without a name fails validation.
     */
    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'description' => 'no name',
            'permission_type' => 'all',
        ]);
        $response->assertStatus(422);
    }

    /**
     * Creating a role without a description fails validation.
     */
    public function test_create_missing_description_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name' => 'X',
            'permission_type' => 'all',
        ]);
        $response->assertStatus(422);
    }

    /**
     * Creating a role without a permission type fails validation.
     */
    public function test_create_missing_permission_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name' => 'X',
            'description' => 'd',
        ]);
        $response->assertStatus(422);
    }

    /**
     * Creating a role with an unknown permission type fails validation.
     */
    public function test_create_invalid_permission_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name' => 'X',
            'description' => 'd',
            'permission_type' => 'somethingelse',
        ]);
        $response->assertStatus(422);
    }

    /**
     * Creating a custom role without permissions fails validation.
     */
    public function test_create_custom_without_permissions_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name' => 'X',
            'description' => 'd',
            'permission_type' => 'custom',
        ]);
        $response->assertStatus(422);
    }

    /**
     * Creating a role without authentication is refused.
     */
    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->publicPost('/api/admin/settings/roles', [
            'name' => 'X',
            'description' => 'd',
            'permission_type' => 'all',
        ]);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    /**
     * An admin lacking the role permissions cannot create a role.
     */
    public function test_create_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $response = $this->adminPost($admin, '/api/admin/settings/roles', [
            'name' => 'X',
            'description' => 'd',
            'permission_type' => 'all',
        ]);
        $response->assertStatus(403);
    }

    /**
     * A role is updated with new values.
     */
    public function test_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRole([
            'name' => 'Before',
            'description' => 'old',
        ]);

        $response = $this->adminPut($admin, '/api/admin/settings/roles/'.$id, [
            'name' => 'After',
            'description' => 'new',
            'permission_type' => 'custom',
            'permissions' => ['catalog.products'],
        ]);

        $response->assertOk();
        expect($response->json('name'))->toBe('After');
        $this->assertDatabaseHas('roles', [
            'id' => $id,
            'name' => 'After',
        ]);
    }

    /**
     * The only role giving an admin all access cannot be demoted to custom.
     */
    public function test_update_refuses_demoting_the_only_all_access_role(): void
    {
        $this->seedRequiredData();

        $limitedId = $this->insertRole(['permissions' => ['settings.roles.edit']]);

        \DB::table('admins')->update(['role_id' => $limitedId]);

        $caller = $this->createAdmin(['role_id' => $limitedId]);
        $id = $this->insertRole([
            'permission_type' => 'all',
            'permissions' => [],
        ]);

        $this->createAdmin(['role_id' => $id]);

        $response = $this->adminPut($caller, '/api/admin/settings/roles/'.$id, [
            'name' => 'Demoted',
            'description' => 'd',
            'permission_type' => 'custom',
            'permissions' => ['catalog.products'],
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseHas('roles', [
            'id' => $id,
            'permission_type' => 'all',
        ]);
    }

    /**
     * An all-access role can be demoted while other admins keep all access.
     */
    public function test_update_allows_demoting_all_access_role_when_others_remain(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRole([
            'permission_type' => 'all',
            'permissions' => [],
        ]);

        $this->partialMock(AdminRepository::class, function ($mock) {
            $mock->shouldReceive('countAdminsWithAllAccess')->andReturn(2);
        });

        $response = $this->adminPut($admin, '/api/admin/settings/roles/'.$id, [
            'name' => 'Demoted',
            'description' => 'd',
            'permission_type' => 'custom',
            'permissions' => ['catalog.products'],
        ]);

        $response->assertOk();
    }

    /**
     * Updating a custom role to all access is accepted.
     */
    public function test_update_to_all_clears_permissions(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRole([
            'permission_type' => 'custom',
            'permissions' => ['catalog.products'],
        ]);

        $response = $this->adminPut($admin, '/api/admin/settings/roles/'.$id, [
            'name' => 'After',
            'description' => 'd',
            'permission_type' => 'all',
        ]);

        $response->assertOk();
        expect($response->json('permissionType'))->toBe('all');
    }

    /**
     * Updating a role with an unknown permission type fails validation.
     */
    public function test_update_invalid_permission_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRole();
        $response = $this->adminPut($admin, '/api/admin/settings/roles/'.$id, [
            'name' => 'X',
            'description' => 'd',
            'permission_type' => 'bogus',
        ]);
        $response->assertStatus(422);
    }

    /**
     * An unknown role id returns a 404 on update.
     */
    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/settings/roles/999999', [
            'name' => 'X',
            'description' => 'd',
            'permission_type' => 'all',
        ]);
        $response->assertStatus(404);
    }

    /**
     * Updating a role without authentication is refused.
     */
    public function test_update_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertRole();
        $response = $this->putJson('/api/admin/settings/roles/'.$id, [
            'name' => 'X',
            'description' => 'd',
            'permission_type' => 'all',
        ]);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    /**
     * An admin lacking the role permissions cannot update a role.
     */
    public function test_update_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertRole();
        $response = $this->adminPut($admin, '/api/admin/settings/roles/'.$id, [
            'name' => 'X',
            'description' => 'd',
            'permission_type' => 'all',
        ]);
        $response->assertStatus(403);
    }

    /**
     * An unused role is deleted.
     */
    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertRole();
        $id = $this->insertRole(['name' => 'ToDelete '.uniqid()]);

        $response = $this->adminDelete($admin, '/api/admin/settings/roles/'.$id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('roles', ['id' => $id]);
    }

    /**
     * A role assigned to an admin cannot be deleted.
     */
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

    /**
     * The last remaining role cannot be deleted.
     */
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

    /**
     * An unknown role id returns a 404 on delete.
     */
    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/roles/999999');
        $response->assertStatus(404);
    }

    /**
     * Deleting a role without authentication is refused.
     */
    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertRole();
        $response = $this->deleteJson('/api/admin/settings/roles/'.$id);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    /**
     * An admin lacking the role permissions cannot delete a role.
     */
    public function test_delete_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $this->insertRole();
        $id = $this->insertRole(['name' => 'NoPermDel '.uniqid()]);

        $response = $this->adminDelete($admin, '/api/admin/settings/roles/'.$id);
        $response->assertStatus(403);
    }

    /**
     * An admin with a custom role may not create an all-access role or grant a key they lack.
     */
    public function test_custom_admin_cannot_grant_permissions_they_lack(): void
    {
        $role = Role::create([
            'name' => 'Role manager '.uniqid(),
            'description' => 'd',
            'permission_type' => 'custom',
            'permissions' => ['settings', 'settings.roles', 'settings.roles.create', 'settings.roles.edit'],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);

        $this->adminPost($admin, '/api/admin/settings/roles', [
            'name' => 'Escalated',
            'description' => 'd',
            'permission_type' => 'all',
        ])->assertStatus(403);

        $this->adminPost($admin, '/api/admin/settings/roles', [
            'name' => 'Escalated custom',
            'description' => 'd',
            'permission_type' => 'custom',
            'permissions' => ['settings.users.edit'],
        ])->assertStatus(403);

        $this->adminPut($admin, '/api/admin/settings/roles/'.$role->id, [
            'name' => 'Self upgrade',
            'description' => 'd',
            'permission_type' => 'all',
        ])->assertStatus(403);

        expect(Role::find($role->id)->permission_type)->toBe('custom');
    }

    /**
     * Insert a role row directly and return its id, defaulting to a custom catalog.products role.
     */
    protected function insertRole(array $overrides = []): int
    {
        $perms = $overrides['permissions'] ?? ['catalog.products'];
        unset($overrides['permissions']);

        return \DB::table('roles')->insertGetId(array_merge([
            'name' => 'Test Role '.uniqid(),
            'description' => 'Test description',
            'permission_type' => 'custom',
            'permissions' => json_encode($perms),
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    /**
     * Send an authenticated admin PUT request.
     */
    protected function adminPut(Admin $admin, string $url, array $data = [], ?string $token = null): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    /**
     * Send an authenticated admin DELETE request.
     */
    protected function adminDelete(Admin $admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    /**
     * Create an admin whose custom role grants only catalog.products.
     */
    protected function createAdminWithoutPermissions(): Admin
    {
        $role = Role::create([
            'name' => 'Limited '.uniqid(),
            'description' => 'no role perms',
            'permission_type' => 'custom',
            'permissions' => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }
}
