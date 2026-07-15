<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCategoryMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\State\AdminCategoryMassUpdateStatusProcessor;

/**
 * One-operation resource for bulk-flipping category status.
 *
 * REST:
 *   POST /api/admin/catalog/categories/mass-update-status
 *     Body: { "indices": [12, 18], "value": 1 }
 *     200:  { "updated": [12, 18], "message": "..." }
 *
 * GraphQL:
 *   createAdminCategoryMassUpdateStatus
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCategoryMassUpdateStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/categories/mass-update-status',
            input: AdminCategoryMassUpdateStatusInput::class,
            processor: AdminCategoryMassUpdateStatusProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Categories'],
                summary: 'Mass update category status',
                description: 'Sets the status of a batch of categories to the given value (0 or 1).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['indices', 'value'],
                                'properties' => [
                                    'indices' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'integer'],
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
                        description: 'Categories updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'updated' => [12, 18],
                                    'message' => 'Categories status updated successfully.',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCategoryMassUpdateStatusInput::class,
            processor: AdminCategoryMassUpdateStatusProcessor::class,
            description: 'Mass-update status for a batch of categories. Becomes createAdminCategoryMassUpdateStatus.',
        ),
    ],
)]
class AdminCategoryMassUpdateStatus
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $updated = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
