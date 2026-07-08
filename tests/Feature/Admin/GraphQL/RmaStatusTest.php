<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class RmaStatusTest extends AdminApiTestCase
{
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

    public function test_update_and_delete(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createStatus('Old-'.uniqid());

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
    }

    public function test_mass_delete_skips_default(): void
    {
        $admin = $this->createAdmin();
        $keep = $this->createStatus('DefaultKeep-'.uniqid(), 1);
        $gone = $this->createStatus('Gone-'.uniqid());

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
    }
}
