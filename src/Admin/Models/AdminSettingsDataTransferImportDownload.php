<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportDownloadProvider;

/**
 * Binary download endpoints (REST-only — binary streams aren't transportable
 * over JSON GraphQL):
 *   GET /settings/data-transfer/imports/{id}/download
 *   GET /settings/data-transfer/imports/{id}/download-error-report
 *   GET /settings/data-transfer/imports/sample/{type}/{format}
 *
 * All three respond with application/octet-stream attachments. The `id`
 * requirement keeps the literal sample/{type}/{format} route from colliding.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsDataTransferImportDownload',
    operations: [
        new Get(
            uriTemplate: '/settings/data-transfer/imports/{id}/download',
            requirements: ['id' => '\d+'],
            provider: AdminSettingsDataTransferImportDownloadProvider::class,
            outputFormats: ['binary' => ['application/octet-stream']],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Download the uploaded import file',
                description: 'Streams the original uploaded file as an attachment. Send `Accept: application/octet-stream`. 404 when no file is present.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Import ID.', true, schema: ['type' => 'integer', 'example' => 3]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'The uploaded file is downloaded.'),
                    '404' => new Model\Response(description: 'Import or file not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/data-transfer/imports/{id}/download-error-report',
            requirements: ['id' => '\d+'],
            provider: AdminSettingsDataTransferImportDownloadProvider::class,
            outputFormats: ['binary' => ['application/octet-stream']],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Download the import error report',
                description: 'Streams the generated error report as an attachment. Send `Accept: application/octet-stream`. 404 when no error report exists.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Import ID.', true, schema: ['type' => 'integer', 'example' => 3]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'The error report is downloaded.'),
                    '404' => new Model\Response(description: 'Import or error report not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/data-transfer/imports/sample/{type}/{format}',
            provider: AdminSettingsDataTransferImportDownloadProvider::class,
            outputFormats: ['binary' => ['application/octet-stream']],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Download a sample import file',
                description: 'Streams the configured sample file for the given importer type and format. Send `Accept: application/octet-stream`. 422 for an unsupported format; 404 for an unknown type / missing sample.',
                parameters: [
                    new Model\Parameter('type', 'path', 'Importer type (products / customers / tax_rates).', true, schema: ['type' => 'string', 'example' => 'products']),
                    new Model\Parameter('format', 'path', 'Sample format (csv / xls / xlsx / xml).', true, schema: ['type' => 'string', 'example' => 'csv']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'The sample file is downloaded.'),
                    '404' => new Model\Response(description: 'Unknown type or missing sample.'),
                    '422' => new Model\Response(description: 'Unsupported sample format.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [],
)]
class AdminSettingsDataTransferImportDownload
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;
}
