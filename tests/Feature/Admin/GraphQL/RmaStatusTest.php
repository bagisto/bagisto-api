<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class RmaStatusTest extends AdminApiTestCase
{
    /**
     * Test create and list.
     */
    public function test_create_and_list(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation {
              createAdminRmaStatus(input: {title: "GQL status", status: 1, color: "#123456"}) {
                adminRmaStatus { _id title color }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [], $admin);
        $response->assertOk();
        $node = $response->json('data.createAdminRmaStatus.adminRmaStatus');
        expect($node['title'])->toBe('GQL status');
        expect($node['color'])->toBe('#123456');

        $list = $this->adminGraphQL('query { adminRmaStatuses { edges { node { _id title } } } }', [], $admin);
        $list->assertOk();
        expect($list->json('data.adminRmaStatuses.edges'))->toBeArray();
    }

    /**
     * Test update and delete.
     */
    public function test_update_and_delete(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createStatus('Old-'.uniqid());
        $other = $this->createStatus('Bystander-'.uniqid());

        $update = <<<GQL
            mutation {
              updateAdminRmaStatus(input: {id: "/api/admin/rma/statuses/{$id}", title: "Updated-{$id}", status: 1, color: "#999999"}) {
                adminRmaStatus { _id title }
              }
            }
        GQL;

        $updateResponse = $this->adminGraphQL($update, [], $admin);
        $updateResponse->assertOk();
        expect($updateResponse->json('data.updateAdminRmaStatus.adminRmaStatus.title'))->toBe('Updated-'.$id);

        $delete = <<<GQL
            mutation {
              deleteAdminRmaStatus(input: {id: "/api/admin/rma/statuses/{$id}"}) {
                adminRmaStatus { _id }
              }
            }
        GQL;

        $deleteResponse = $this->adminGraphQL($delete, [], $admin);
        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('rma_statuses', ['id' => $id]);
        $this->assertDatabaseHas('rma_statuses', ['id' => $other]);
    }

    /**
     * Test mass delete skips default.
     */
    public function test_mass_delete_skips_default(): void
    {
        $admin = $this->createAdmin();
        $keep = $this->createStatus('DefaultKeep-'.uniqid(), 1);
        $gone = $this->createStatus('Gone-'.uniqid());
        $other = $this->createStatus('Bystander-'.uniqid());

        $mutation = <<<GQL
            mutation {
              createAdminRmaStatusMassDelete(input: {indices: [{$keep}, {$gone}]}) {
                adminRmaStatusMassDelete { deleted message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [], $admin);
        $response->assertOk();
        $this->assertDatabaseHas('rma_statuses', ['id' => $keep]);
        $this->assertDatabaseMissing('rma_statuses', ['id' => $gone]);
        $this->assertDatabaseHas('rma_statuses', ['id' => $other]);
    }

    /**
     * The mass update status mutation switches the given statuses off and skips unknown ids.
     */
    public function test_mass_update_status_disables_statuses(): void
    {
        $a = $this->createStatus('MassA-'.uniqid());
        $b = $this->createStatus('MassB-'.uniqid());

        $response = $this->massUpdateStatusStatus([
            'indices' => [$a, $b, 999999999],
            'value' => 0,
        ], $this->createAdmin());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createAdminRmaStatusMassUpdateStatus.adminRmaStatusMassUpdateStatus.updated'))->toBe([$a, $b]);
        $this->assertDatabaseHas('rma_statuses', [
            'id' => $a,
            'status' => 0,
        ]);
        $this->assertDatabaseHas('rma_statuses', [
            'id' => $b,
            'status' => 0,
        ]);
    }

    /**
     * The mass update status mutation rejects a status value other than 0 or 1.
     */
    public function test_mass_update_status_rejects_invalid_value(): void
    {
        $id = $this->createStatus('MassA-'.uniqid());

        $response = $this->massUpdateStatusStatus([
            'indices' => [$id],
            'value' => 5,
        ], $this->createAdmin());

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.rma.mass-value-invalid'));
        $this->assertDatabaseHas('rma_statuses', [
            'id' => $id,
            'status' => 1,
        ]);
    }

    /**
     * The mass update status mutation is refused for a role without the status edit permission.
     */
    public function test_mass_update_status_without_permission_is_refused(): void
    {
        $id = $this->createStatus('MassA-'.uniqid());

        $role = Role::create([
            'name' => 'NoRmaStatusEdit '.uniqid(),
            'description' => 'no rma status edit',
            'permission_type' => 'custom',
            'permissions' => ['sales', 'sales.rma', 'sales.rma.statuses'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $response = $this->massUpdateStatusStatus([
            'indices' => [$id],
            'value' => 0,
        ], $admin, $this->adminAccessToken($admin));

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.rma.no-permission'));
        $this->assertDatabaseHas('rma_statuses', [
            'id' => $id,
            'status' => 1,
        ]);
    }

    /**
     * The mass update status mutation rejects a request without an admin token.
     */
    public function test_mass_update_status_requires_authentication(): void
    {
        $id = $this->createStatus('MassA-'.uniqid());

        $response = $this->massUpdateStatusStatus([
            'indices' => [$id],
            'value' => 0,
        ]);

        expect($response->json('errors'))->not->toBeNull();
        $this->assertDatabaseHas('rma_statuses', [
            'id' => $id,
            'status' => 1,
        ]);
    }

    /**
     * Run the status mass update status mutation, optionally as an admin with a given token.
     */
    protected function massUpdateStatusStatus(array $input, ?Admin $admin = null, ?string $token = null): TestResponse
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminRmaStatusMassUpdateStatusInput!) {
              createAdminRmaStatusMassUpdateStatus(input: $input) {
                adminRmaStatusMassUpdateStatus { updated message }
              }
            }
        GQL;

        return $this->adminGraphQL($mutation, ['input' => $input], $admin, $token);
    }

    /**
     * Create status.
     */
    private function createStatus(string $title, int $default = 0): int
    {
        return DB::table('rma_statuses')->insertGetId([
            'title' => $title,
            'status' => 1,
            'color' => '#abcdef',
            'default' => $default,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
