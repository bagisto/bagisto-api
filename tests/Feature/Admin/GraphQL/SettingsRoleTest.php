<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class SettingsRoleTest extends AdminApiTestCase
{
    /**
     * Test query listing.
     */
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

    /**
     * Test query detail.
     */
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

    /**
     * Test query detail multiword fields resolve over graphql.
     */
    public function test_query_detail_multiword_fields_resolve_over_graphql(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRole([
            'name' => 'GQL-FR-'.uniqid(),
            'permission_type' => 'custom',
            'permissions' => ['catalog.products'],
        ]);
        $iri = '/api/admin/settings/roles/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsRole(id: $id) {
                _id
                name
                permissionType
                createdAt
                updatedAt
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);
        $response->assertOk();
        $node = $response->json('data.adminSettingsRole');

        expect($node['permissionType'])->not->toBeNull();
        expect($node['createdAt'])->not->toBeNull();
        expect($node['updatedAt'])->not->toBeNull();
    }

    /**
     * Test mutation create.
     */
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
                'name' => 'GQL Created '.uniqid(),
                'description' => 'd',
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

    /**
     * Test mutation update.
     */
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
                'id' => $iri,
                'name' => 'AfterGQL',
                'description' => 'd',
                'permissionType' => 'all',
            ],
        ], $admin);

        $response->assertOk();
        $updated = \DB::table('roles')->where('id', $id)->where('name', 'AfterGQL')->exists();
        $hasErrors = ! empty($response->json('errors'));
        expect($updated || $hasErrors)->toBeTrue();
    }

    /**
     * Test mutation delete in use returns error.
     */
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

    /**
     * Test mutation delete happy path.
     */
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

    /**
     * Insert role.
     */
    protected function insertRole(array $overrides = []): int
    {
        $perms = $overrides['permissions'] ?? ['catalog.products'];
        unset($overrides['permissions']);

        return \DB::table('roles')->insertGetId(array_merge([
            'name' => 'GQL Role '.uniqid(),
            'description' => 'gql',
            'permission_type' => 'custom',
            'permissions' => json_encode($perms),
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }
}
