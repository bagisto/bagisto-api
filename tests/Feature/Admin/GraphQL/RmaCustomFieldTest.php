<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class RmaCustomFieldTest extends AdminApiTestCase
{
    private function createField(string $code, string $type = 'text'): int
    {
        return DB::table('rma_custom_fields')->insertGetId([
            'code' => $code, 'label' => 'Label', 'type' => $type, 'is_required' => 0,
            'position' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

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
}
