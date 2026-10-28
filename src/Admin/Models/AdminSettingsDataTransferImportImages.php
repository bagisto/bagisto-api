<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsDataTransferImportActionInput;
use Webkul\BagistoApi\Admin\Resolver\AdminSettingsDataTransferImportImagesResolver;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportImagesProcessor;

/**
 * The remote-image phase of an `image_source=url` products import. It runs after validate and
 * before start, as in the admin panel, and fetches every image URL so start can attach them.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsDataTransferImportImages',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/data-transfer/imports/{id}/download-images',
            requirements: ['id' => '\d+'],
            input: AdminSettingsDataTransferImportActionInput::class,
            processor: AdminSettingsDataTransferImportImagesProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Download the next wave of import images',
                description: 'Fetches the next wave of image URLs of a validated `image_source=url` import and returns progress. Call it until `done` is true, then call start. An import without URL images is `done` straight away.',
                requestBody: new Model\RequestBody(required: false, content: new \ArrayObject([
                    'application/json' => ['schema' => ['type' => 'object'], 'example' => new \stdClass],
                ])),
                responses: [
                    '200' => new Model\Response(
                        description: 'Download progress.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'id' => 7, 'state' => 'validated', 'total' => 120, 'processed' => 20, 'progress' => 16, 'done' => false,
                        ]]]),
                    ),
                    '400' => new Model\Response(description: 'The download failed.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks settings.data_transfer.imports.import.'),
                    '404' => new Model\Response(description: 'Import not found.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/settings/data-transfer/imports/{id}/download-images/queue',
            requirements: ['id' => '\d+'],
            input: AdminSettingsDataTransferImportActionInput::class,
            processor: AdminSettingsDataTransferImportImagesProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Queue the import image download',
                description: 'Dispatches the whole image download as a background queue batch and returns the image `total`. Poll `GET .../download-images/status` until `done`. A `total` of 0 means there was nothing to fetch.',
                requestBody: new Model\RequestBody(required: false, content: new \ArrayObject([
                    'application/json' => ['schema' => ['type' => 'object'], 'example' => new \stdClass],
                ])),
                responses: [
                    '200' => new Model\Response(
                        description: 'Download dispatched.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'id' => 7, 'state' => 'downloading', 'total' => 120, 'processed' => 0, 'progress' => 0, 'done' => false,
                        ]]]),
                    ),
                    '400' => new Model\Response(description: 'The download could not be queued.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '404' => new Model\Response(description: 'Import not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/data-transfer/imports/{id}/download-images/status',
            requirements: ['id' => '\d+'],
            provider: AdminSettingsDataTransferImportImagesProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Data Transfer'],
                summary: 'Queued image download progress',
                description: 'Progress of a queued image download.',
                responses: [
                    '200' => new Model\Response(
                        description: 'Download progress.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'id' => 7, 'state' => 'validated', 'total' => 120, 'processed' => 120, 'progress' => 100, 'done' => true,
                        ]]]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '404' => new Model\Response(description: 'Import not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'download',
            input: AdminSettingsDataTransferImportActionInput::class,
            read: false,
            processor: AdminSettingsDataTransferImportImagesProcessor::class,
            description: 'Download the next wave of import images.',
        ),
        new Mutation(
            name: 'queue',
            input: AdminSettingsDataTransferImportActionInput::class,
            read: false,
            processor: AdminSettingsDataTransferImportImagesProcessor::class,
            description: 'Queue the import image download.',
        ),
        new Query(
            resolver: AdminSettingsDataTransferImportImagesResolver::class,
            args: ['importId' => ['type' => 'Int!']],
            normalizationContext: ['groups' => ['query']],
            description: 'Queued image download progress.',
        ),
    ],
)]
class AdminSettingsDataTransferImportImages
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['query'])]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    #[Groups(['query'])]
    public ?string $state = null;

    #[ApiProperty(writable: false, description: 'Number of image URLs in the import.')]
    #[Groups(['query'])]
    public ?int $total = null;

    #[ApiProperty(writable: false, description: 'Images fetched or given up on so far.')]
    #[Groups(['query'])]
    public ?int $processed = null;

    #[ApiProperty(writable: false, description: 'Percentage done, 0 to 100.')]
    #[Groups(['query'])]
    public ?int $progress = null;

    #[ApiProperty(writable: false, description: 'True once the download phase is finished and start can be called.')]
    #[Groups(['query'])]
    public ?bool $done = null;
}
