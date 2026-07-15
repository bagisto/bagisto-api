<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsDataTransferImportCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsDataTransferImportDeleteInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportWriteProvider;

/**
 * Admin Settings → Data Transfer Imports (Block B Wave 3).
 *
 * REST:
 *   GET    /api/admin/settings/data-transfer/imports          — listing
 *   GET    /api/admin/settings/data-transfer/imports/{id}     — detail
 *   DELETE /api/admin/settings/data-transfer/imports/{id}     — delete (DB row + file)
 *
 * GraphQL:
 *   adminSettingsDataTransferImports  — cursor listing
 *   adminSettingsDataTransferImport(id:) — detail
 *   deleteAdminSettingsDataTransferImport — delete
 *
 * Cancel action lives on the sibling resource AdminSettingsDataTransferImportCancel
 * (POST /api/admin/settings/data-transfer/imports/{id}/cancel + cancel mutation).
 *
 * Create endpoint deferred — file upload + async queue dispatch will land in a
 * follow-up. Use the Bagisto admin panel for now. Mirrors the deferral pattern
 * documented for Phase 5.11 product images and the AdminSettingsChannel logo.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\DataTransfer\ImportController
 * for listing, detail, cancel and delete actions.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsDataTransferImport',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/data-transfer/imports',
            inputFormats: ['multipart' => ['multipart/form-data']],
            processor: AdminSettingsDataTransferImportCreateProcessor::class,
            status: 201,
            deserialize: false,
            read: false,
            validate: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Create an import',
                description: 'Creates a new import. Send as multipart/form-data with the binary `file` part plus type / action / validation_strategy / allowed_errors / field_separator. Binary upload is REST-only. Permission: settings.data_transfer.imports.create.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['type', 'action', 'validation_strategy', 'allowed_errors', 'field_separator', 'file'],
                                'properties' => [
                                    'type' => ['type' => 'string', 'example' => 'products'],
                                    'action' => ['type' => 'string', 'enum' => ['append', 'delete'], 'example' => 'append'],
                                    'validation_strategy' => ['type' => 'string', 'enum' => ['stop-on-errors', 'skip-errors'], 'example' => 'stop-on-errors'],
                                    'allowed_errors' => ['type' => 'integer', 'example' => 0],
                                    'field_separator' => ['type' => 'string', 'example' => ','],
                                    'process_in_queue' => ['type' => 'boolean', 'example' => false],
                                    'images_directory_path' => ['type' => 'string', 'example' => ''],
                                    'file' => ['type' => 'string', 'format' => 'binary'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Import created.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks settings.data_transfer.imports.create.'),
                    '422' => new Model\Response(description: 'Validation failure (missing file, invalid type/action/strategy).'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/settings/data-transfer/imports/{id}',
            inputFormats: ['multipart' => ['multipart/form-data']],
            provider: AdminSettingsDataTransferImportWriteProvider::class,
            processor: AdminSettingsDataTransferImportCreateProcessor::class,
            requirements: ['id' => '\d+'],
            deserialize: false,
            read: false,
            validate: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Update an import',
                description: 'Updates an import. Send as multipart/form-data; the `file` part is optional. Re-uploading resets the import state to pending and clears the previous counts / error report. Permission: settings.data_transfer.imports.edit.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Import ID.', true, schema: ['type' => 'integer', 'example' => 3]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['type', 'action', 'validation_strategy', 'allowed_errors', 'field_separator'],
                                'properties' => [
                                    'type' => ['type' => 'string', 'example' => 'products'],
                                    'action' => ['type' => 'string', 'enum' => ['append', 'delete'], 'example' => 'append'],
                                    'validation_strategy' => ['type' => 'string', 'enum' => ['stop-on-errors', 'skip-errors'], 'example' => 'stop-on-errors'],
                                    'allowed_errors' => ['type' => 'integer', 'example' => 0],
                                    'field_separator' => ['type' => 'string', 'example' => ','],
                                    'file' => ['type' => 'string', 'format' => 'binary'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Import updated.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks settings.data_transfer.imports.edit.'),
                    '404' => new Model\Response(description: 'Import not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/data-transfer/imports/{id}',
            provider: AdminSettingsDataTransferImportWriteProvider::class,
            processor: AdminSettingsDataTransferImportProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Delete an import',
                description: 'Removes the DB row and best-effort deletes the underlying upload file from storage. Permission: settings.data_transfer.imports.delete.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Import ID.', true, schema: ['type' => 'integer', 'example' => 3]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Import deleted.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks settings.data_transfer.imports.delete.'),
                    '404' => new Model\Response(description: 'Import not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/data-transfer/imports/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminSettingsDataTransferImportItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Import detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Import ID.', true, schema: ['type' => 'integer', 'example' => 3]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Single import with full state, counts and file info.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '404' => new Model\Response(description: 'Import not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/settings/data-transfer/imports',
            provider: AdminSettingsDataTransferImportCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'List imports',
                description: 'Paginated listing of all imports across entity types and actions. Filters: code (entity type), type (synonym for code), action, state, created_at_from, created_at_to. Sort: id (default desc), state, created_at.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('code', 'query', 'Filter by entity type (e.g. product, customer).', false, schema: ['type' => 'string', 'example' => 'product']),
                    new Model\Parameter('type', 'query', 'Alias for code (kept for spec compatibility).', false, schema: ['type' => 'string', 'example' => 'product']),
                    new Model\Parameter('action', 'query', 'Filter by action (append / update / delete).', false, schema: ['type' => 'string', 'example' => 'append']),
                    new Model\Parameter('state', 'query', 'Filter by state.', false, schema: ['type' => 'string', 'example' => 'pending']),
                    new Model\Parameter('created_at_from', 'query', 'Filter by created_at >= (ISO date).', false, schema: ['type' => 'string', 'example' => '2026-01-01']),
                    new Model\Parameter('created_at_to', 'query', 'Filter by created_at <= (ISO date).', false, schema: ['type' => 'string', 'example' => '2026-12-31']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'state', 'created_at'], 'example' => 'id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated imports in the { data, meta } envelope.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSettingsDataTransferImportCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'code' => ['type' => 'String'],
                'type' => ['type' => 'String'],
                'action' => ['type' => 'String'],
                'state' => ['type' => 'String'],
                'created_at_from' => ['type' => 'String'],
                'created_at_to' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin imports listing (cursor pagination).',
        ),
        new Query(
            provider: AdminSettingsDataTransferImportItemProvider::class,
            description: 'Admin import detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminSettingsDataTransferImportCreateInput::class,
            processor: AdminSettingsDataTransferImportCreateProcessor::class,
            description: 'Placeholder for createAdminSettingsDataTransferImport — binary upload is REST-only. Use POST /api/admin/settings/data-transfer/imports.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSettingsDataTransferImportDeleteInput::class,
            processor: AdminSettingsDataTransferImportProcessor::class,
            description: 'Delete an import. Becomes deleteAdminSettingsDataTransferImport.',
        ),
    ],
)]
class AdminSettingsDataTransferImport
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false, example: 1)]
    public ?int $id = null;

    /** Entity type (product / customer / category / etc). Sourced from the `type` column. */
    #[ApiProperty(writable: false, example: 'products')]
    public ?string $code = null;

    /** Action — append / update / delete. */
    #[ApiProperty(writable: false, example: 'append')]
    public ?string $action = null;

    /** Current state — pending / validated / processing / processed / completed / etc. */
    #[ApiProperty(writable: false, example: 'pending')]
    public ?string $state = null;

    #[ApiProperty(writable: false, example: false)]
    public ?bool $process_in_queue = null;

    #[ApiProperty(writable: false, example: 'stop-on-errors')]
    public ?string $validation_strategy = null;

    #[ApiProperty(writable: false, example: 0)]
    public ?int $allowed_errors = null;

    #[ApiProperty(writable: false, example: 0)]
    public ?int $processed_rows_count = null;

    #[ApiProperty(writable: false, example: 0)]
    public ?int $invalid_rows_count = null;

    #[ApiProperty(writable: false, example: 0)]
    public ?int $errors_count = null;

    /** @var array<int,mixed>|null */
    #[ApiProperty(writable: false, example: [])]
    public ?array $errors = null;

    #[ApiProperty(writable: false, example: ',')]
    public ?string $field_separator = null;

    #[ApiProperty(writable: false, example: 'imports/6f1a_products.csv')]
    public ?string $file_path = null;

    #[ApiProperty(writable: false, example: 'product/images')]
    public ?string $images_directory_path = null;

    #[ApiProperty(writable: false, example: null)]
    public ?string $error_file_path = null;

    /** @var array<string,mixed>|null */
    #[ApiProperty(writable: false, example: ['created' => 0, 'updated' => 0, 'deleted' => 0])]
    public ?array $summary = null;

    #[ApiProperty(writable: false, example: null)]
    public ?string $started_at = null;

    #[ApiProperty(writable: false, example: null)]
    public ?string $completed_at = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:15:00+00:00')]
    public ?string $created_at = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:20:00+00:00')]
    public ?string $updated_at = null;

    /** Populated on cancel-action responses. */
    #[ApiProperty(writable: false, example: true)]
    public ?bool $success = null;

    /** Populated on validate-action responses. */
    #[ApiProperty(writable: false, example: true)]
    public ?bool $is_valid = null;

    /**
     * Populated on validate / start / link / index / stats action responses —
     * the per-batch progress + summary block from the import helper.
     *
     * @var array<string,mixed>|null
     */
    #[ApiProperty(writable: false, example: ['processed' => 120, 'total' => 120, 'invalid' => 0])]
    public ?array $stats = null;

    #[ApiProperty(writable: false, example: 'Import deleted successfully.')]
    public ?string $message = null;
}
