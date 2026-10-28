<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductImageDeleteInput;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductImageReorderInput;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductImageUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductImageProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductImageProvider;

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
                description: 'Uploads a new image for the given product. Send as multipart/form-data with `image` containing the file (allowed mime types: bmp, jpeg, jpg, png, webp). Optional `alt_text` is stored for every locale the request covers.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Parent product ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 12,
                    ]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['image'],

                                'properties' => [
                                    'image' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                    ],

                                    'position' => [
                                        'type' => 'integer',
                                        'example' => 1,
                                    ],

                                    'alt_text' => [
                                        'type' => 'string',
                                        'example' => 'Blue running shoe, side view',
                                    ],
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
                                    'id' => 47,
                                    'productId' => 12,
                                    'path' => 'product/12/abc123.webp',
                                    'position' => 1,
                                    'url' => '/storage/product/12/abc123.webp',
                                    'altText' => 'Blue running shoe, side view',
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
                    new Model\Parameter('productId', 'path', 'Parent product ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 12,
                    ]),
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
                                            [
                                                'id' => 47,
                                                'position' => 2,
                                            ], [
                                                'id' => 48,
                                                'position' => 1,
                                            ],
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
                                    'success' => true,
                                    'message' => 'Product images reordered successfully.',

                                    'images' => [
                                        [
                                            'id' => 48,
                                            'productId' => 12,
                                            'path' => 'product/12/xyz.webp',
                                            'position' => 1,
                                            'url' => '/storage/product/12/xyz.webp',
                                        ], [
                                            'id' => 47,
                                            'productId' => 12,
                                            'path' => 'product/12/abc.webp',
                                            'position' => 2,
                                            'url' => '/storage/product/12/abc.webp',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Image id does not belong to the product, or order payload invalid.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/catalog/products/{productId}/images/{id}',
            input: AdminCatalogProductImageUpdateInput::class,
            provider: AdminCatalogProductImageProvider::class,
            processor: AdminCatalogProductImageProcessor::class,
            requirements: ['id' => '\d+'],
            extraProperties: ['standard_put' => false],
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Update a product image',
                description: 'Edits what is stored alongside an uploaded image — its alt text and its position. The file itself is replaced by uploading a new image and deleting this one.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Parent product ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 12,
                    ]),
                    new Model\Parameter('id', 'path', 'Image ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 47,
                    ]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',

                                'properties' => [
                                    'alt_text' => [
                                        'type' => 'string',
                                        'example' => 'Blue running shoe, side view',
                                    ],

                                    'position' => [
                                        'type' => 'integer',
                                        'example' => 2,
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Image updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 47,
                                    'productId' => 12,
                                    'path' => 'product/12/abc123.webp',
                                    'position' => 2,
                                    'url' => '/storage/product/12/abc123.webp',
                                    'altText' => 'Blue running shoe, side view',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Image (or its parent product) not found.'),
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
                    new Model\Parameter('productId', 'path', 'Parent product ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 12,
                    ]),
                    new Model\Parameter('id', 'path', 'Image ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 47,
                    ]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Image deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Product image deleted successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Image (or its parent product) not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCatalogProductImageReorderInput::class,
            processor: AdminCatalogProductImageProcessor::class,
            description: 'Placeholder for createAdminCatalogProductImage — binary upload is REST-only. Use POST /api/admin/catalog/products/{productId}/images.',
        ),
        new Mutation(
            name: 'reorder',
            input: AdminCatalogProductImageReorderInput::class,
            processor: AdminCatalogProductImageProcessor::class,
            description: 'Reorder product images. Becomes reorderAdminCatalogProductImage.',
        ),
        new Mutation(
            name: 'update',
            input: AdminCatalogProductImageUpdateInput::class,
            processor: AdminCatalogProductImageProcessor::class,
            description: 'Update a product image\'s alt text and position. Becomes updateAdminCatalogProductImage.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminCatalogProductImageDeleteInput::class,
            processor: AdminCatalogProductImageProcessor::class,
            description: 'Delete a product image. Becomes deleteAdminCatalogProductImage.',
        ),
    ],
)]
class AdminCatalogProductImage
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * The product id.
     */
    #[ApiProperty(writable: false)]
    public ?int $product_id = null;

    /**
     * The path.
     */
    #[ApiProperty(writable: false)]
    public ?string $path = null;

    /**
     * The position.
     */
    #[ApiProperty(writable: false)]
    public ?int $position = null;

    /**
     * The url.
     */
    #[ApiProperty(writable: false)]
    public ?string $url = null;

    /**
     * The alt text.
     */
    #[ApiProperty(writable: false, description: 'Alt text stored for the current locale.')]
    public ?string $alt_text = null;

    /**
     * The success.
     */
    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    /**
     * The message.
     */
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
