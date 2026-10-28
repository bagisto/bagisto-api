<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class RmaCustomFieldTest extends AdminApiTestCase
{
    /**
     * Test create select and list.
     */
    public function test_create_select_and_list(): void
    {
        $admin = $this->createAdmin();
        $code = 'gql_'.uniqid();
        $mutation = <<<GQL
            mutation {
              createAdminRmaCustomField(input: {code: "{$code}", label: "GQL field", position: 1, type: "select", status: 1, options: [{name: "New", value: "new"}]}) {
                adminRmaCustomField { _id label options }
              }
            }
        GQL;
        $response = $this->adminGraphQL($mutation, [], $admin);
        $response->assertOk();
        $node = $response->json('data.createAdminRmaCustomField.adminRmaCustomField');
        expect($node['label'])->toBe('GQL field');

        $list = $this->adminGraphQL('query { adminRmaCustomFields { edges { node { _id code } } } }', [], $admin);
        $list->assertOk();
        expect($list->json('data.adminRmaCustomFields.edges'))->toBeArray();
    }

    /**
     * Test update and delete.
     */
    public function test_update_and_delete(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createField('gupd_'.uniqid());
        $newCode = 'gupd_new_'.uniqid();
        $update = <<<GQL
            mutation {
              updateAdminRmaCustomField(input: {id: "/api/admin/rma/custom-fields/{$id}", code: "{$newCode}", label: "Updated", position: 2, type: "text", status: 1}) {
                adminRmaCustomField { _id label }
              }
            }
        GQL;
        $updateResponse = $this->adminGraphQL($update, [], $admin);
        $updateResponse->assertOk();
        expect($updateResponse->json('data.updateAdminRmaCustomField.adminRmaCustomField.label'))->toBe('Updated');

        $delete = <<<GQL
            mutation {
              deleteAdminRmaCustomField(input: {id: "/api/admin/rma/custom-fields/{$id}"}) {
                adminRmaCustomField { _id }
              }
            }
        GQL;
        $deleteResponse = $this->adminGraphQL($delete, [], $admin);
        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('rma_custom_fields', ['id' => $id]);
    }

    /**
     * Test mass delete.
     */
    public function test_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createField('ga_'.uniqid());
        $b = $this->createField('gb_'.uniqid());
        $mutation = <<<GQL
            mutation {
              createAdminRmaCustomFieldMassDelete(input: {indices: [{$a}, {$b}]}) {
                adminRmaCustomFieldMassDelete { deleted message }
              }
            }
        GQL;
        $response = $this->adminGraphQL($mutation, [], $admin);
        $response->assertOk();
        $this->assertDatabaseMissing('rma_custom_fields', ['id' => $a]);
    }

    /**
     * The mass update status mutation switches the given custom fields off and skips unknown ids.
     */
    public function test_mass_update_status_disables_custom_fields(): void
    {
        $a = $this->createField('mass_a_'.uniqid());
        $b = $this->createField('mass_b_'.uniqid());

        $response = $this->massUpdateCustomFieldStatus([
            'indices' => [$a, $b, 999999999],
            'value' => 0,
        ], $this->createAdmin());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createAdminRmaCustomFieldMassUpdateStatus.adminRmaCustomFieldMassUpdateStatus.updated'))->toBe([$a, $b]);
        $this->assertDatabaseHas('rma_custom_fields', [
            'id' => $a,
            'status' => 0,
        ]);
        $this->assertDatabaseHas('rma_custom_fields', [
            'id' => $b,
            'status' => 0,
        ]);
    }

    /**
     * The mass update status mutation rejects a status value other than 0 or 1.
     */
    public function test_mass_update_status_rejects_invalid_value(): void
    {
        $id = $this->createField('mass_a_'.uniqid());

        $response = $this->massUpdateCustomFieldStatus([
            'indices' => [$id],
            'value' => 5,
        ], $this->createAdmin());

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.rma.mass-value-invalid'));
        $this->assertDatabaseHas('rma_custom_fields', [
            'id' => $id,
            'status' => 1,
        ]);
    }

    /**
     * The mass update status mutation is refused for a role without the custom field edit permission.
     */
    public function test_mass_update_status_without_permission_is_refused(): void
    {
        $id = $this->createField('mass_a_'.uniqid());

        $role = Role::create([
            'name' => 'NoRmaCustomFieldEdit '.uniqid(),
            'description' => 'no rma custom field edit',
            'permission_type' => 'custom',
            'permissions' => ['sales', 'sales.rma', 'sales.rma.custom-fields'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $response = $this->massUpdateCustomFieldStatus([
            'indices' => [$id],
            'value' => 0,
        ], $admin, $this->adminAccessToken($admin));

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.rma.no-permission'));
        $this->assertDatabaseHas('rma_custom_fields', [
            'id' => $id,
            'status' => 1,
        ]);
    }

    /**
     * The mass update status mutation rejects a request without an admin token.
     */
    public function test_mass_update_status_requires_authentication(): void
    {
        $id = $this->createField('mass_a_'.uniqid());

        $response = $this->massUpdateCustomFieldStatus([
            'indices' => [$id],
            'value' => 0,
        ]);

        expect($response->json('errors'))->not->toBeNull();
        $this->assertDatabaseHas('rma_custom_fields', [
            'id' => $id,
            'status' => 1,
        ]);
    }

    /**
     * Run the custom field mass update status mutation, optionally as an admin with a given token.
     */
    protected function massUpdateCustomFieldStatus(array $input, ?Admin $admin = null, ?string $token = null): TestResponse
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminRmaCustomFieldMassUpdateStatusInput!) {
              createAdminRmaCustomFieldMassUpdateStatus(input: $input) {
                adminRmaCustomFieldMassUpdateStatus { updated message }
              }
            }
        GQL;

        return $this->adminGraphQL($mutation, ['input' => $input], $admin, $token);
    }

    /**
     * Create field.
     */
    private function createField(string $code, string $type = 'text'): int
    {
        return DB::table('rma_custom_fields')->insertGetId([
            'code' => $code, 'label' => 'Label', 'type' => $type, 'is_required' => 0,
            'position' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
