<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\PersonalAccessToken;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for the Admin API Authentication group:
 * login, logout, profile get, profile update, forgot-password.
 */
class AuthenticationTest extends AdminApiTestCase
{
    // ---------------------------------------------------------------- login

    public function test_login_succeeds_with_valid_credentials(): void
    {
        $admin = $this->createAdmin();

        $response = $this->publicPost('/api/admin/login', [
            'email'    => $admin->email,
            'password' => $this->adminPassword,
        ]);

        expect($response->json('success'))->toBeTrue();
        expect($response->json('token'))->not->toBeEmpty();
        expect($response->json('email'))->toBe($admin->email);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $admin = $this->createAdmin();

        $response = $this->publicPost('/api/admin/login', [
            'email'    => $admin->email,
            'password' => 'wrong-password',
        ]);

        expect($response->json('success'))->toBeFalse();
        expect($response->json('token'))->toBeEmpty();
    }

    public function test_login_fails_with_missing_fields(): void
    {
        $response = $this->publicPost('/api/admin/login', ['email' => 'x@y.com']);

        expect($response->json('success'))->toBeFalse();
    }

    public function test_login_fails_for_inactive_admin(): void
    {
        $admin = $this->createAdmin(['status' => 0]);

        $response = $this->publicPost('/api/admin/login', [
            'email'    => $admin->email,
            'password' => $this->adminPassword,
        ]);

        expect($response->json('success'))->toBeFalse();
        expect($response->json('message'))->toBe(trans('bagistoapi::admin.login.account-inactive'));
    }

    // --------------------------------------------------------------- logout

    public function test_logout_revokes_the_current_token(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        $response = $this->adminPost($admin, '/api/admin/logout', [], $token);

        expect($response->json('success'))->toBeTrue();

        // The token row is gone — a follow-up call with it must fail.
        $this->adminGet($admin, '/api/admin/get', $token)->assertStatus(401);
    }

    public function test_logout_without_authentication_fails(): void
    {
        $response = $this->publicPost('/api/admin/logout', []);

        expect($response->json('success'))->toBeFalse();
    }

    public function test_logout_all_revokes_every_token(): void
    {
        $admin = $this->createAdmin();
        $this->adminToken($admin);
        $this->adminToken($admin);
        $current = $this->adminToken($admin);

        $this->adminPost($admin, '/api/admin/logout', ['all' => true], $current);

        expect(PersonalAccessToken::where('tokenable_id', $admin->id)
            ->where('tokenable_type', get_class($admin))->count())->toBe(0);
    }

    // ------------------------------------------------------------ get profile

    public function test_get_profile_returns_admin_details(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/get');

        $response->assertOk();
        expect($response->json('0.email'))->toBe($admin->email);
        expect($response->json('0.name'))->toBe($admin->name);
    }

    public function test_get_profile_requires_authentication(): void
    {
        $this->publicGet('/api/admin/get')->assertStatus(401);
    }

    // --------------------------------------------------------- update profile

    public function test_update_changes_the_name(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/update', [
            'name'            => 'Renamed Admin',
            'currentPassword' => $this->adminPassword,
        ]);

        expect($response->json('success'))->toBeTrue();
        expect($admin->fresh()->name)->toBe('Renamed Admin');
    }

    public function test_update_can_change_the_password(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/update', [
            'currentPassword' => $this->adminPassword,
            'password'        => 'NewPass123!',
            'confirmPassword' => 'NewPass123!',
        ]);

        expect($response->json('success'))->toBeTrue();

        // Old password no longer logs in; the new one does.
        expect($this->publicPost('/api/admin/login', [
            'email' => $admin->email, 'password' => 'NewPass123!',
        ])->json('success'))->toBeTrue();
    }

    public function test_update_fails_with_wrong_current_password(): void
    {
        $admin = $this->createAdmin();

        $this->adminPost($admin, '/api/admin/update', [
            'name'            => 'Hacker',
            'currentPassword' => 'not-the-password',
        ])->assertStatus(400);
    }

    public function test_update_fails_when_password_confirmation_mismatches(): void
    {
        $admin = $this->createAdmin();

        $this->adminPost($admin, '/api/admin/update', [
            'currentPassword' => $this->adminPassword,
            'password'        => 'NewPass123!',
            'confirmPassword' => 'Different123!',
        ])->assertStatus(400);
    }

    public function test_update_fails_with_an_email_already_in_use(): void
    {
        $other = $this->createAdmin();
        $admin = $this->createAdmin();

        $this->adminPost($admin, '/api/admin/update', [
            'email'           => $other->email,
            'currentPassword' => $this->adminPassword,
        ])->assertStatus(400);
    }

    public function test_update_requires_authentication(): void
    {
        $this->publicPost('/api/admin/update', [
            'name'            => 'X',
            'currentPassword' => 'whatever',
        ])->assertStatus(401);
    }

    // ------------------------------------------------------- forgot password

    public function test_forgot_password_sends_link_for_known_email(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();

        $response = $this->publicPost('/api/admin/forgot-password', [
            'email' => $admin->email,
        ]);

        expect($response->json('success'))->toBeTrue();
    }

    public function test_forgot_password_fails_for_unknown_email(): void
    {
        $response = $this->publicPost('/api/admin/forgot-password', [
            'email' => 'nobody-'.uniqid().'@example.com',
        ]);

        expect($response->json('success'))->toBeFalse();
    }

    public function test_forgot_password_fails_without_email(): void
    {
        $response = $this->publicPost('/api/admin/forgot-password', []);

        expect($response->json('success'))->toBeFalse();
    }
}
