<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminMediaPlaceholderProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsChannelMediaProcessor;

/**
 * Channel logo and favicon upload / removal. REST only — a binary upload is not
 * transportable over JSON GraphQL.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsChannelMedia',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/channels/{id}/logo',
            requirements: ['id' => '\d+'],
            inputFormats: ['multipart' => ['multipart/form-data']],
            deserialize: false,
            read: false,
            validate: false,
            status: 200,
            extraProperties: ['media_type' => 'logo'],
            processor: AdminSettingsChannelMediaProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Channels'],
                summary: 'Upload the channel logo',
                description: 'Send multipart/form-data with `logo` (bmp, jpeg, jpg, png, webp). Replaces the current logo. Optional `file_name` names the stored file and `alt_text` is saved for the `locale` sent (or the default locale). Returns the channel detail.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Channel ID.', true, schema: ['type' => 'integer', 'example' => 1]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['logo'],
                                'properties' => [
                                    'logo' => ['type' => 'string', 'format' => 'binary'],
                                    'file_name' => ['type' => 'string', 'example' => 'store-logo'],
                                    'alt_text' => ['type' => 'string', 'example' => 'Acme store logo'],
                                    'locale' => ['type' => 'string', 'example' => 'en'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Stored. Returns the channel detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'code' => 'default',
                                    'name' => 'Default',
                                    'logo' => 'channel/1/store-logo.png',
                                    'logoUrl' => 'https://example.com/storage/channel/1/store-logo.png',
                                ],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(description: 'Missing settings.channels.edit permission.'),
                    '404' => new Model\Response(description: 'Channel not found.'),
                    '422' => new Model\Response(description: 'Missing or invalid file.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/channels/{id}/logo',
            requirements: ['id' => '\d+'],
            status: 200,
            extraProperties: ['media_type' => 'logo'],
            provider: AdminMediaPlaceholderProvider::class,
            processor: AdminSettingsChannelMediaProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Channels'],
                summary: 'Remove the channel logo',
                description: 'Deletes the stored logo file and clears it on the channel. Returns the channel detail.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Channel ID.', true, schema: ['type' => 'integer', 'example' => 1]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Removed. Returns the channel detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['id' => 1, 'code' => 'default', 'logo' => null, 'logoUrl' => null],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(description: 'Missing settings.channels.edit permission.'),
                    '404' => new Model\Response(description: 'Channel not found.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/settings/channels/{id}/favicon',
            requirements: ['id' => '\d+'],
            inputFormats: ['multipart' => ['multipart/form-data']],
            deserialize: false,
            read: false,
            validate: false,
            status: 200,
            extraProperties: ['media_type' => 'favicon'],
            processor: AdminSettingsChannelMediaProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Channels'],
                summary: 'Upload the channel favicon',
                description: 'Send multipart/form-data with `favicon` (bmp, jpeg, jpg, png, webp, ico). Replaces the current favicon. Optional `file_name` names the stored file. Returns the channel detail.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Channel ID.', true, schema: ['type' => 'integer', 'example' => 1]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['favicon'],
                                'properties' => [
                                    'favicon' => ['type' => 'string', 'format' => 'binary'],
                                    'file_name' => ['type' => 'string', 'example' => 'favicon'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Stored. Returns the channel detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'code' => 'default',
                                    'name' => 'Default',
                                    'favicon' => 'channel/1/favicon.ico',
                                    'faviconUrl' => 'https://example.com/storage/channel/1/favicon.ico',
                                ],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(description: 'Missing settings.channels.edit permission.'),
                    '404' => new Model\Response(description: 'Channel not found.'),
                    '422' => new Model\Response(description: 'Missing or invalid file.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/channels/{id}/favicon',
            requirements: ['id' => '\d+'],
            status: 200,
            extraProperties: ['media_type' => 'favicon'],
            provider: AdminMediaPlaceholderProvider::class,
            processor: AdminSettingsChannelMediaProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Channels'],
                summary: 'Remove the channel favicon',
                description: 'Deletes the stored favicon file and clears it on the channel. Returns the channel detail.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Channel ID.', true, schema: ['type' => 'integer', 'example' => 1]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Removed. Returns the channel detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['id' => 1, 'code' => 'default', 'favicon' => null, 'faviconUrl' => null],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(description: 'Missing settings.channels.edit permission.'),
                    '404' => new Model\Response(description: 'Channel not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [],
)]
class AdminSettingsChannelMedia
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;
}
