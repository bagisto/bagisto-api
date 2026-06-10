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

    public function test_query_detail_multiword_fields_resolve_over_graphql(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport([
            'type'                 => 'product',
            'action'               => 'append',
            'state'                => 'pending',
            'process_in_queue'     => 1,
            'validation_strategy'  => 'stop-on-errors',
            'allowed_errors'       => 3,
            'processed_rows_count' => 12,
            'invalid_rows_count'   => 1,
            'errors_count'         => 1,
            'field_separator'      => ',',
            'file_path'            => 'imports/mw-'.uniqid().'.csv',
        ]);

        $query = <<<GQL
            query {
              adminSettingsDataTransferImport(id: "/api/admin/settings/data-transfer/imports/{$id}") {
                _id
                code
                action
                state
                processInQueue
                validationStrategy
                allowedErrors
                processedRowsCount
                invalidRowsCount
                errorsCount
                fieldSeparator
                filePath
                createdAt
                updatedAt
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();

        $node = $response->json('data.adminSettingsDataTransferImport');

        expect($node['processInQueue'])->not->toBeNull();
        expect($node['validationStrategy'])->not->toBeNull();
        expect($node['allowedErrors'])->not->toBeNull();
        expect($node['processedRowsCount'])->not->toBeNull();
        expect($node['invalidRowsCount'])->not->toBeNull();
        expect($node['errorsCount'])->not->toBeNull();
        expect($node['fieldSeparator'])->not->toBeNull();
        expect($node['filePath'])->not->toBeNull();
        expect($node['createdAt'])->not->toBeNull();
        expect($node['updatedAt'])->not->toBeNull();
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

    public function test_create_mutation_is_rejected_over_graphql(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation Create($input: createAdminSettingsDataTransferImportInput!) {
              createAdminSettingsDataTransferImport(input: $input) {
                adminSettingsDataTransferImport { id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['type' => 'products', 'action' => 'append'],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
        expect(\DB::table('imports')->where('type', 'products')->whereNull('file_path')->exists())->toBeFalse();
    }

    public function test_validate_mutation_not_found(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation V($input: validateAdminSettingsDataTransferImportValidateInput!) {
              validateAdminSettingsDataTransferImportValidate(input: $input) {
                adminSettingsDataTransferImportValidate { id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['importId' => 999999]], $admin);
        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_start_mutation_nothing_to_import(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['processed_rows_count' => 0]);

        $mutation = <<<'GQL'
            mutation S($input: startAdminSettingsDataTransferImportStartInput!) {
              startAdminSettingsDataTransferImportStart(input: $input) {
                adminSettingsDataTransferImportStart { id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['importId' => $id]], $admin);
        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('pending');
    }

    public function test_stats_query_returns_node(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport();

        $query = <<<GQL
            query {
              adminSettingsDataTransferImportStats(id: "/api/admin/settings/data-transfer/imports/{$id}/stats") {
                _id
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();
        expect($response->json('data.adminSettingsDataTransferImportStats._id'))->toBe($id);
    }
}
