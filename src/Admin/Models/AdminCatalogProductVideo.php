<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductVideoDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductVideoProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductVideoProvider;

/**
 * Admin Catalog Product videos sub-resource (mirrors AdminCatalogProductImage).
 *
 *   POST   /api/admin/catalog/products/{productId}/videos       upload (multipart `video`)
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
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
