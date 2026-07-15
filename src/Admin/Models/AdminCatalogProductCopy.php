<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductCopyInput;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCopyProcessor;

/**
 * One-operation resource for copying a catalog product.
 *
 * REST:
 *   POST /api/admin/catalog/products/{sourceId}/copy
 *     200: { id, sourceId, sku, type, name, success, message }
 *
 * GraphQL:
 *   createAdminCatalogProductCopy(input: { sourceId: Int! })
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCatalogProductCopy',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/products/{sourceId}/copy',
            input: AdminCatalogProductCopyInput::class,
            processor: AdminCatalogProductCopyProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Copy a catalog product',
                description: 'Duplicates an existing product across all attribute_values, images, inventories, categories and customer_group_prices. Refuses variants (parent_id != null) with HTTP 422. Mirrors Bagisto monolith ProductController::copy. Fires catalog.product.create.before and catalog.product.create.after.',
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Product copied successfully.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 43,
                                    'sourceId' => 12,
                                    'sku' => 'SKU-001-copy-1',
                                    'type' => 'simple',
                                    'name' => 'Test SKU-001 (Copy)',
                                    'success' => true,
                                    'message' => 'Product copied successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks catalog.products.create.'),
                    '404' => new Model\Response(description: 'Source product not found.'),
                    '422' => new Model\Response(description: 'Source product is a configurable variant (parent_id is set) — variants cannot be copied.'),
                    '500' => new Model\Response(description: 'Underlying copy threw an exception.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCatalogProductCopyInput::class,
            processor: AdminCatalogProductCopyProcessor::class,
            description: 'Copy a catalog product. Becomes createAdminCatalogProductCopy in GraphQL.',
        ),
    ],
)]
class AdminCatalogProductCopy
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false, description: 'ID of the source product that was copied.')]
    public ?int $sourceId = null;

    #[ApiProperty(writable: false, description: 'SKU of the newly created copy (auto-suffixed by Bagisto core).')]
    public ?string $sku = null;

    #[ApiProperty(writable: false, description: 'Product type of the copy — matches the source.')]
    public ?string $type = null;

    #[ApiProperty(writable: false, description: 'Display name of the copy, when available.')]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
