<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class RmaReasonTest extends AdminApiTestCase
{
    private function createReason(string $title = 'Wrong size'): int
    {
        $id = DB::table('rma_reasons')->insertGetId([
            'title'      => $title,
            'status'     => 1,
            'position'   => 1,
            'is_admin'   => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('rma_reason_resolutions')->insert(['rma_reason_id' => $id, 'resolution_type' => 'return']);

        return $id;
    }

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
}
