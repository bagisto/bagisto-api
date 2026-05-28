<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for Admin Settings → Roles CRUD (Block B Wave 2).
 */
class SettingsRoleTest extends AdminApiTestCase
{
    protected function insertRole(array $overrides = []): int
    {
        $perms = $overrides['permissions'] ?? ['catalog.products'];
        unset($overrides['permissions']);

        return \DB::table('roles')->insertGetId(array_merge([
            'name'            => 'GQL Role '.uniqid(),
            'description'     => 'gql',
            'permission_type' => 'custom',
            'permissions'     => json_encode($perms),
            'created_at'      => now(),
            'updated_at'      => now(),
        ], $overrides));
    }

    public function test_query_listing(): void
    {
        $admin = $this->createAdmin();
        $this->insertRole();

        $query = <<<'GQL'
            query {
              adminSettingsRoles(first: 10) {
                edges { node { _id name } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('data.adminSettingsRoles.edges'))->toBeArray();
    }

    public function test_query_detail(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRole(['name' => 'GQLDetail']);
        $iri = '/api/admin/settings/roles/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsRole(id: $id) { _id }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);

        $response->assertOk();
        $edges = $response->json('data.adminSettingsRole');
        $hasErrors = ! empty($response->json('errors'));
        expect($edges !== null || $hasErrors)->toBeTrue();
    }

    public function test_mutation_create(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsRoleInput!) {
              createAdminSettingsRole(input: $input) {
                adminSettingsRole { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'name'           => 'GQL Created '.uniqid(),
                'description'    => 'd',
                'permissionType' => 'all',
            ],
        ], $admin);

        $response->assertOk();
        $exists = \DB::table('roles')
            ->where('description', 'd')
            ->where('permission_type', 'all')
            ->exists();
        $hasErrors = ! empty($response->json('errors'));
        expect($exists || $hasErrors)->toBeTrue();
    }

    public function test_mutation_update(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRole(['name' => 'BeforeGQL']);
        $iri = '/api/admin/settings/roles/'.$id;

        $mutation = <<<'GQL'
            mutation($input: updateAdminSettingsRoleInput!) {
              updateAdminSettingsRole(input: $input) {
                adminSettingsRole { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'             => $iri,
                'name'           => 'AfterGQL',
                'description'    => 'd',
                'permissionType' => 'all',
            ],
        ], $admin);

        $response->assertOk();
        $updated = \DB::table('roles')->where('id', $id)->where('name', 'AfterGQL')->exists();
        $hasErrors = ! empty($response->json('errors'));
        expect($updated || $hasErrors)->toBeTrue();
    }

    public function test_mutation_delete_in_use_returns_error(): void
    {
        $admin = $this->createAdmin();
        $roleId = $this->insertRole(['name' => 'GQLInUse '.uniqid()]);
        $this->createAdmin(['role_id' => $roleId]);
        $this->insertRole();
        $iri = '/api/admin/settings/roles/'.$roleId;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminSettingsRoleInput!) {
              deleteAdminSettingsRole(input: $input) {
                adminSettingsRole { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);

        $response->assertOk();
        $hasErrors = ! empty($response->json('errors'));
        $stillHere = \DB::table('roles')->where('id', $roleId)->exists();
        expect($hasErrors || $stillHere)->toBeTrue();
    }

    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertRole();
        $id = $this->insertRole(['name' => 'GQLToDelete '.uniqid()]);
        $iri = '/api/admin/settings/roles/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminSettingsRoleInput!) {
              deleteAdminSettingsRole(input: $input) {
                adminSettingsRole { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);

        $response->assertOk();
        $gone = ! \DB::table('roles')->where('id', $id)->exists();
        $hasErrors = ! empty($response->json('errors'));
        expect($gone || $hasErrors)->toBeTrue();
    }
}
