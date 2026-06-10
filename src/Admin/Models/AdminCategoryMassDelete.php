<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCategoryMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminCategoryMassDeleteProcessor;

/**
 * One-operation resource for mass-deleting categories.
 *
 * REST:
 *   POST /api/admin/catalog/categories/mass-delete
 *     Body: { "indices": [12, 18] }
 *     200:  { "deleted": [12, 18], "message": "..." }
 *     400:  if any ID is non-deletable (root, channel root), entire batch rejected.
 *
 * GraphQL:
 *   createAdminCategoryMassDelete
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCategoryMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/categories/mass-delete',
            input: AdminCategoryMassDeleteInput::class,
            processor: AdminCategoryMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Categories'],
                summary: 'Mass delete categories',
                description: 'Deletes a batch of categories. If any ID is non-deletable (root or a channel root_category_id), the entire batch is rejected with HTTP 400. Non-existent IDs are silently skipped.',
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
                        description: 'Categories deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [12, 18],
                                    'message' => 'Categories deleted successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(
                        description: 'Batch rejected — at least one ID is a root category or a channel root.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'type'   => '/errors/400',
                                    'status' => 400,
                                    'detail' => 'Root and channel-root categories cannot be deleted.',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new \ApiPlatform\Metadata\GraphQl\Mutation(
            name: 'create',
            input: AdminCategoryMassDeleteInput::class,
            processor: AdminCategoryMassDeleteProcessor::class,
            description: 'Mass-delete a batch of categories. Becomes createAdminCategoryMassDelete in GraphQL.',
        ),
    ],
)]
class AdminCategoryMassDelete
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
