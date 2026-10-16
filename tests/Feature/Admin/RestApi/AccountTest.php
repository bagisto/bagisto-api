<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;

class AccountTest extends AdminApiTestCase
{
    private function put(Admin $admin, array $data)
    {
        return $this->putJson('/api/admin/account', $data, $this->adminHeaders($admin));
    }

    public function test_update_changes_name_and_email(): void
    {
        $admin = $this->createAdmin();

        $response = $this->put($admin, [
            'name' => 'Renamed Admin',
            'email' => 'renamed-'.uniqid().'@example.com',
            'current_password' => $this->adminPassword,
        ]);

        $response->assertOk();
        $this->assertSame('Renamed Admin', $response->json('name'));
        $this->assertSame('Renamed Admin', $admin->fresh()->name);
    }

    public function test_update_rejects_wrong_current_password(): void
    {
        $admin = $this->createAdmin(['name' => 'Original']);

        $response = $this->put($admin, ['name' => 'Changed', 'current_password' => 'wrong-password']);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('Original', $admin->fresh()->name);
    }

    public function test_update_requires_current_password(): void
    {
        $response = $this->put($this->createAdmin(), ['name' => 'Changed']);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_update_rejects_taken_email(): void
    {
        $other = $this->createAdmin();

        $response = $this->put($this->createAdmin(), [
            'name' => 'Admin',
            'email' => $other->email,
            'current_password' => $this->adminPassword,
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_password_change_fires_event(): void
    {
        Event::fake(['admin.password.update.after']);

        $admin = $this->createAdmin();

        $response = $this->put($admin, [
            'name' => $admin->name,
            'password' => 'new-secret',
            'password_confirmation' => 'new-secret',
            'current_password' => $this->adminPassword,
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('new-secret', $admin->fresh()->password));
        Event::assertDispatched('admin.password.update.after');
    }

    public function test_password_change_requires_confirmation(): void
    {
        $admin = $this->createAdmin();

        $response = $this->put($admin, [
            'name' => $admin->name,
            'password' => 'new-secret',
            'password_confirmation' => 'different',
            'current_password' => $this->adminPassword,
        ]);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertTrue(Hash::check($this->adminPassword, $admin->fresh()->password));
    }

    public function test_avatar_upload_and_removal(): void
    {
        Storage::fake();

        $admin = $this->createAdmin();

        $response = $this->post('/api/admin/account/image', [
            'image' => UploadedFile::fake()->image('avatar.png'),
            'current_password' => $this->adminPassword,
        ], $this->adminHeaders($admin) + ['Accept' => 'application/json']);

        $response->assertOk();

        $path = $admin->fresh()->image;

        $this->assertNotNull($path);
        Storage::assertExists($path);

        $response = $this->deleteJson('/api/admin/account/image', ['current_password' => $this->adminPassword], $this->adminHeaders($admin));

        $response->assertOk();
        $this->assertNull($admin->fresh()->image);
        Storage::assertMissing($path);
    }

    public function test_avatar_upload_rejects_non_image(): void
    {
        Storage::fake();

        $response = $this->post('/api/admin/account/image', [
            'image' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain'),
            'current_password' => $this->adminPassword,
        ], $this->adminHeaders($this->createAdmin()) + ['Accept' => 'application/json']);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_requires_authentication(): void
    {
        $this->assertSame(401, $this->putJson('/api/admin/account', ['name' => 'x'])->getStatusCode());
    }
}
