<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductMassUpdateStatusProcessor;

/**
 * One-operation resource for bulk-flipping catalog product status.
 *
 * REST:
 *   POST /api/admin/catalog/products/mass-update-status
 *     Body: { "indices": [12, 18], "value": 1 }
 *     200:  { "updated": [12, 18], "message": "..." }
 *
 * GraphQL:
 *   createAdminCatalogProductMassUpdateStatus
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCatalogProductMassUpdateStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/products/mass-update-status',
            input: AdminCatalogProductMassUpdateStatusInput::class,
            processor: AdminCatalogProductMassUpdateStatusProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog'],
                summary: 'Mass update catalog product status',
                description: 'Sets the status of a batch of products to the given value (0 or 1). Mirrors Bagisto monolith ProductController::massUpdate — best-effort, fires catalog.product.update.{before,after} per ID.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['indices', 'value'],
                                'properties' => [
                                    'indices' => [
                                        'type'    => 'array',
                                        'items'   => ['type' => 'integer'],
                                        'example' => [12, 18],
                                    ],
                                    'value' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Products updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'updated' => [12, 18],
                                    'message' => 'Products status updated successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(description: 'Empty or malformed indices, or value not 0/1.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks catalog.products.edit.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new \ApiPlatform\Metadata\GraphQl\Mutation(
            name: 'create',
            input: AdminCatalogProductMassUpdateStatusInput::class,
            processor: AdminCatalogProductMassUpdateStatusProcessor::class,
            description: 'Mass-update status for a batch of catalog products. Becomes createAdminCatalogProductMassUpdateStatus.',
        ),
    ],
)]
class AdminCatalogProductMassUpdateStatus
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $updated = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
