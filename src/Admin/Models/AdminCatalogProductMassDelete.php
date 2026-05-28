<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductMassDeleteProcessor;

/**
 * One-operation resource for mass-deleting catalog products.
 *
 * REST:
 *   POST /api/admin/catalog/products/mass-delete
 *     Body: { "indices": [12, 18] }
 *     200:  { "deleted": [12, 18], "message": "..." }
 *
 * GraphQL:
 *   createAdminCatalogProductMassDelete
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCatalogProductMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/products/mass-delete',
            input: AdminCatalogProductMassDeleteInput::class,
            processor: AdminCatalogProductMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog'],
                summary: 'Mass delete catalog products',
                description: 'Deletes a batch of products. Non-existent IDs are silently skipped. If any single delete throws an exception, the endpoint returns 500 with the underlying message — matching the Bagisto monolith ProductController::massDestroy behaviour.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['indices'],
                                'properties' => [
                                    'indices' => [
                                        'type'    => 'array',
                                        'items'   => ['type' => 'integer'],
                                        'example' => [12, 18],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Products deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [12, 18],
                                    'message' => 'Products deleted successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(description: 'Empty or malformed indices.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks catalog.products.delete.'),
                    '500' => new Model\Response(description: 'Underlying delete threw an exception (mirrors monolith behaviour).'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new \ApiPlatform\Metadata\GraphQl\Mutation(
            name: 'create',
            input: AdminCatalogProductMassDeleteInput::class,
            processor: AdminCatalogProductMassDeleteProcessor::class,
            description: 'Mass-delete a batch of catalog products. Becomes createAdminCatalogProductMassDelete in GraphQL.',
        ),
    ],
)]
class AdminCatalogProductMassDelete
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
