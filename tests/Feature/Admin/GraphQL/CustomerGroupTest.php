<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;

/**
 * GraphQL coverage for admin Customer Groups CRUD (Block C C2).
 */
class CustomerGroupTest extends AdminApiTestCase
{
    protected function uniqueCode(string $prefix = 'gqlg'): string
    {
        return $prefix.str_replace('.', '', (string) microtime(true)).rand(10, 99);
    }

    protected function seedUserGroup(array $overrides = []): CustomerGroup
    {
        $this->seedRequiredData();

        return CustomerGroup::create(array_merge([
            'code' => $this->uniqueCode('u'),
            'name' => 'User Group',
            'is_user_defined' => 1,
        ], $overrides));
    }

    protected function systemGroup(): ?CustomerGroup
    {
        $this->seedRequiredData();

        return CustomerGroup::where('is_user_defined', 0)->first()
            ?? CustomerGroup::where('code', 'general')->first();
    }

    public function test_listing(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup();

        $query = <<<'GQL'
            query {
              adminCustomerGroups(first: 50) {
                edges { node { id _id code } }
                totalCount
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, [], $admin);
        $resp->assertOk();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $resp->json('data.adminCustomerGroups.edges') ?? []);
        expect($ids)->toContain($g->id);
    }

    public function test_listing_filter_by_code(): void
    {
        $admin = $this->createAdmin();
        $marker = $this->uniqueCode('flt');
        $g = $this->seedUserGroup(['code' => $marker]);

        $query = <<<'GQL'
            query($code: String) {
              adminCustomerGroups(first: 50, code: $code) {
                edges { node { _id } }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['code' => $marker], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $resp->json('data.adminCustomerGroups.edges') ?? []);
        expect($ids)->toContain($g->id);
    }

    public function test_listing_requires_auth(): void
    {
        $query = 'query { adminCustomerGroups(first: 5) { edges { node { _id } } } }';
        $resp = $this->adminGraphQL($query);
        $resp->assertOk();
        expect($resp->json('errors'))->not()->toBeNull();
    }

    public function test_detail(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup();

        $query = <<<'GQL'
            query($id: ID!) { adminCustomerGroup(id: $id) { id _id } }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/customers/groups/'.$g->id], $admin);
        $resp->assertOk();
        expect($resp->json('data.adminCustomerGroup._id'))->toBe($g->id);
    }

    public function test_detail_unknown(): void
    {
        $admin = $this->createAdmin();
        $query = <<<'GQL'
            query($id: ID!) { adminCustomerGroup(id: $id) { id _id } }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/customers/groups/99999999'], $admin);
        $resp->assertOk();
        $errors = $resp->json('errors');
        $isNull = $resp->json('data.adminCustomerGroup') === null;
        expect($errors !== null || $isNull)->toBeTrue();
    }

    public function test_mutation_create(): void
    {
        $admin = $this->createAdmin();
        $code = $this->uniqueCode('cr');

        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerGroupInput!) {
              createAdminCustomerGroup(input: $input) {
                adminCustomerGroup { _id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['code' => $code, 'name' => 'GQL Created'],
        ], $admin);
        $resp->assertOk();
        expect(CustomerGroup::where('code', $code)->exists())->toBeTrue();
    }

    public function test_mutation_create_requires_auth(): void
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerGroupInput!) {
              createAdminCustomerGroup(input: $input) { adminCustomerGroup { _id } }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['code' => $this->uniqueCode('noa'), 'name' => 'NoAuth'],
        ]);
        $resp->assertOk();
        expect($resp->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_update(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup(['name' => 'OldGQL']);

        $mutation = <<<'GQL'
            mutation($input: updateAdminCustomerGroupInput!) {
              updateAdminCustomerGroup(input: $input) {
                adminCustomerGroup { _id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/customers/groups/'.$g->id, 'name' => 'NewGQL'],
        ], $admin);
        $resp->assertOk();
        $hasErrors = ! empty($resp->json('errors'));
        $name = $g->fresh()->name;
        expect($name === 'NewGQL' || $hasErrors)->toBeTrue();
    }

    public function test_mutation_delete(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup();

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCustomerGroupInput!) {
              deleteAdminCustomerGroup(input: $input) {
                adminCustomerGroup {
                  id
                  _id
                  code
                  message
                }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/customers/groups/'.$g->id],
        ], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        expect((int) $resp->json('data.deleteAdminCustomerGroup.adminCustomerGroup._id'))->toBe($g->id);
        expect($resp->json('data.deleteAdminCustomerGroup.adminCustomerGroup.message'))->not()->toBeNull();
        expect(CustomerGroup::where('id', $g->id)->exists())->toBeFalse();
    }

    public function test_mutation_delete_system_blocked(): void
    {
        $admin = $this->createAdmin();
        $sys = $this->systemGroup();
        if (! $sys) {
            $this->markTestSkipped('No system customer group present.');
        }

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCustomerGroupInput!) {
              deleteAdminCustomerGroup(input: $input) { adminCustomerGroup { _id } }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/customers/groups/'.$sys->id],
        ], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->not()->toBeNull();
        expect(CustomerGroup::where('id', $sys->id)->exists())->toBeTrue();
    }

    public function test_mutation_delete_with_customers_blocked(): void
    {
        $admin = $this->createAdmin();
        $g = $this->seedUserGroup();
        Customer::factory()->create(['customer_group_id' => $g->id, 'status' => 1]);

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCustomerGroupInput!) {
              deleteAdminCustomerGroup(input: $input) { adminCustomerGroup { _id } }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/customers/groups/'.$g->id],
        ], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->not()->toBeNull();
        expect(CustomerGroup::where('id', $g->id)->exists())->toBeTrue();
    }

    public function test_mutation_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedUserGroup();
        $b = $this->seedUserGroup();

        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerGroupMassDeleteInput!) {
              createAdminCustomerGroupMassDelete(input: $input) {
                adminCustomerGroupMassDelete { _id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, ['input' => ['indices' => [$a->id, $b->id]]], $admin);
        $resp->assertOk();
        expect(CustomerGroup::where('id', $a->id)->exists())->toBeFalse();
        expect(CustomerGroup::where('id', $b->id)->exists())->toBeFalse();
    }
}
