<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class RmaRuleTest extends AdminApiTestCase
{
    private function createRule(string $name = 'Rule'): int
    {
        return DB::table('rma_rules')->insertGetId([
            'name' => $name, 'description' => 'desc', 'status' => 1, 'return_period' => 30,
            'default' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

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
}
