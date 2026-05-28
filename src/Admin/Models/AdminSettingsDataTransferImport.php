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
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsDataTransferImportDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportCollectionProvider;
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
        new Delete(
            uriTemplate: '/settings/data-transfer/imports/{id}',
            provider: AdminSettingsDataTransferImportWriteProvider::class,
            processor: AdminSettingsDataTransferImportProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
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
                tags: ['Admin Settings'],
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
                tags: ['Admin Settings'],
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
                'code'            => ['type' => 'String'],
                'type'            => ['type' => 'String'],
                'action'          => ['type' => 'String'],
                'state'           => ['type' => 'String'],
                'created_at_from' => ['type' => 'String'],
                'created_at_to'   => ['type' => 'String'],
                'sort'            => ['type' => 'String'],
                'order'           => ['type' => 'String'],
            ],
            description: 'Admin imports listing (cursor pagination).',
        ),
        new Query(
            provider: AdminSettingsDataTransferImportItemProvider::class,
            description: 'Admin import detail by id.',
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
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** Entity type (product / customer / category / etc). Sourced from the `type` column. */
    #[ApiProperty(writable: false)]
    public ?string $code = null;

    /** Action — append / update / delete. */
    #[ApiProperty(writable: false)]
    public ?string $action = null;

    /** Current state — pending / validated / processing / processed / completed / etc. */
    #[ApiProperty(writable: false)]
    public ?string $state = null;

    #[ApiProperty(writable: false)]
    public ?bool $processInQueue = null;

    #[ApiProperty(writable: false)]
    public ?string $validationStrategy = null;

    #[ApiProperty(writable: false)]
    public ?int $allowedErrors = null;

    #[ApiProperty(writable: false)]
    public ?int $processedRowsCount = null;

    #[ApiProperty(writable: false)]
    public ?int $invalidRowsCount = null;

    #[ApiProperty(writable: false)]
    public ?int $errorsCount = null;

    /** @var array<int,mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $errors = null;

    #[ApiProperty(writable: false)]
    public ?string $fieldSeparator = null;

    #[ApiProperty(writable: false)]
    public ?string $filePath = null;

    #[ApiProperty(writable: false)]
    public ?string $imagesDirectoryPath = null;

    #[ApiProperty(writable: false)]
    public ?string $errorFilePath = null;

    /** @var array<string,mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $summary = null;

    #[ApiProperty(writable: false)]
    public ?string $startedAt = null;

    #[ApiProperty(writable: false)]
    public ?string $completedAt = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;

    /** Populated on cancel-action responses. */
    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
