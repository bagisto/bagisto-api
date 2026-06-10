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
 * POST /api/admin/settings/data-transfer/imports/{id}/index
 * + indexAdminSettingsDataTransferImportIndex mutation.
 *
 * Indexes one linked batch and returns { stats, import }. Permission:
 * settings.data_transfer.imports.edit.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsDataTransferImportIndex',
    normalizationContext: ['skip_null_values' => false],
    output: AdminSettingsDataTransferImport::class,
    operations: [
        new Post(
            uriTemplate: '/settings/data-transfer/imports/{id}/index',
            input: AdminSettingsDataTransferImportActionInput::class,
            processor: AdminSettingsDataTransferImportActionProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Index an import batch',
                description: 'Indexes the next linked batch and returns stats + the refreshed import. Permission: settings.data_transfer.imports.edit.',
                requestBody: new Model\RequestBody(required: false, content: new \ArrayObject([
                    'application/json' => ['schema' => ['type' => 'object'], 'example' => new \stdClass],
                ])),
                responses: [
                    '200' => new Model\Response(description: 'Batch indexed; stats + refreshed import.'),
                    '400' => new Model\Response(description: 'Nothing to import / not valid.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks settings.data_transfer.imports.edit.'),
                    '404' => new Model\Response(description: 'Import not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'index',
            input: AdminSettingsDataTransferImportActionInput::class,
            processor: AdminSettingsDataTransferImportActionProcessor::class,
            description: 'Index an import batch.',
        ),
    ],
)]
class AdminSettingsDataTransferImportIndex
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;
}
