<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for the Admin API Authentication surface, post 2026-05-27 refactor.
 *
 * Only the profile-read endpoint (`GET /api/admin/get`) survives — admin
 * clients authenticate via pre-issued integration tokens (AdminPersonalAccessToken
 * → AdminApiGuard). Login / Logout / ForgotPassword / ProfileUpdate were removed.
 */
class AuthenticationTest extends AdminApiTestCase
{
    public function test_get_profile_returns_admin_details(): void
    {
        $admin = $this->createAdmin(['name' => 'API Profile Admin']);
        $token = $this->adminToken($admin);

        $response = $this->adminGet($admin, '/api/admin/get', $token);

        $response->assertOk();

        $body = $response->json();
        $row = is_array($body) && isset($body[0]) ? $body[0] : $body;

        $this->assertSame((string) $admin->id, $row['id'] ?? null);
        $this->assertSame($admin->name, $row['name'] ?? null);
        $this->assertSame($admin->email, $row['email'] ?? null);
    }

    public function test_get_profile_requires_bearer_token(): void
    {
        $response = $this->publicGet('/api/admin/get');

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_get_profile_rejects_garbage_bearer_token(): void
    {
        $response = $this->getJson('/api/admin/get', [
            'Authorization' => 'Bearer not-a-real-token',
        ]);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_removed_login_endpoint_no_longer_exists(): void
    {
        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'whatever',
        ]);

        $this->assertContains($response->getStatusCode(), [404, 405, 500]);
    }

    public function test_removed_logout_endpoint_no_longer_exists(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        $response = $this->postJson('/api/admin/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $this->assertContains($response->getStatusCode(), [404, 405, 500]);
    }

    public function test_removed_forgot_password_endpoint_no_longer_exists(): void
    {
        $response = $this->postJson('/api/admin/forgot-password', [
            'email' => 'admin@example.com',
        ]);

        $this->assertContains($response->getStatusCode(), [404, 405, 500]);
    }

    public function test_removed_update_endpoint_no_longer_exists(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        $response = $this->postJson('/api/admin/update', [
            'name' => 'Renamed',
            'email' => $admin->email,
            'currentPassword' => $this->adminPassword,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $this->assertContains($response->getStatusCode(), [404, 405, 500]);
    }
}
