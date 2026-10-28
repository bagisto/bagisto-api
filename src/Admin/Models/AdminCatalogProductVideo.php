<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductVideoDeleteInput;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductVideoReorderInput;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductVideoProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductVideoProvider;

/**
 * Admin Catalog Product videos sub-resource (mirrors AdminCatalogProductImage).
 *
 *   POST   /api/admin/catalog/products/{productId}/videos       upload (multipart `video`)
 *   PUT    /api/admin/catalog/products/{productId}/videos/reorder  reorder positions
 *   DELETE /api/admin/catalog/products/{productId}/videos/{id}  remove DB row + file
 *
 * Binary upload is REST-only; the GraphQL create mutation is a placeholder that
 * rejects with 422. GraphQL delete works. Permission: catalog.products.edit.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCatalogProductVideo',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/products/{productId}/videos',
            inputFormats: ['multipart' => ['multipart/form-data']],
            processor: AdminCatalogProductVideoProcessor::class,
            status: 201,
            deserialize: false,
            read: false,
            validate: false,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Upload a product video',
                description: 'Uploads a new video for the given product. Send as multipart/form-data with `video` containing the file (mp4, webm, ogg). Optional `position`.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Parent product ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['video'],
                                'properties' => [
                                    'video' => ['type' => 'string', 'format' => 'binary'],
                                    'position' => ['type' => 'integer', 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Video uploaded.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 8,
                                    'productId' => 12,
                                    'path' => 'product/12/xyz789.mp4',
                                    'position' => 1,
                                    'url' => '/storage/product/12/xyz789.mp4',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure (missing file, invalid type, too large).'),
                    '404' => new Model\Response(description: 'Product not found.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/catalog/products/{productId}/videos/reorder',
            input: AdminCatalogProductVideoReorderInput::class,
            provider: AdminCatalogProductVideoProvider::class,
            processor: AdminCatalogProductVideoProcessor::class,
            extraProperties: ['standard_put' => false],
            read: false,
            write: true,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Reorder product videos',
                description: 'Updates the position of one or more existing videos for the given product. Each video id must belong to the product; otherwise the call is rejected.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Parent product ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['order'],
                                'properties' => [
                                    'order' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => ['type' => 'integer'],
                                                'position' => ['type' => 'integer'],
                                            ],
                                        ],
                                        'example' => [
                                            ['id' => 8, 'position' => 2],
                                            ['id' => 9, 'position' => 1],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Videos reordered. Returns the updated list ordered by position.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Product videos reordered successfully.',
                                    'videos' => [
                                        ['id' => 9, 'productId' => 12, 'path' => 'product/12/def456.mp4', 'position' => 1, 'url' => '/storage/product/12/def456.mp4'],
                                        ['id' => 8, 'productId' => 12, 'path' => 'product/12/xyz789.mp4', 'position' => 2, 'url' => '/storage/product/12/xyz789.mp4'],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Product not found.'),
                    '422' => new Model\Response(description: 'Video id does not belong to the product, or order payload invalid.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/catalog/products/{productId}/videos/{id}',
            provider: AdminCatalogProductVideoProvider::class,
            processor: AdminCatalogProductVideoProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Delete a product video',
                description: 'Deletes the DB row and removes the file from public storage.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Parent product ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                    new Model\Parameter('id', 'path', 'Video ID.', true, schema: ['type' => 'integer', 'example' => 8]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Video deleted.',
                        content: new \ArrayObject([
                            'application/json' => ['example' => ['success' => true, 'message' => 'Product video deleted successfully.']],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Video (or its parent product) not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCatalogProductVideoDeleteInput::class,
            processor: AdminCatalogProductVideoProcessor::class,
            description: 'Placeholder for createAdminCatalogProductVideo — binary upload is REST-only. Use POST /api/admin/catalog/products/{productId}/videos.',
        ),
        new Mutation(
            name: 'reorder',
            input: AdminCatalogProductVideoReorderInput::class,
            read: false,
            processor: AdminCatalogProductVideoProcessor::class,
            description: 'Reorder product videos. Becomes reorderAdminCatalogProductVideo.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminCatalogProductVideoDeleteInput::class,
            processor: AdminCatalogProductVideoProcessor::class,
            description: 'Delete a product video. Becomes deleteAdminCatalogProductVideo.',
        ),
    ],
)]
class AdminCatalogProductVideo
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $productId = null;

    #[ApiProperty(writable: false)]
    public ?string $path = null;

    #[ApiProperty(writable: false)]
    public ?int $position = null;

    #[ApiProperty(writable: false)]
    public ?string $url = null;

    #[ApiProperty(writable: false)]
    public ?array $videos = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
