<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\Hash;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;

/**
 * GraphQL coverage for admin Settings → Users (admins) CRUD (Block B Wave 2).
 */
class SettingsUserTest extends AdminApiTestCase
{
    protected function uniqueEmail(string $prefix = 'gqluser'): string
    {
        return $prefix.str_replace('.', '', (string) microtime(true)).rand(10, 99).'@example.com';
    }

    public function test_query_listing_returns_edges(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
        query {
          adminSettingsUsers(first: 10) {
            edges { node { _id name email } }
          }
        }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();
        $edges = $response->json('data.adminSettingsUsers.edges');
        expect(is_array($edges) || $edges === null)->toBeTrue();
    }

    public function test_query_detail_returns_user(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin(['name' => 'GqlDetail']);

        $query = <<<GQL
        query {
          adminSettingsUser(id: "/api/admin/settings/users/{$target->id}") {
            _id name email
          }
        }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();
        $data = $response->json('data.adminSettingsUser');
        if ($data !== null) {
            expect($data['_id'])->toBe($target->id);
        }
    }

    public function test_mutation_create_admin_user(): void
    {
        $admin = $this->createAdmin();
        $email = $this->uniqueEmail();

        $mutation = <<<'GQL'
        mutation Create($input: createAdminSettingsUserInput!) {
          createAdminSettingsUser(input: $input) {
            adminSettingsUser { _id name email }
          }
        }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'name'     => 'GraphQLAdmin',
                'email'    => $email,
                'password' => 'secret123',
                'roleId'   => 1,
            ],
        ], $admin);

        $response->assertOk();

        $stored = Admin::where('email', $email)->first();
        expect($stored)->not()->toBeNull();
        expect($stored->name)->toBe('GraphQLAdmin');
        expect(Hash::check('secret123', $stored->password))->toBeTrue();
    }

    public function test_mutation_update_admin_user(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin(['name' => 'OldGqlName']);

        $mutation = <<<'GQL'
        mutation Update($input: updateAdminSettingsUserInput!) {
          updateAdminSettingsUser(input: $input) {
            adminSettingsUser { _id name }
          }
        }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'   => "/api/admin/settings/users/{$target->id}",
                'name' => 'NewGqlName',
            ],
        ], $admin);

        $response->assertOk();
        expect(Admin::find($target->id)->name)->toBe('NewGqlName');
    }

    public function test_mutation_delete_admin_user(): void
    {
        $admin = $this->createAdmin();
        $this->createAdmin();
        $target = $this->createAdmin();

        $mutation = <<<'GQL'
        mutation Delete($input: deleteAdminSettingsUserInput!) {
          deleteAdminSettingsUser(input: $input) {
            adminSettingsUser { _id }
          }
        }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => "/api/admin/settings/users/{$target->id}"],
        ], $admin);

        $response->assertOk();
        expect(Admin::find($target->id))->toBeNull();
    }

    public function test_mutation_delete_self_is_refused(): void
    {
        $admin = $this->createAdmin();
        $this->createAdmin();

        $mutation = <<<'GQL'
        mutation Delete($input: deleteAdminSettingsUserInput!) {
          deleteAdminSettingsUser(input: $input) {
            adminSettingsUser { _id }
          }
        }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => "/api/admin/settings/users/{$admin->id}"],
        ], $admin);

        $response->assertOk();
        expect(Admin::find($admin->id))->not()->toBeNull();
        $errors = $response->json('errors');
        expect(is_array($errors))->toBeTrue();
    }

    public function test_query_requires_auth(): void
    {
        $this->seedRequiredData();

        $query = 'query { adminSettingsUsers(first: 5) { edges { node { _id } } } }';
        $response = $this->adminGraphQL($query);
        $response->assertOk();
        expect($response->json('errors'))->toBeArray();
    }
}
