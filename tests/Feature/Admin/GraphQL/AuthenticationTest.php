<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for the Admin API Authentication surface, post 2026-05-27 refactor.
 *
 * Only the profile-read query (`readAdminProfile`) survives — admin clients
 * authenticate via pre-issued integration tokens (AdminPersonalAccessToken
 * → AdminApiGuard). createAdminLogin / createAdminLogout / createAdminForgotPassword
 * / createAdminProfileUpdate were removed.
 */
class AuthenticationTest extends AdminApiTestCase
{
    public function test_profile_query_returns_admin_details(): void
    {
        $admin = $this->createAdmin(['name' => 'GraphQL Admin']);

        $query = <<<'GQL'
            query { readAdminProfile { id name email success } }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();

        $data = $response->json('data.readAdminProfile');
        $this->assertIsArray($data, 'GraphQL response missing data.readAdminProfile: '.json_encode($response->json()));
        $this->assertSame($admin->email, $data['email']);
        $this->assertSame($admin->name, $data['name']);
    }

    public function test_profile_query_requires_authentication(): void
    {
        $query = <<<'GQL'
            query { readAdminProfile { id email } }
        GQL;

        $response = $this->adminGraphQL($query);

        $errors = $response->json('errors');
        $this->assertNotNull($errors, 'expected GraphQL errors[] when no Bearer token was supplied');
        $this->assertNull($response->json('data.readAdminProfile'));
    }

    public function test_removed_login_mutation_no_longer_in_schema(): void
    {
        $mutation = <<<'GQL'
            mutation { createAdminLogin(input: { email: "a@b.co", password: "x" }) { adminLogin { id } } }
        GQL;

        $response = $this->adminGraphQL($mutation);

        // GraphQL returns 200 with errors[] for unknown fields, not 4xx
        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'createAdminLogin should be missing from the schema after the refactor');
    }

    public function test_removed_logout_mutation_no_longer_in_schema(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation { createAdminLogout(input: { all: false }) { adminLogout { success } } }
        GQL;

        $response = $this->adminGraphQL($mutation, [], $admin);

        $this->assertNotEmpty($response->json('errors'), 'createAdminLogout should be missing from the schema after the refactor');
    }

    public function test_removed_forgot_password_mutation_no_longer_in_schema(): void
    {
        $mutation = <<<'GQL'
            mutation { createAdminForgotPassword(input: { email: "a@b.co" }) { adminForgotPassword { success } } }
        GQL;

        $response = $this->adminGraphQL($mutation);

        $this->assertNotEmpty($response->json('errors'), 'createAdminForgotPassword should be missing from the schema after the refactor');
    }

    public function test_removed_update_mutation_no_longer_in_schema(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation { createAdminProfileUpdate(input: { name: "x", currentPassword: "y" }) { adminProfileUpdate { success } } }
        GQL;

        $response = $this->adminGraphQL($mutation, [], $admin);

        $this->assertNotEmpty($response->json('errors'), 'createAdminProfileUpdate should be missing from the schema after the refactor');
    }
}
