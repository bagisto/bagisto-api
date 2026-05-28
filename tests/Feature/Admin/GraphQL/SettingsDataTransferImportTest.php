<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class SettingsDataTransferImportTest extends AdminApiTestCase
{
    protected function insertImport(array $overrides = []): int
    {
        return (int) \DB::table('imports')->insertGetId(array_merge([
            'state'                => 'pending',
            'process_in_queue'     => 1,
            'type'                 => 'product',
            'action'               => 'append',
            'validation_strategy'  => 'stop-on-errors',
            'allowed_errors'       => 0,
            'processed_rows_count' => 0,
            'invalid_rows_count'   => 0,
            'errors_count'         => 0,
            'field_separator'      => ',',
            'file_path'            => 'imports/sample-'.uniqid().'.csv',
            'created_at'           => now(),
            'updated_at'           => now(),
        ], $overrides));
    }

    public function test_listing_returns_edges(): void
    {
        $admin = $this->createAdmin();
        $this->insertImport();

        $query = <<<'GQL'
            query {
              adminSettingsDataTransferImports(first: 5) {
                edges { node { id } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();
        expect($response->json('data.adminSettingsDataTransferImports.edges'))->toBeArray();
    }

    public function test_detail_returns_node(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['type' => 'customer', 'state' => 'pending']);

        $query = <<<GQL
            query {
              adminSettingsDataTransferImport(id: "/api/admin/settings/data-transfer/imports/{$id}") {
                id
                _id
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();
        expect($response->json('data.adminSettingsDataTransferImport._id'))->toBe($id);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport();
        $iri = '/api/admin/settings/data-transfer/imports/'.$id;

        $mutation = <<<'GQL'
            mutation Del($input: deleteAdminSettingsDataTransferImportInput!) {
              deleteAdminSettingsDataTransferImport(input: $input) {
                adminSettingsDataTransferImport { id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);
        $response->assertOk();
        expect(\DB::table('imports')->where('id', $id)->exists())->toBeFalse();
    }
}
