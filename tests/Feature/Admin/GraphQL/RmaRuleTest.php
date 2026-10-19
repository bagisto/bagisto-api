<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class RmaRuleTest extends AdminApiTestCase
{
    /**
     * Test create and list.
     */
    public function test_create_and_list(): void
    {
        $admin = $this->createAdmin();
        $mutation = <<<'GQL'
            mutation {
              createAdminRmaRule(input: {name: "GQL rule", description: "d", status: 1, returnPeriod: 10}) {
                adminRmaRule { _id name returnPeriod }
              }
            }
        GQL;
        $response = $this->adminGraphQL($mutation, [], $admin);
        $response->assertOk();
        $node = $response->json('data.createAdminRmaRule.adminRmaRule');
        expect($node['name'])->toBe('GQL rule');
        expect((int) $node['returnPeriod'])->toBe(10);

        $list = $this->adminGraphQL('query { adminRmaRules { edges { node { _id name } } } }', [], $admin);
        $list->assertOk();
        expect($list->json('data.adminRmaRules.edges'))->toBeArray();
    }

    /**
     * Test update and delete.
     */
    public function test_update_and_delete(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createRule('Old-'.uniqid());
        $update = <<<GQL
            mutation {
              updateAdminRmaRule(input: {id: "/api/admin/rma/rules/{$id}", name: "Updated", description: "d2", status: 1, returnPeriod: 5}) {
                adminRmaRule { _id name }
              }
            }
        GQL;
        $updateResponse = $this->adminGraphQL($update, [], $admin);
        $updateResponse->assertOk();
        expect($updateResponse->json('data.updateAdminRmaRule.adminRmaRule.name'))->toBe('Updated');

        $delete = <<<GQL
            mutation {
              deleteAdminRmaRule(input: {id: "/api/admin/rma/rules/{$id}"}) {
                adminRmaRule { _id }
              }
            }
        GQL;
        $deleteResponse = $this->adminGraphQL($delete, [], $admin);
        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('rma_rules', ['id' => $id]);
    }

    /**
     * Test mass delete.
     */
    public function test_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createRule('A-'.uniqid());
        $b = $this->createRule('B-'.uniqid());
        $mutation = <<<GQL
            mutation {
              createAdminRmaRuleMassDelete(input: {indices: [{$a}, {$b}]}) {
                adminRmaRuleMassDelete { deleted message }
              }
            }
        GQL;
        $response = $this->adminGraphQL($mutation, [], $admin);
        $response->assertOk();
        $this->assertDatabaseMissing('rma_rules', ['id' => $a]);
    }

    /**
     * The mass update status mutation switches the given rules off and skips unknown ids.
     */
    public function test_mass_update_status_disables_rules(): void
    {
        $a = $this->createRule('MassA-'.uniqid());
        $b = $this->createRule('MassB-'.uniqid());

        $response = $this->massUpdateRuleStatus([
            'indices' => [$a, $b, 999999999],
            'value' => 0,
        ], $this->createAdmin());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createAdminRmaRuleMassUpdateStatus.adminRmaRuleMassUpdateStatus.updated'))->toBe([$a, $b]);
        $this->assertDatabaseHas('rma_rules', [
            'id' => $a,
            'status' => 0,
        ]);
        $this->assertDatabaseHas('rma_rules', [
            'id' => $b,
            'status' => 0,
        ]);
    }

    /**
     * The mass update status mutation rejects a status value other than 0 or 1.
     */
    public function test_mass_update_status_rejects_invalid_value(): void
    {
        $id = $this->createRule('MassA-'.uniqid());

        $response = $this->massUpdateRuleStatus([
            'indices' => [$id],
            'value' => 5,
        ], $this->createAdmin());

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.rma.mass-value-invalid'));
        $this->assertDatabaseHas('rma_rules', [
            'id' => $id,
            'status' => 1,
        ]);
    }

    /**
     * The mass update status mutation is refused for a role without the rule edit permission.
     */
    public function test_mass_update_status_without_permission_is_refused(): void
    {
        $id = $this->createRule('MassA-'.uniqid());

        $role = Role::create([
            'name' => 'NoRmaRuleEdit '.uniqid(),
            'description' => 'no rma rule edit',
            'permission_type' => 'custom',
            'permissions' => ['sales', 'sales.rma', 'sales.rma.rules'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $response = $this->massUpdateRuleStatus([
            'indices' => [$id],
            'value' => 0,
        ], $admin, $this->adminTokenSameAsWeb($admin));

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.rma.no-permission'));
        $this->assertDatabaseHas('rma_rules', [
            'id' => $id,
            'status' => 1,
        ]);
    }

    /**
     * The mass update status mutation rejects a request without an admin token.
     */
    public function test_mass_update_status_requires_authentication(): void
    {
        $id = $this->createRule('MassA-'.uniqid());

        $response = $this->massUpdateRuleStatus([
            'indices' => [$id],
            'value' => 0,
        ]);

        expect($response->json('errors'))->not->toBeNull();
        $this->assertDatabaseHas('rma_rules', [
            'id' => $id,
            'status' => 1,
        ]);
    }

    /**
     * Run the rule mass update status mutation, optionally as an admin with a given token.
     */
    protected function massUpdateRuleStatus(array $input, ?Admin $admin = null, ?string $token = null): TestResponse
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminRmaRuleMassUpdateStatusInput!) {
              createAdminRmaRuleMassUpdateStatus(input: $input) {
                adminRmaRuleMassUpdateStatus { updated message }
              }
            }
        GQL;

        return $this->adminGraphQL($mutation, ['input' => $input], $admin, $token);
    }

    /**
     * Create rule.
     */
    private function createRule(string $name = 'Rule'): int
    {
        return DB::table('rma_rules')->insertGetId([
            'name' => $name, 'description' => 'desc', 'status' => 1, 'return_period' => 30,
            'default' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
