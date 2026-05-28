<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\Hash;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;

/**
 * REST coverage for the admin Settings → Users (admins) CRUD endpoints
 * (Block B Wave 2).
 *
 * Endpoints:
 *   GET    /api/admin/settings/users
 *   GET    /api/admin/settings/users/{id}
 *   POST   /api/admin/settings/users
 *   PUT    /api/admin/settings/users/{id}
 *   DELETE /api/admin/settings/users/{id}
 */
class SettingsUserTest extends AdminApiTestCase
{
    protected function uniqueEmail(string $prefix = 'user'): string
    {
        return $prefix.str_replace('.', '', (string) microtime(true)).rand(10, 99).'@example.com';
    }

    protected function adminPut(Admin $admin, string $url, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete(Admin $admin, string $url, ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/settings/users');
        $response->assertStatus(401);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/settings/users', [
            'name' => 'X', 'email' => $this->uniqueEmail(), 'password' => 'secret123', 'role_id' => 1,
        ]);
        $response->assertStatus(401);
    }

    public function test_detail_requires_auth(): void
    {
        $admin = $this->createAdmin();
        $response = $this->publicGet('/api/admin/settings/users/'.$admin->id);
        $response->assertStatus(401);
    }

    public function test_delete_requires_auth(): void
    {
        $admin = $this->createAdmin();
        $response = $this->deleteJson('/api/admin/settings/users/'.$admin->id);
        $response->assertStatus(401);
    }

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

    public function test_listing_returns_caller_row(): void
    {
        $admin = $this->createAdmin(['name' => 'CallerAdmin'.rand(1000, 9999)]);
        $response = $this->adminGet($admin, '/api/admin/settings/users?per_page=50');
        $response->assertOk();

        $row = collect($response->json('data'))->firstWhere('id', $admin->id);
        expect($row)->not()->toBeNull();
        expect($row)->toHaveKeys(['id', 'name', 'email', 'roleId', 'roleName', 'status', 'image', 'imageUrl', 'createdAt', 'updatedAt']);
    }

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

    public function test_listing_per_page_above_cap_clamped(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/users?per_page=9999');
        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

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

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/users/9999999');
        $response->assertStatus(404);
    }

    public function test_create_happy_path_returns_201(): void
    {
        $admin = $this->createAdmin();
        $email = $this->uniqueEmail('cr');

        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name'     => 'CreatedAdmin',
            'email'    => $email,
            'password' => 'secret123',
            'role_id'  => 1,
        ]);

        $response->assertStatus(201);
        expect($response->json('id'))->toBeInt();
        expect($response->json('email'))->toBe($email);
        expect($response->json('name'))->toBe('CreatedAdmin');

        $stored = Admin::where('email', $email)->first();
        expect($stored)->not()->toBeNull();
        expect(Hash::check('secret123', $stored->password))->toBeTrue();
    }

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

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'email' => $this->uniqueEmail(), 'password' => 'secret123', 'role_id' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_missing_email_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'password' => 'secret123', 'role_id' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_invalid_email_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'email' => 'not-an-email', 'password' => 'secret123', 'role_id' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_duplicate_email_returns_422(): void
    {
        $admin = $this->createAdmin();
        $existing = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'email' => $existing->email, 'password' => 'secret123', 'role_id' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_missing_password_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'email' => $this->uniqueEmail(), 'role_id' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_short_password_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'email' => $this->uniqueEmail(), 'password' => 'abc', 'role_id' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_missing_role_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'email' => $this->uniqueEmail(), 'password' => 'secret123',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_unknown_role_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/users', [
            'name' => 'X', 'email' => $this->uniqueEmail(), 'password' => 'secret123', 'role_id' => 9999999,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

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

    public function test_update_same_email_excludes_self(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin();

        $response = $this->adminPut($admin, '/api/admin/settings/users/'.$target->id, [
            'email' => $target->email,
            'name'  => $target->name,
        ]);
        $response->assertOk();
    }

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

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/settings/users/9999999', [
            'name' => 'X',
        ]);
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_update_short_password_returns_422(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/settings/users/'.$target->id, [
            'password' => 'abc',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->createAdmin();
        $target = $this->createAdmin();

        $response = $this->adminDelete($admin, '/api/admin/settings/users/'.$target->id);
        $response->assertOk();
        expect(Admin::find($target->id))->toBeNull();
    }

    public function test_delete_self_returns_400(): void
    {
        $admin = $this->createAdmin();
        $this->createAdmin();

        $response = $this->adminDelete($admin, '/api/admin/settings/users/'.$admin->id);
        expect($response->getStatusCode())->toBe(400);
        expect(Admin::find($admin->id))->not()->toBeNull();
    }

    public function test_delete_last_admin_returns_400(): void
    {
        $admin = $this->createAdmin();

        Admin::where('id', '!=', $admin->id)->delete();
        expect(Admin::count())->toBe(1);

        $response = $this->adminDelete($admin, '/api/admin/settings/users/'.$admin->id);
        expect($response->getStatusCode())->toBe(400);
        expect(Admin::find($admin->id))->not()->toBeNull();
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/users/9999999');
        expect($response->getStatusCode())->toBe(404);
    }
}
