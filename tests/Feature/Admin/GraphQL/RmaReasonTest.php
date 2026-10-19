<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class RmaReasonTest extends AdminApiTestCase
{
    /**
     * Test create and list.
     */
    public function test_create_and_list(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation {
              createAdminRmaReason(input: {title: "GQL reason", status: 1, position: 3, resolutionType: ["return"]}) {
                adminRmaReason { _id title status resolutionType }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [], $admin);
        $response->assertOk();
        $node = $response->json('data.createAdminRmaReason.adminRmaReason');
        expect($node['title'])->toBe('GQL reason');
        expect($node['resolutionType'])->toContain('return');

        $list = $this->adminGraphQL('query { adminReturnReasonsList: adminRmaReasons { edges { node { _id title } } } }', [], $admin);
        $list->assertOk();
        expect($list->json('data.adminReturnReasonsList.edges'))->toBeArray();
    }

    /**
     * Test update and delete.
     */
    public function test_update_and_delete(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createReason('Old');

        $update = <<<GQL
            mutation {
              updateAdminRmaReason(input: {id: "/api/admin/rma/reasons/{$id}", title: "Updated", status: 1, position: 2, resolutionType: ["cancel_items"]}) {
                adminRmaReason { _id title resolutionType }
              }
            }
        GQL;

        $updateResponse = $this->adminGraphQL($update, [], $admin);
        $updateResponse->assertOk();
        expect($updateResponse->json('data.updateAdminRmaReason.adminRmaReason.title'))->toBe('Updated');
        expect($updateResponse->json('data.updateAdminRmaReason.adminRmaReason.resolutionType'))->toBe(['cancel_items']);

        $delete = <<<GQL
            mutation {
              deleteAdminRmaReason(input: {id: "/api/admin/rma/reasons/{$id}"}) {
                adminRmaReason { _id }
              }
            }
        GQL;

        $deleteResponse = $this->adminGraphQL($delete, [], $admin);
        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('rma_reasons', ['id' => $id]);
    }

    /**
     * Test mass delete.
     */
    public function test_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createReason('A');
        $b = $this->createReason('B');

        $mutation = <<<GQL
            mutation {
              createAdminRmaReasonMassDelete(input: {indices: [{$a}, {$b}]}) {
                adminRmaReasonMassDelete {
                  deleted
                  message
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [], $admin);
        $response->assertOk();
        $this->assertDatabaseMissing('rma_reasons', ['id' => $a]);
        $this->assertDatabaseMissing('rma_reasons', ['id' => $b]);
    }

    /**
     * The mass update status mutation switches the given reasons off and skips unknown ids.
     */
    public function test_mass_update_status_disables_reasons(): void
    {
        $a = $this->createReason('MassA-'.uniqid());
        $b = $this->createReason('MassB-'.uniqid());

        $response = $this->massUpdateReasonStatus([
            'indices' => [$a, $b, 999999999],
            'value' => 0,
        ], $this->createAdmin());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createAdminRmaReasonMassUpdateStatus.adminRmaReasonMassUpdateStatus.updated'))->toBe([$a, $b]);
        $this->assertDatabaseHas('rma_reasons', [
            'id' => $a,
            'status' => 0,
        ]);
        $this->assertDatabaseHas('rma_reasons', [
            'id' => $b,
            'status' => 0,
        ]);
    }

    /**
     * The mass update status mutation rejects a status value other than 0 or 1.
     */
    public function test_mass_update_status_rejects_invalid_value(): void
    {
        $id = $this->createReason('MassA-'.uniqid());

        $response = $this->massUpdateReasonStatus([
            'indices' => [$id],
            'value' => 5,
        ], $this->createAdmin());

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.rma.mass-value-invalid'));
        $this->assertDatabaseHas('rma_reasons', [
            'id' => $id,
            'status' => 1,
        ]);
    }

    /**
     * The mass update status mutation is refused for a role without the reason edit permission.
     */
    public function test_mass_update_status_without_permission_is_refused(): void
    {
        $id = $this->createReason('MassA-'.uniqid());

        $role = Role::create([
            'name' => 'NoRmaReasonEdit '.uniqid(),
            'description' => 'no rma reason edit',
            'permission_type' => 'custom',
            'permissions' => ['sales', 'sales.rma', 'sales.rma.reasons'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $response = $this->massUpdateReasonStatus([
            'indices' => [$id],
            'value' => 0,
        ], $admin, $this->adminTokenSameAsWeb($admin));

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.rma.no-permission'));
        $this->assertDatabaseHas('rma_reasons', [
            'id' => $id,
            'status' => 1,
        ]);
    }

    /**
     * The mass update status mutation rejects a request without an admin token.
     */
    public function test_mass_update_status_requires_authentication(): void
    {
        $id = $this->createReason('MassA-'.uniqid());

        $response = $this->massUpdateReasonStatus([
            'indices' => [$id],
            'value' => 0,
        ]);

        expect($response->json('errors'))->not->toBeNull();
        $this->assertDatabaseHas('rma_reasons', [
            'id' => $id,
            'status' => 1,
        ]);
    }

    /**
     * Run the reason mass update status mutation, optionally as an admin with a given token.
     */
    protected function massUpdateReasonStatus(array $input, ?Admin $admin = null, ?string $token = null): TestResponse
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminRmaReasonMassUpdateStatusInput!) {
              createAdminRmaReasonMassUpdateStatus(input: $input) {
                adminRmaReasonMassUpdateStatus { updated message }
              }
            }
        GQL;

        return $this->adminGraphQL($mutation, ['input' => $input], $admin, $token);
    }

    /**
     * Create reason.
     */
    private function createReason(string $title = 'Wrong size'): int
    {
        $id = DB::table('rma_reasons')->insertGetId([
            'title' => $title,
            'status' => 1,
            'position' => 1,
            'is_admin' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('rma_reason_resolutions')->insert([
            'rma_reason_id' => $id,
            'resolution_type' => 'return',
        ]);

        return $id;
    }
}
