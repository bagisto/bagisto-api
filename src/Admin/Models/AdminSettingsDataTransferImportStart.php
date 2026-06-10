<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsDataTransferImportActionInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportActionProcessor;

/**
 * POST /api/admin/settings/data-transfer/imports/{id}/start
 * + startAdminSettingsDataTransferImportStart mutation.
 *
 * Runs one batch of the import (or kicks off the queue when process_in_queue is
 * set) and returns { stats, import }. Guards: nothing-to-import (400), not-valid
 * (400), setup-queue-error (400). Permission: settings.data_transfer.imports.edit.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsDataTransferImportStart',
    normalizationContext: ['skip_null_values' => false],
    output: AdminSettingsDataTransferImport::class,
    operations: [
        new Post(
            uriTemplate: '/settings/data-transfer/imports/{id}/start',
            input: AdminSettingsDataTransferImportActionInput::class,
            processor: AdminSettingsDataTransferImportActionProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Start an import batch',
                description: 'Processes the next pending batch (or dispatches the queue). Returns stats + the refreshed import. Permission: settings.data_transfer.imports.edit.',
                requestBody: new Model\RequestBody(required: false, content: new \ArrayObject([
                    'application/json' => ['schema' => ['type' => 'object'], 'example' => new \stdClass],
                ])),
                responses: [
                    '200' => new Model\Response(description: 'Batch processed; stats + refreshed import.'),
                    '400' => new Model\Response(description: 'Nothing to import / not valid / queue not configured.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks settings.data_transfer.imports.edit.'),
                    '404' => new Model\Response(description: 'Import not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'start',
            input: AdminSettingsDataTransferImportActionInput::class,
            processor: AdminSettingsDataTransferImportActionProcessor::class,
            description: 'Start an import batch.',
        ),
    ],
)]
class AdminSettingsDataTransferImportStart
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;
}
