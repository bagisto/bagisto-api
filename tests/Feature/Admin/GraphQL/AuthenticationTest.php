<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\Notification;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for the Admin API Authentication group.
 */
class AuthenticationTest extends AdminApiTestCase
{
    // ---------------------------------------------------------------- login

    public function test_login_mutation_succeeds(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation login($input: createAdminLoginInput!) {
              createAdminLogin(input: $input) {
                adminLogin { email token success message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['email' => $admin->email, 'password' => $this->adminPassword],
        ]);

        $data = $response->json('data.createAdminLogin.adminLogin');
        expect($data['success'])->toBeTrue();
        expect($data['token'])->not->toBeEmpty();
        expect($data['email'])->toBe($admin->email);
    }

    public function test_login_mutation_fails_with_wrong_password(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation login($input: createAdminLoginInput!) {
              createAdminLogin(input: $input) {
                adminLogin { success message token }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['email' => $admin->email, 'password' => 'wrong'],
        ]);

        $data = $response->json('data.createAdminLogin.adminLogin');
        expect($data['success'])->toBeFalse();
        expect($data['token'])->toBeEmpty();
    }

    public function test_login_mutation_fails_for_inactive_admin(): void
    {
        $admin = $this->createAdmin(['status' => 0]);

        $mutation = <<<'GQL'
            mutation login($input: createAdminLoginInput!) {
              createAdminLogin(input: $input) {
                adminLogin { success message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['email' => $admin->email, 'password' => $this->adminPassword],
        ]);

        expect($response->json('data.createAdminLogin.adminLogin.success'))->toBeFalse();
    }

    // --------------------------------------------------------------- logout

    public function test_logout_mutation_succeeds_when_authenticated(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation logout($input: createAdminLogoutInput!) {
              createAdminLogout(input: $input) {
                adminLogout { success message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['all' => false]], $admin);

        expect($response->json('data.createAdminLogout.adminLogout.success'))->toBeTrue();
    }

    public function test_logout_mutation_fails_without_authentication(): void
    {
        $mutation = <<<'GQL'
            mutation logout($input: createAdminLogoutInput!) {
              createAdminLogout(input: $input) {
                adminLogout { success message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['all' => false]]);

        expect($response->json('data.createAdminLogout.adminLogout.success'))->toBeFalse();
    }

    // ------------------------------------------------------------- profile

    public function test_profile_query_returns_admin_details(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query { readAdminProfile { id name email success } }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $data = $response->json('data.readAdminProfile');
        expect($data['email'])->toBe($admin->email);
        expect($data['name'])->toBe($admin->name);
    }

    public function test_profile_query_requires_authentication(): void
    {
        $query = <<<'GQL'
            query { readAdminProfile { id email } }
        GQL;

        $response = $this->adminGraphQL($query);

        expect($response->json('errors'))->not->toBeNull();
        expect($response->json('data.readAdminProfile'))->toBeNull();
    }

    // -------------------------------------------------------- profile update

    public function test_update_mutation_changes_the_name(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation update($input: createAdminProfileUpdateInput!) {
              createAdminProfileUpdate(input: $input) {
                adminProfileUpdate { name success message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['name' => 'GraphQL Renamed', 'currentPassword' => $this->adminPassword],
        ], $admin);

        expect($response->json('data.createAdminProfileUpdate.adminProfileUpdate.success'))->toBeTrue();
        expect($admin->fresh()->name)->toBe('GraphQL Renamed');
    }

    public function test_update_mutation_fails_with_wrong_current_password(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation update($input: createAdminProfileUpdateInput!) {
              createAdminProfileUpdate(input: $input) {
                adminProfileUpdate { success message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['name' => 'Nope', 'currentPassword' => 'wrong'],
        ], $admin);

        expect($response->json('errors'))->not->toBeNull();
        expect($admin->fresh()->name)->not->toBe('Nope');
    }

    // ------------------------------------------------------- forgot password

    public function test_forgot_password_mutation_succeeds_for_known_email(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation forgot($input: createAdminForgotPasswordInput!) {
              createAdminForgotPassword(input: $input) {
                adminForgotPassword { success message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['email' => $admin->email]]);

        expect($response->json('data.createAdminForgotPassword.adminForgotPassword.success'))->toBeTrue();
    }

    public function test_forgot_password_mutation_fails_for_unknown_email(): void
    {
        $mutation = <<<'GQL'
            mutation forgot($input: createAdminForgotPasswordInput!) {
              createAdminForgotPassword(input: $input) {
                adminForgotPassword { success message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['email' => 'ghost-'.uniqid().'@example.com'],
        ]);

        expect($response->json('data.createAdminForgotPassword.adminForgotPassword.success'))->toBeFalse();
    }
}
