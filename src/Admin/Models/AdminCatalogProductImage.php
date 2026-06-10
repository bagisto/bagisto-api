<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductImageDeleteInput;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductImageReorderInput;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductImageProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductImageProvider;

/**
 * Phase 5.11 — Admin Catalog Product images sub-resource.
 *
 * Endpoints (under the parent product):
 *   POST   /api/admin/catalog/products/{productId}/images
 *          Multipart `image: <file>` (bmp/jpeg/jpg/png/webp). Optional `position`.
 *          Returns the new image row.
 *
 *   PUT    /api/admin/catalog/products/{productId}/images/reorder
 *          Body: { "order": [{ "id": <imageId>, "position": <int> }, ...] }
 *          Returns the updated list of images.
 *
 *   DELETE /api/admin/catalog/products/{productId}/images/{id}
 *          Removes the DB row and the storage file. Returns { success, message }.
 *
 * GraphQL mutations: createAdminCatalogProductImage / reorderAdminCatalogProductImages /
 * deleteAdminCatalogProductImage. The upload mutation is documented but the
 * binary file part is not transportable over JSON GraphQL — clients must use
 * REST for upload.
 *
 * Permission: catalog.products.edit (Sanctum-token pattern — reads
 * $admin->role->permission_type/->permissions directly, never bouncer()).
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCatalogProductImage',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/products/{productId}/images',
            inputFormats: ['multipart' => ['multipart/form-data']],
            processor: AdminCatalogProductImageProcessor::class,
            status: 201,
            deserialize: false,
            read: false,
            validate: false,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Upload a product image',
                description: 'Uploads a new image for the given product. Send as multipart/form-data with `image` containing the file (allowed mime types: bmp, jpeg, jpg, png, webp).',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Parent product ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['image'],
                                'properties' => [
                                    'image'    => ['type' => 'string', 'format' => 'binary'],
                                    'position' => ['type' => 'integer', 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Image uploaded.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'        => 47,
                                    'productId' => 12,
                                    'path'      => 'product/12/abc123.webp',
                                    'position'  => 1,
                                    'url'       => '/storage/product/12/abc123.webp',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure (missing file, invalid mime, too large).'),
                    '404' => new Model\Response(description: 'Product not found.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/catalog/products/{productId}/images/reorder',
            input: AdminCatalogProductImageReorderInput::class,
            provider: AdminCatalogProductImageProvider::class,
            processor: AdminCatalogProductImageProcessor::class,
            extraProperties: ['standard_put' => false],
            read: false,
            write: true,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Reorder product images',
                description: 'Updates the position of one or more existing images for the given product. Each image id must belong to the product; otherwise the call is rejected.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Parent product ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['order'],
                                'properties' => [
                                    'order' => [
                                        'type'  => 'array',
                                        'items' => [
                                            'type'       => 'object',
                                            'properties' => [
                                                'id'       => ['type' => 'integer'],
                                                'position' => ['type' => 'integer'],
                                            ],
                                        ],
                                        'example' => [
                                            ['id' => 47, 'position' => 2],
                                            ['id' => 48, 'position' => 1],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Images reordered. Returns the updated list ordered by position.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success'  => true,
                                    'message'  => 'Product images reordered successfully.',
                                    'images'   => [
                                        ['id' => 48, 'productId' => 12, 'path' => 'product/12/xyz.webp', 'position' => 1, 'url' => '/storage/product/12/xyz.webp'],
                                        ['id' => 47, 'productId' => 12, 'path' => 'product/12/abc.webp', 'position' => 2, 'url' => '/storage/product/12/abc.webp'],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Image id does not belong to the product, or order payload invalid.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/catalog/products/{productId}/images/{id}',
            provider: AdminCatalogProductImageProvider::class,
            processor: AdminCatalogProductImageProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Delete a product image',
                description: 'Deletes the DB row and removes the file from public storage.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Parent product ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                    new Model\Parameter('id', 'path', 'Image ID.', true, schema: ['type' => 'integer', 'example' => 47]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Image deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['success' => true, 'message' => 'Product image deleted successfully.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Image (or its parent product) not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new \ApiPlatform\Metadata\GraphQl\Mutation(
            name: 'create',
            input: AdminCatalogProductImageReorderInput::class,
            processor: AdminCatalogProductImageProcessor::class,
            description: 'Placeholder for createAdminCatalogProductImage — binary upload is REST-only. Use POST /api/admin/catalog/products/{productId}/images.',
        ),
        new \ApiPlatform\Metadata\GraphQl\Mutation(
            name: 'reorder',
            input: AdminCatalogProductImageReorderInput::class,
            processor: AdminCatalogProductImageProcessor::class,
            description: 'Reorder product images. Becomes reorderAdminCatalogProductImage.',
        ),
        new \ApiPlatform\Metadata\GraphQl\Mutation(
            name: 'delete',
            input: AdminCatalogProductImageDeleteInput::class,
            processor: AdminCatalogProductImageProcessor::class,
            description: 'Delete a product image. Becomes deleteAdminCatalogProductImage.',
        ),
    ],
)]
class AdminCatalogProductImage
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

    /**
     * Reorder response: the updated list of all the product's images.
     *
     * @var array<int, array<string, mixed>>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $images = null;
}
