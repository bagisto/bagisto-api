<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class SettingsDataTransferImportTest extends AdminApiTestCase
{
    /**
     * The listing query returns the imports as edges.
     */
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

    /**
     * The detail query returns the requested import.
     */
    public function test_detail_returns_node(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport([
            'type' => 'customer',
            'state' => 'pending',
        ]);

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

    /**
     * The detail query resolves the multi-word fields of an import.
     */
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

    /**
     * The delete mutation removes the import.
     */
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

    /**
     * The create mutation is refused over GraphQL, since the file upload is REST-only.
     */
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
            'input' => [
                'type' => 'products',
                'action' => 'append',
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
        expect(\DB::table('imports')->where('type', 'products')->whereNull('file_path')->exists())->toBeFalse();
    }

    /**
     * The validate mutation refuses an import that does not exist.
     */
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

        $response = $this->adminGraphQL($mutation, ['input' => [
            'id' => '/api/admin/settings/data-transfer/imports/999999/validate',
            'importId' => 999999,
        ]], $admin);

        $response->assertOk();
        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.settings.data-transfer.import.not-found'));
    }

    /**
     * The start mutation refuses an import without processed rows and leaves it pending.
     */
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

        $response = $this->adminGraphQL($mutation, ['input' => [
            'id' => '/api/admin/settings/data-transfer/imports/'.$id.'/start',
            'importId' => $id,
        ]], $admin);

        $response->assertOk();
        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.settings.data-transfer.import.nothing-to-import'));
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('pending');
    }

    /**
     * The stats query returns the import.
     */
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

    /**
     * The cancel mutation moves a pending import to the cancelled state.
     */
    public function test_cancel_mutation_cancels_a_pending_import(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['state' => 'pending']);

        $response = $this->runImportAction('cancel', $id, $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.cancelAdminSettingsDataTransferImportCancel.adminSettingsDataTransferImportCancel');

        expect($node['_id'])->toBe($id);
        expect($node['state'])->toBe('cancelled');
        expect($node['success'])->toBeTrue();

        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('cancelled');
    }

    /**
     * The cancel mutation refuses an import that has already completed.
     */
    public function test_cancel_mutation_refuses_a_completed_import(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['state' => 'completed']);

        $response = $this->runImportAction('cancel', $id, $admin);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.settings.data-transfer.import.cannot-cancel', ['state' => 'completed']));
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('completed');
    }

    /**
     * The cancel mutation refuses a request without an admin token.
     */
    public function test_cancel_mutation_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertImport(['state' => 'pending']);

        $response = $this->runImportAction('cancel', $id);

        expect($response->getStatusCode() === 401 || $response->json('errors') !== null)->toBeTrue();
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('pending');
    }

    /**
     * The cancel mutation refuses a role without the import permission.
     */
    public function test_cancel_mutation_requires_the_import_permission(): void
    {
        [$admin, $token] = $this->adminWithoutImportPermission();
        $id = $this->insertImport(['state' => 'pending']);

        $response = $this->runImportAction('cancel', $id, $admin, $token);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.settings.data-transfer.import.no-permission'));
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('pending');
    }

    /**
     * The link mutation links the processed batch and moves the import into linking.
     */
    public function test_link_mutation_links_the_processed_batch(): void
    {
        $admin = $this->createAdmin();
        [$id, $batchId] = $this->insertProcessableImport('processed', 'processed');

        $response = $this->runImportAction('link', $id, $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.linkAdminSettingsDataTransferImportLink.adminSettingsDataTransferImportLink');

        expect($node['_id'])->toBe($id);
        expect($node['success'])->toBeTrue();
        expect($node['stats']['batches'])->toMatchArray([
            'total' => 1,
            'completed' => 1,
            'remaining' => 0,
        ]);
        expect($node['stats']['progress'])->toEqual(100);

        expect(\DB::table('import_batches')->where('id', $batchId)->value('state'))->toBe('linked');
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('linking');
    }

    /**
     * Linking an import already in linking reports the batch it just linked as completed.
     */
    public function test_link_mutation_reports_fresh_stats_for_an_import_already_linking(): void
    {
        [$id] = $this->insertProcessableImport('linking', 'processed');

        $response = $this->runImportAction('link', $id, $this->createAdmin());

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.linkAdminSettingsDataTransferImportLink.adminSettingsDataTransferImportLink.stats.batches'))->toMatchArray([
            'total' => 1,
            'completed' => 1,
            'remaining' => 0,
        ]);
    }

    /**
     * The link mutation refuses an import with no processed rows.
     */
    public function test_link_mutation_refuses_an_import_with_nothing_to_import(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['processed_rows_count' => 0]);

        $response = $this->runImportAction('link', $id, $admin);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.settings.data-transfer.import.nothing-to-import'));
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('pending');
    }

    /**
     * The link mutation refuses a request without an admin token.
     */
    public function test_link_mutation_requires_admin_token(): void
    {
        $this->seedRequiredData();
        [$id, $batchId] = $this->insertProcessableImport('processed', 'processed');

        $response = $this->runImportAction('link', $id);

        expect($response->getStatusCode() === 401 || $response->json('errors') !== null)->toBeTrue();
        expect(\DB::table('import_batches')->where('id', $batchId)->value('state'))->toBe('processed');
    }

    /**
     * The link mutation refuses a role without the import permission.
     */
    public function test_link_mutation_requires_the_import_permission(): void
    {
        [$admin, $token] = $this->adminWithoutImportPermission();
        [$id, $batchId] = $this->insertProcessableImport('processed', 'processed');

        $response = $this->runImportAction('link', $id, $admin, $token);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.settings.data-transfer.import.no-permission'));
        expect(\DB::table('import_batches')->where('id', $batchId)->value('state'))->toBe('processed');
    }

    /**
     * The index mutation indexes the linked batch and moves the import into indexing.
     */
    public function test_index_mutation_indexes_the_linked_batch(): void
    {
        $admin = $this->createAdmin();
        [$id, $batchId] = $this->insertProcessableImport('linked', 'linked');

        $response = $this->runImportAction('index', $id, $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.indexAdminSettingsDataTransferImportIndex.adminSettingsDataTransferImportIndex');

        expect($node['_id'])->toBe($id);
        expect($node['success'])->toBeTrue();
        expect($node['stats']['batches'])->toMatchArray([
            'total' => 1,
            'completed' => 1,
            'remaining' => 0,
        ]);
        expect($node['stats']['progress'])->toEqual(100);

        expect(\DB::table('import_batches')->where('id', $batchId)->value('state'))->toBe('indexed');
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('indexing');
    }

    /**
     * Indexing an import already in indexing reports the batch it just indexed as completed.
     */
    public function test_index_mutation_reports_fresh_stats_for_an_import_already_indexing(): void
    {
        [$id] = $this->insertProcessableImport('indexing', 'linked');

        $response = $this->runImportAction('index', $id, $this->createAdmin());

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.indexAdminSettingsDataTransferImportIndex.adminSettingsDataTransferImportIndex.stats.batches'))->toMatchArray([
            'total' => 1,
            'completed' => 1,
            'remaining' => 0,
        ]);
    }

    /**
     * The index mutation refuses an import with no processed rows.
     */
    public function test_index_mutation_refuses_an_import_with_nothing_to_import(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['processed_rows_count' => 0]);

        $response = $this->runImportAction('index', $id, $admin);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.settings.data-transfer.import.nothing-to-import'));
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('pending');
    }

    /**
     * The index mutation refuses a request without an admin token.
     */
    public function test_index_mutation_requires_admin_token(): void
    {
        $this->seedRequiredData();
        [$id, $batchId] = $this->insertProcessableImport('linked', 'linked');

        $response = $this->runImportAction('index', $id);

        expect($response->getStatusCode() === 401 || $response->json('errors') !== null)->toBeTrue();
        expect(\DB::table('import_batches')->where('id', $batchId)->value('state'))->toBe('linked');
    }

    /**
     * The index mutation refuses a role without the import permission.
     */
    public function test_index_mutation_requires_the_import_permission(): void
    {
        [$admin, $token] = $this->adminWithoutImportPermission();
        [$id, $batchId] = $this->insertProcessableImport('linked', 'linked');

        $response = $this->runImportAction('index', $id, $admin, $token);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.settings.data-transfer.import.no-permission'));
        expect(\DB::table('import_batches')->where('id', $batchId)->value('state'))->toBe('linked');
    }

    /**
     * Insert an import row with sensible defaults and return its id.
     */
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

    /**
     * Insert a batch of the import in the given state and return its id.
     */
    protected function insertBatch(int $importId, string $state, array $rows = []): int
    {
        return (int) \DB::table('import_batches')->insertGetId([
            'import_id' => $importId,
            'state' => $state,
            'data' => json_encode($rows),

            'summary' => json_encode([
                'created' => 1,
                'updated' => 0,
                'deleted' => 0,
            ]),
        ]);
    }

    /**
     * Insert a product import in the given state with one processed row of a real product in a batch of the batch state.
     */
    protected function insertProcessableImport(string $state, string $batchState): array
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'IMPORT-GQL-'.uniqid(),
        ]);

        $id = $this->insertImport([
            'type' => 'products',
            'state' => $state,
            'process_in_queue' => 0,
            'processed_rows_count' => 1,
        ]);

        return [$id, $this->insertBatch($id, $batchState, [[
            'sku' => $product->sku,
            'type' => 'simple',
        ]])];
    }

    /**
     * Create an admin whose role holds only the given permissions, with a role-bound token.
     */
    protected function customRoleAdmin(array $permissions): array
    {
        $this->seedRequiredData();

        $role = Role::create([
            'name' => 'Import role '.uniqid(),
            'description' => 'import-test',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);

        return [$admin, $this->adminAccessToken($admin)];
    }

    /**
     * Create an admin that may edit imports but not run them, with a role-bound token.
     */
    protected function adminWithoutImportPermission(): array
    {
        return $this->customRoleAdmin([
            'settings',
            'settings.data_transfer',
            'settings.data_transfer.imports',
            'settings.data_transfer.imports.edit',
        ]);
    }

    /**
     * Run one of the import action mutations (cancel, link, index) against the given import.
     */
    protected function runImportAction(string $action, int $importId, ?Admin $admin = null, ?string $token = null): TestResponse
    {
        $operation = $action.'AdminSettingsDataTransferImport'.ucfirst($action);
        $field = 'adminSettingsDataTransferImport'.ucfirst($action);
        $selection = $action === 'cancel' ? '_id state success message' : '_id state success stats';

        $mutation = <<<GQL
            mutation(\$input: {$operation}Input!) {
              {$operation}(input: \$input) {
                {$field} { {$selection} }
              }
            }
        GQL;

        return $this->adminGraphQL($mutation, [
            'input' => [
                'id' => '/api/admin/settings/data-transfer/imports/'.$importId.'/'.$action,
                'importId' => $importId,
            ],
        ], $admin, $token);
    }
}
