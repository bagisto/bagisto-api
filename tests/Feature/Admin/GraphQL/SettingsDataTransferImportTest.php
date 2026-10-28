<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class SettingsDataTransferImportTest extends AdminApiTestCase
{
    protected function insertImport(array $overrides = []): int
    {
        return (int) \DB::table('imports')->insertGetId(array_merge([
            'state' => 'pending',
            'process_in_queue' => 1,
            'type' => 'product',
            'action' => 'append',
            'validation_strategy' => 'stop-on-errors',
            'allowed_errors' => 0,
            'processed_rows_count' => 0,
            'invalid_rows_count' => 0,
            'errors_count' => 0,
            'field_separator' => ',',
            'file_path' => 'imports/sample-'.uniqid().'.csv',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_download_images_mutation_and_status_query(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['type' => 'products', 'state' => 'validated', 'image_source' => 'directory']);

        $mutation = <<<'GQL'
            mutation D($input: downloadAdminSettingsDataTransferImportImagesInput!) {
              downloadAdminSettingsDataTransferImportImages(input: $input) {
                adminSettingsDataTransferImportImages { _id total processed progress done }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => (string) $id, 'importId' => $id]], $admin);

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.downloadAdminSettingsDataTransferImportImages.adminSettingsDataTransferImportImages.done'))->toBeTrue();

        $status = $this->adminGraphQL(
            'query S($importId: Int!) { adminSettingsDataTransferImportImages(importId: $importId) { _id progress done } }',
            ['importId' => $id],
            $admin,
        );

        expect($status->json('errors'))->toBeNull();
        expect($status->json('data.adminSettingsDataTransferImportImages.progress'))->toBe(100);
    }

    public function test_validation_status_query_reports_the_verdict(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['type' => 'products', 'state' => 'validated', 'processed_rows_count' => 5]);

        $response = $this->adminGraphQL(
            'query S($importId: Int!) { adminSettingsDataTransferImportValidation(importId: $importId) { _id state total processed progress done isValid import } }',
            ['importId' => $id],
            $admin,
        );

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.adminSettingsDataTransferImportValidation.done'))->toBeTrue();
        expect($response->json('data.adminSettingsDataTransferImportValidation.isValid'))->toBeTrue();
        expect($response->json('data.adminSettingsDataTransferImportValidation.state'))->toBe('validated');
    }

    public function test_validation_chunk_and_queue_mutations_report_a_failed_step(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $path = 'imports/empty-'.uniqid().'.csv';
        Storage::disk('private')->put($path, '');
        $id = $this->insertImport(['type' => 'products', 'file_path' => $path]);

        foreach (['chunk', 'queue'] as $name) {
            $field = $name.'AdminSettingsDataTransferImportValidation';

            $response = $this->adminGraphQL(
                "mutation V(\$input: {$field}Input!) { {$field}(input: \$input) { adminSettingsDataTransferImportValidation { _id done } } }",
                ['input' => ['id' => (string) $id, 'importId' => $id]],
                $admin,
            );

            expect($response->json('errors'))->not->toBeNull();
        }
    }

    public function test_validation_chunk_mutation_of_an_unknown_import_errors(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGraphQL(
            'mutation V($input: chunkAdminSettingsDataTransferImportValidationInput!) { chunkAdminSettingsDataTransferImportValidation(input: $input) { adminSettingsDataTransferImportValidation { _id } } }',
            ['input' => ['id' => '999999', 'importId' => 999999]],
            $admin,
        );

        expect($response->json('errors'))->not->toBeNull();
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
            'type' => 'product',
            'action' => 'append',
            'state' => 'pending',
            'process_in_queue' => 1,
            'validation_strategy' => 'stop-on-errors',
            'allowed_errors' => 3,
            'processed_rows_count' => 12,
            'invalid_rows_count' => 1,
            'errors_count' => 1,
            'field_separator' => ',',
            'file_path' => 'imports/mw-'.uniqid().'.csv',
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
