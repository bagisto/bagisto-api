<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportStatsProvider;

/**
 * GET /api/admin/settings/data-transfer/imports/{id}/stats?state=
 * + adminSettingsDataTransferImportStats query.
 *
 * Returns the import detail plus the per-batch progress + summary block.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsDataTransferImportStats',
    normalizationContext: ['skip_null_values' => false],
    output: AdminSettingsDataTransferImport::class,
    operations: [
        new Get(
            uriTemplate: '/settings/data-transfer/imports/{id}/stats',
            requirements: ['id' => '\d+'],
            provider: AdminSettingsDataTransferImportStatsProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Import stats',
                description: 'Returns the per-batch progress + summary for the requested state (default processed) plus the refreshed import.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Import ID.', true, schema: ['type' => 'integer', 'example' => 3]),
                    new Model\Parameter('state', 'query', 'Batch state to summarise (default processed).', false, schema: ['type' => 'string', 'example' => 'processed']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Stats + refreshed import.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '404' => new Model\Response(description: 'Import not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: AdminSettingsDataTransferImportStatsProvider::class,
            description: 'Import stats by id.',
        ),
    ],
)]
class AdminSettingsDataTransferImportStats
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;
}
