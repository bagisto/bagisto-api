<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminMediaPlaceholderProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsLocaleLogoProcessor;

/**
 * Locale flag/logo upload and removal. REST only — a binary upload is not
 * transportable over JSON GraphQL.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsLocaleLogo',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/locales/{id}/logo',
            requirements: ['id' => '\d+'],
            inputFormats: ['multipart' => ['multipart/form-data']],
            deserialize: false,
            read: false,
            validate: false,
            status: 200,
            processor: AdminSettingsLocaleLogoProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Locales'],
                summary: 'Upload a locale logo',
                description: 'Send multipart/form-data with `logo` (an image: jpeg, jpg, png or webp). Stored as locales/{code}.{extension}, replacing the current logo. Returns the locale detail.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Locale ID.', true, schema: ['type' => 'integer', 'example' => 2]),
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
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Logo stored. Returns the locale detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 2,
                                    'code' => 'fr',
                                    'name' => 'French',
                                    'direction' => 'ltr',
                                    'logoPath' => 'locales/fr.png',
                                    'logoUrl' => 'https://example.com/storage/locales/fr.png',
                                ],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(description: 'Missing settings.locales.edit permission.'),
                    '404' => new Model\Response(description: 'Locale not found.'),
                    '422' => new Model\Response(description: 'Missing or invalid logo.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/locales/{id}/logo',
            requirements: ['id' => '\d+'],
            status: 200,
            provider: AdminMediaPlaceholderProvider::class,
            processor: AdminSettingsLocaleLogoProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Locales'],
                summary: 'Remove a locale logo',
                description: 'Clears the locale logo and deletes the stored file. Returns the locale detail.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Locale ID.', true, schema: ['type' => 'integer', 'example' => 2]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Logo removed. Returns the locale detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['id' => 2, 'code' => 'fr', 'name' => 'French', 'logoPath' => null, 'logoUrl' => null],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(description: 'Missing settings.locales.edit permission.'),
                    '404' => new Model\Response(description: 'Locale not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [],
)]
class AdminSettingsLocaleLogo
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;
}
