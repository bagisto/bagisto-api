<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminCategoryMediaProcessor;
use Webkul\BagistoApi\Admin\State\AdminMediaPlaceholderProvider;

/**
 * Category logo and banner upload / removal. REST only — a binary upload is not
 * transportable over JSON GraphQL.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCategoryMedia',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/categories/{id}/media',
            requirements: ['id' => '\d+'],
            inputFormats: ['multipart' => ['multipart/form-data']],
            deserialize: false,
            read: false,
            validate: false,
            status: 200,
            processor: AdminCategoryMediaProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Categories'],
                summary: 'Upload a category logo or banner',
                description: 'Send multipart/form-data with exactly one of `logo` or `banner` (bmp, jpeg, jpg, png, webp). The image is stored as webp and replaces the current one. Optional `file_name` names the stored file, `alt_text` is saved for the `locale` sent (or the default locale; `all` for every locale). Returns the category detail.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Category ID.', true, schema: ['type' => 'integer', 'example' => 7]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'logo' => ['type' => 'string', 'format' => 'binary'],
                                    'banner' => ['type' => 'string', 'format' => 'binary'],
                                    'alt_text' => ['type' => 'string', 'example' => 'Summer collection banner'],
                                    'file_name' => ['type' => 'string', 'example' => 'summer-collection'],
                                    'locale' => ['type' => 'string', 'example' => 'en'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Image stored. Returns the category detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 7,
                                    'position' => 1,
                                    'status' => 1,
                                    'parentId' => 1,
                                    'displayMode' => 'products_and_description',
                                    'logoUrl' => null,
                                    'bannerUrl' => 'https://example.com/storage/category/7/summer-collection.webp',
                                    'logoAlt' => null,
                                    'bannerAlt' => 'Summer collection banner',
                                    'name' => 'Summer',
                                    'slug' => 'summer',
                                    'locale' => 'en',
                                ],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(description: 'Missing catalog.categories.edit permission.'),
                    '404' => new Model\Response(description: 'Category not found.'),
                    '422' => new Model\Response(description: 'No logo/banner file, both sent, or invalid file type.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/catalog/categories/{id}/media/{type}',
            requirements: ['id' => '\d+', 'type' => 'logo|banner'],
            status: 200,
            provider: AdminMediaPlaceholderProvider::class,
            processor: AdminCategoryMediaProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Categories'],
                summary: 'Remove a category logo or banner',
                description: 'Deletes the stored logo or banner file and clears its alt text in every locale. Returns the category detail.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Category ID.', true, schema: ['type' => 'integer', 'example' => 7]),
                    new Model\Parameter('type', 'path', '`logo` or `banner`.', true, schema: ['type' => 'string', 'enum' => ['logo', 'banner']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Image removed. Returns the category detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['id' => 7, 'logoUrl' => null, 'bannerUrl' => null, 'logoAlt' => null, 'bannerAlt' => null, 'name' => 'Summer'],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(description: 'Missing catalog.categories.edit permission.'),
                    '404' => new Model\Response(description: 'Category not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [],
)]
class AdminCategoryMedia
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;
}
