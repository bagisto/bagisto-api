<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsDataTransferImportCancelInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportCancelProcessor;

/**
 * One-operation resource for cancelling a data-transfer import.
 *
 * REST:
 *   POST /api/admin/settings/data-transfer/imports/{id}/cancel
 *
 * GraphQL:
 *   cancelAdminSettingsDataTransferImport(input: { importId: Int! })
 *
 * Sets the import state to `cancelled` if currently `pending` or `processing`.
 * Anything else (completed / processed / failed / cancelled) → HTTP 422.
 *
 * Mirrors the same single-action-resource pattern used by
 * AdminCatalogProductCopy / AdminCustomerImpersonate (Phase 5.2 / Block C).
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsDataTransferImportCancel',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/data-transfer/imports/{id}/cancel',
            input: AdminSettingsDataTransferImportCancelInput::class,
            processor: AdminSettingsDataTransferImportCancelProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Cancel a running or pending import',
                description: 'Sets state to "cancelled". Refuses (422) when the import is in a terminal state (completed / processed / failed / cancelled). Permission: settings.data_transfer.imports.edit.',
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Import cancelled successfully.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'      => 3,
                                    'state'   => 'cancelled',
                                    'success' => true,
                                    'message' => 'Import cancelled successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks settings.data_transfer.imports.edit.'),
                    '404' => new Model\Response(description: 'Import not found.'),
                    '422' => new Model\Response(description: 'Import is in a terminal state and cannot be cancelled.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'cancel',
            input: AdminSettingsDataTransferImportCancelInput::class,
            processor: AdminSettingsDataTransferImportCancelProcessor::class,
            description: 'Cancel a pending/processing import. Becomes cancelAdminSettingsDataTransferImportCancel via API Platform GraphQL naming — clients typically alias the field as `cancelAdminSettingsDataTransferImport`.',
        ),
    ],
)]
class AdminSettingsDataTransferImportCancel
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $state = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
