<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class SettingsUserTest extends AdminApiTestCase
{
    /**
     * The user listing rejects a request without an admin token.
     */
    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/settings/users');
        $response->assertStatus(401);
    }

    /**
     * Creating a user rejects an unauthenticated request.
     */
    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/settings/users', [
            'name' => 'X', 'email' => $this->uniqueEmail(), 'password' => 'secret123', 'role_id' => 1,
        ]);
        $response->assertStatus(401);
    }

    /**
     * The user detail endpoint rejects an unauthenticated request.
     */
    public function test_detail_requires_auth(): void
    {
        $admin = $this->createAdmin();
        $response = $this->publicGet('/api/admin/settings/users/'.$admin->id);
        $response->assertStatus(401);
    }

    /**
     * Deleting a user rejects an unauthenticated request.
     */
    public function test_delete_requires_auth(): void
    {
        $admin = $this->createAdmin();
        $response = $this->deleteJson('/api/admin/settings/users/'.$admin->id);
        $response->assertStatus(401);
    }

    /**
     * The user listing returns the data array and the pagination meta with default paging.
     */
    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/users');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
        expect($response->json('meta.currentPage'))->toBe(1);
        expect($response->json('meta.perPage'))->toBe(10);
    }

    /**
     * The user listing includes the calling admin with every expected field.
     */
    public function test_listing_returns_caller_row(): void
    {
        $admin = $this->createAdmin(['name' => 'CallerAdmin'.rand(1000, 9999)]);
        $response = $this->adminGet($admin, '/api/admin/settings/users?per_page=50');
        $response->assertOk();

        $row = collect($response->json('data'))->firstWhere('id', $admin->id);
        expect($row)->not()->toBeNull();
        expect($row)->toHaveKeys(['id', 'name', 'email', 'roleId', 'roleName', 'status', 'image', 'imageUrl', 'createdAt', 'updatedAt']);
    }

    /**
     * The user listing filters by email.
     */
    public function test_listing_filter_by_email(): void
    {
        $admin = $this->createAdmin();
        $email1 = $this->uniqueEmail('fa');
        $other = $this->createAdmin(['email' => $email1]);

        $response = $this->adminGet($admin, '/api/admin/settings/users?email='.$email1.'&per_page=50');
        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($other->id);
    }

    /**
     * The user listing filters by name.
     */
    public function test_listing_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $marker = 'NameFilter'.rand(1000, 9999);
        $other = $this->createAdmin(['name' => $marker]);

        $response = $this->adminGet($admin, '/api/admin/settings/users?name='.$marker.'&per_page=50');
        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($other->id);
    }

    /**
     * The user listing filtered by role returns only admins of that role.
     */
    public function test_listing_filter_by_role(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/users?role_id=1&per_page=50');
        $response->assertOk();

        $roleIds = collect($response->json('data'))->pluck('roleId')->unique()->values()->all();
        if (! empty($roleIds)) {
            expect($roleIds)->toBe([1]);
        }
    }

    /**
     * The user listing filtered by status returns disabled admins and omits active ones.
     */
    public function test_listing_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        $disabled = $this->createAdmin(['status' => 0]);

        $response = $this->adminGet($admin, '/api/admin/settings/users?status=0&per_page=50');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($disabled->id);
        expect($ids)->not()->toContain($admin->id);
    }

    /**
     * The user listing sorts by email in ascending order.
     */
    public function test_listing_sort_by_email_asc(): void
    {
        $admin = $this->createAdmin();
        $this->createAdmin(['email' => $this->uniqueEmail('zz')]);
        $this->createAdmin(['email' => $this->uniqueEmail('aa')]);

        $response = $this->adminGet($admin, '/api/admin/settings/users?sort=email-asc&per_page=50');
        $response->assertOk();

        $emails = collect($response->json('data'))->pluck('email')->all();
        $sorted = $emails;
        sort($sorted);
        expect($emails)->toBe($sorted);
    }

    /**
     * A page size above the cap is clamped to at most fifty.
     */
    public function test_listing_per_page_above_cap_clamped(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/users?per_page=9999');
        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    /**
     * The user detail endpoint returns the requested admin.
     */
    public function test_detail_returns_admin(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin(['name' => 'DetailTarget']);

        $response = $this->adminGet($admin, '/api/admin/settings/users/'.$target->id);
        $response->assertOk();
        expect($response->json('id'))->toBe($target->id);
        expect($response->json('name'))->toBe('DetailTarget');
        expect($response->json('email'))->toBe($target->email);
        expect($response->json('roleId'))->toBe(1);
    }

    /**
     * The user detail endpoint returns 404 for an unknown id.
     */
    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/users/9999999');
        $response->assertStatus(404);
    }

    /**
     * Creating a user returns 201 and stores the admin with a hashed password.
     */
    public function test_create_happy_path_returns_201(): void
    {
        $admin = $this->createAdmin();
        $email = $this->uniqueEmail('cr');

        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'CreatedAdmin',
            'email' => $email,
            'password' => 'secret123',
            'role_id' => 1,
        ]);

        $response->assertStatus(201);
        expect($response->json('id'))->toBeInt();
        expect($response->json('email'))->toBe($email);
        expect($response->json('name'))->toBe('CreatedAdmin');

        $stored = Admin::where('email', $email)->first();
        expect($stored)->not()->toBeNull();
        expect(Hash::check('secret123', $stored->password))->toBeTrue();
    }

    /**
     * An admin whose role grants only the core `settings.users.create` permission can create an admin user.
     */
    public function test_role_with_only_users_create_permission_can_create(): void
    {
        $role = Role::create([
            'name' => 'r-'.Str::random(6),
            'description' => 'test',
            'permission_type' => 'custom',
            'permissions' => ['settings.users.create'],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);
        $token = $this->adminToken($admin);
        $email = $this->uniqueEmail('cust');

        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'CustomTokenAdmin',
            'email' => $email,
            'password' => 'secret123',
            'role_id' => $role->id,
        ], $token);

        $response->assertStatus(201);
        expect(Admin::where('email', $email)->exists())->toBeTrue();
    }

    /**
     * Creating a user stores the password hashed, never in plain text.
     */
    public function test_create_hashes_password(): void
    {
        $admin = $this->createAdmin();
        $email = $this->uniqueEmail('hash');

        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'HashUser', 'email' => $email, 'password' => 'pass1234', 'role_id' => 1,
        ]);
        $response->assertStatus(201);

        $stored = Admin::where('email', $email)->first();
        expect($stored->password)->not()->toBe('pass1234');
        expect(Hash::check('pass1234', $stored->password))->toBeTrue();
    }

    /**
     * Creating a user without a name returns 422.
     */
    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'email' => $this->uniqueEmail(), 'password' => 'secret123', 'role_id' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Creating a user without an email returns 422.
     */
    public function test_create_missing_email_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'password' => 'secret123', 'role_id' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Creating a user with a malformed email returns 422.
     */
    public function test_create_invalid_email_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'email' => 'not-an-email', 'password' => 'secret123', 'role_id' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Creating a user with an email already in use returns 422.
     */
    public function test_create_duplicate_email_returns_422(): void
    {
        $admin = $this->createAdmin();
        $existing = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'email' => $existing->email, 'password' => 'secret123', 'role_id' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Creating a user without a password returns 422.
     */
    public function test_create_missing_password_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'email' => $this->uniqueEmail(), 'role_id' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Creating a user with a too-short password returns 422.
     */
    public function test_create_short_password_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'email' => $this->uniqueEmail(), 'password' => 'abc', 'role_id' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Creating a user without a role returns 422.
     */
    public function test_create_missing_role_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'email' => $this->uniqueEmail(), 'password' => 'secret123',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Creating a user with an unknown role returns 422.
     */
    public function test_create_unknown_role_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'email' => $this->uniqueEmail(), 'password' => 'secret123', 'role_id' => 9999999,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Updating a user changes the name.
     */
    public function test_update_changes_name(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin(['name' => 'OldName']);

        $response = $this->adminPut($admin, '/api/admin/settings/users/'.$target->id, [
            'name' => 'NewName',
        ]);
        $response->assertOk();
        expect(Admin::find($target->id)->name)->toBe('NewName');
    }

    /**
     * Updating a user's password stores a new hash of it.
     */
    public function test_update_changes_password(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin();
        $oldHash = $target->password;

        $response = $this->adminPut($admin, '/api/admin/settings/users/'.$target->id, [
            'password' => 'newpassword',
        ]);
        $response->assertOk();

        $fresh = Admin::find($target->id);
        expect($fresh->password)->not()->toBe($oldHash);
        expect(Hash::check('newpassword', $fresh->password))->toBeTrue();
    }

    /**
     * Updating a user's password dispatches the admin password updated event.
     */
    public function test_update_password_dispatches_password_updated_event(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin();

        Event::fake(['admin.password.update.after']);

        $this->adminPut($admin, '/api/admin/settings/users/'.$target->id, [
            'password' => 'newpassword',
        ])->assertOk();

        Event::assertDispatched('admin.password.update.after');
    }

    /**
     * Deactivating the last active all-access admin is refused with 400.
     */
    public function test_update_refuses_deactivating_the_last_active_all_access_admin(): void
    {
        $this->seedRequiredData();

        $limited = $this->limitedRole();

        \DB::table('admins')->update(['role_id' => $limited->id]);

        $caller = $this->createAdmin(['role_id' => $this->allAccessRole()->id]);

        $this->adminPut($caller, '/api/admin/settings/users/'.$caller->id, [
            'status' => 0,
        ])->assertStatus(400);

        expect((int) Admin::find($caller->id)->status)->toBe(1);
    }

    /**
     * An admin who cannot manage the last all-access admin is refused before the last-admin check, as in core.
     */
    public function test_update_of_the_last_all_access_admin_by_an_admin_who_cannot_manage_them_returns_403(): void
    {
        $this->seedRequiredData();

        $limited = $this->limitedRole();

        \DB::table('admins')->update(['role_id' => $limited->id]);

        $caller = $this->createAdmin(['role_id' => $limited->id]);
        $target = $this->createAdmin(['role_id' => $this->allAccessRole()->id]);

        $this->adminPut($caller, '/api/admin/settings/users/'.$target->id, [
            'status' => 0,
        ])->assertStatus(403);

        expect((int) Admin::find($target->id)->status)->toBe(1);
    }

    /**
     * Moving the last all-access admin to another role is refused with 400.
     */
    public function test_update_refuses_moving_the_last_all_access_admin_to_another_role(): void
    {
        $this->seedRequiredData();

        $limited = $this->limitedRole();

        \DB::table('admins')->update(['role_id' => $limited->id]);

        $all = $this->allAccessRole();
        $caller = $this->createAdmin(['role_id' => $all->id]);

        $this->adminPut($caller, '/api/admin/settings/users/'.$caller->id, [
            'role_id' => $limited->id,
        ])->assertStatus(400);

        expect((int) Admin::find($caller->id)->role_id)->toBe((int) $all->id);
    }

    /**
     * Updating a user without a password keeps the stored hash.
     */
    public function test_update_omitted_password_preserves_hash(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin();
        $oldHash = $target->password;

        $response = $this->adminPut($admin, '/api/admin/settings/users/'.$target->id, [
            'name' => 'OnlyNameChange',
        ]);
        $response->assertOk();
        expect(Admin::find($target->id)->password)->toBe($oldHash);
    }

    /**
     * Updating a user with their own email passes the uniqueness check.
     */
    public function test_update_same_email_excludes_self(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin();

        $response = $this->adminPut($admin, '/api/admin/settings/users/'.$target->id, [
            'email' => $target->email,
            'name' => $target->name,
        ]);
        $response->assertOk();
    }

    /**
     * Updating a user with another admin's email returns 422.
     */
    public function test_update_duplicate_email_returns_422(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createAdmin();
        $b = $this->createAdmin();

        $response = $this->adminPut($admin, '/api/admin/settings/users/'.$b->id, [
            'email' => $a->email,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Updating an unknown user returns 404.
     */
    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/settings/users/9999999', [
            'name' => 'X',
        ]);
        expect($response->getStatusCode())->toBe(404);
    }

    /**
     * Updating a user with a too-short password returns 422.
     */
    public function test_update_short_password_returns_422(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/settings/users/'.$target->id, [
            'password' => 'abc',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Deleting another admin removes them.
     */
    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->createAdmin();
        $target = $this->createAdmin();

        $response = $this->adminDelete($admin, '/api/admin/settings/users/'.$target->id);
        $response->assertOk();
        expect(Admin::find($target->id))->toBeNull();
    }

    /**
     * An admin cannot delete themselves through the delete endpoint.
     */
    public function test_delete_self_returns_400(): void
    {
        $admin = $this->createAdmin();
        $this->createAdmin();

        $response = $this->adminDelete($admin, '/api/admin/settings/users/'.$admin->id);
        expect($response->getStatusCode())->toBe(400);
        expect(Admin::find($admin->id))->not()->toBeNull();
    }

    /**
     * The last remaining admin cannot be deleted.
     */
    public function test_delete_last_admin_returns_400(): void
    {
        $admin = $this->createAdmin();

        Admin::where('id', '!=', $admin->id)->delete();
        expect(Admin::count())->toBe(1);

        $response = $this->adminDelete($admin, '/api/admin/settings/users/'.$admin->id);
        expect($response->getStatusCode())->toBe(400);
        expect(Admin::find($admin->id))->not()->toBeNull();
    }

    /**
     * Deleting an unknown user returns 404.
     */
    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/users/9999999');
        expect($response->getStatusCode())->toBe(404);
    }

    /**
     * An admin can delete their own account by confirming the password.
     */
    public function test_self_delete_happy_path(): void
    {
        $this->createAdmin();
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/settings/users/delete-self', [
            'password' => $this->adminPassword,
        ]);

        $response->assertOk();
        expect($response->json('success'))->toBeTrue();
        expect(Admin::find($admin->id))->toBeNull();
    }

    /**
     * Self-deletion with a wrong password returns 422 and keeps the account.
     */
    public function test_self_delete_wrong_password_returns_422(): void
    {
        $this->createAdmin();
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/settings/users/delete-self', [
            'password' => 'definitely-wrong',
        ]);

        expect($response->getStatusCode())->toBe(422);
        expect(Admin::find($admin->id))->not->toBeNull();
    }

    /**
     * Self-deletion without a password returns 422 and keeps the account.
     */
    public function test_self_delete_missing_password_returns_422(): void
    {
        $this->createAdmin();
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/settings/users/delete-self', []);
        expect($response->getStatusCode())->toBe(422);
        expect(Admin::find($admin->id))->not->toBeNull();
    }

    /**
     * The last remaining admin cannot delete their own account.
     */
    public function test_self_delete_last_admin_returns_400(): void
    {
        $admin = $this->createAdmin();
        Admin::where('id', '!=', $admin->id)->delete();

        $response = $this->adminPost($admin, '/api/admin/settings/users/delete-self', [
            'password' => $this->adminPassword,
        ]);

        expect($response->getStatusCode())->toBe(400);
        expect(Admin::find($admin->id))->not->toBeNull();
    }

    /**
     * Self-deletion rejects an unauthenticated request.
     */
    public function test_self_delete_requires_auth(): void
    {
        $response = $this->postJson('/api/admin/settings/users/delete-self', [
            'password' => $this->adminPassword,
        ]);
        expect($response->getStatusCode())->toBe(401);
    }

    /**
     * An admin with a custom role may not assign an all-access role or change an all-access admin.
     */
    public function test_custom_admin_cannot_assign_or_manage_a_wider_role(): void
    {
        $role = Role::create([
            'name' => 'User manager '.Str::random(6),
            'description' => 'd',
            'permission_type' => 'custom',
            'permissions' => ['settings', 'settings.users', 'settings.users.create', 'settings.users.edit'],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);
        $superAdmin = $this->createAdmin(['role_id' => 1]);

        $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'Escalated',
            'email' => $this->uniqueEmail('esc'),
            'password' => 'secret123',
            'role_id' => 1,
        ])->assertStatus(403);

        $this->adminPut($admin, '/api/admin/settings/users/'.$admin->id, [
            'role_id' => 1,
        ])->assertStatus(403);

        $this->adminPut($admin, '/api/admin/settings/users/'.$superAdmin->id, [
            'email' => $this->uniqueEmail('takeover'),
        ])->assertStatus(403);

        expect((int) $admin->fresh()->role_id)->toBe((int) $role->id);
        expect($superAdmin->fresh()->email)->toBe($superAdmin->email);
    }

    /**
     * Build an email address that is unique for this run, using the given prefix.
     */
    protected function uniqueEmail(string $prefix = 'user'): string
    {
        return $prefix.str_replace('.', '', (string) microtime(true)).rand(10, 99).'@example.com';
    }

    /**
     * Send an authenticated admin PUT request as JSON.
     */
    protected function adminPut(Admin $admin, string $url, array $data = [], ?string $token = null): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    /**
     * Send an authenticated admin DELETE request as JSON.
     */
    protected function adminDelete(Admin $admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    /**
     * Create a custom role that may only edit users.
     */
    protected function limitedRole(): Role
    {
        return Role::create([
            'name' => 'Limited '.uniqid(),
            'description' => 'custom',
            'permission_type' => 'custom',
            'permissions' => ['settings.users.edit'],
        ]);
    }

    /**
     * Create a role with the all permission type.
     */
    protected function allAccessRole(): Role
    {
        return Role::create([
            'name' => 'Everything '.uniqid(),
            'description' => 'all',
            'permission_type' => 'all',
            'permissions' => [],
        ]);
    }
}
