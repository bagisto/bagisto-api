<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class AuthenticationTest extends AdminApiTestCase
{
    /**
     * Test profile query returns admin details.
     */
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

    /**
     * Test profile query resolves multiword role fields.
     */
    public function test_profile_query_resolves_multiword_role_fields(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query { readAdminProfile { _id roleId roleName } }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $this->assertNull($response->json('errors'));

        $data = $response->json('data.readAdminProfile');
        $this->assertSame($admin->role_id, $data['roleId']);
        $this->assertSame($admin->role?->name, $data['roleName']);
    }

    /**
     * Test profile query requires authentication.
     */
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

    /**
     * Test removed login mutation no longer in schema.
     */
    public function test_removed_login_mutation_no_longer_in_schema(): void
    {
        $mutation = <<<'GQL'
            mutation { createAdminLogin(input: { email: "a@b.co", password: "x" }) { adminLogin { id } } }
        GQL;

        $response = $this->adminGraphQL($mutation);

        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'createAdminLogin should be missing from the schema after the refactor');
    }

    /**
     * Test removed logout mutation no longer in schema.
     */
    public function test_removed_logout_mutation_no_longer_in_schema(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation { createAdminLogout(input: { all: false }) { adminLogout { success } } }
        GQL;

        $response = $this->adminGraphQL($mutation, [], $admin);

        $this->assertNotEmpty($response->json('errors'), 'createAdminLogout should be missing from the schema after the refactor');
    }

    /**
     * Test removed forgot password mutation no longer in schema.
     */
    public function test_removed_forgot_password_mutation_no_longer_in_schema(): void
    {
        $mutation = <<<'GQL'
            mutation { createAdminForgotPassword(input: { email: "a@b.co" }) { adminForgotPassword { success } } }
        GQL;

        $response = $this->adminGraphQL($mutation);

        $this->assertNotEmpty($response->json('errors'), 'createAdminForgotPassword should be missing from the schema after the refactor');
    }

    /**
     * Test removed update mutation no longer in schema.
     */
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
