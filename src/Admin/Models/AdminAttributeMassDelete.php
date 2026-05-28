<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminAttributeMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminAttributeMassDeleteProcessor;

/**
 * One-operation resource for mass-deleting attributes.
 *
 * REST:
 *   POST /api/admin/catalog/attributes/mass-delete
 *     Body: { "indices": [24, 31] }
 *     Response 200: { "deleted": [24, 31], "message": "..." }
 *
 * GraphQL:
 *   massDeleteAdminCatalogAttributes
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAttributeMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/attributes/mass-delete',
            input: AdminAttributeMassDeleteInput::class,
            processor: AdminAttributeMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog'],
                summary: 'Mass delete attributes',
                description: 'Deletes a batch of user-defined attributes. If any ID in the batch belongs to a system attribute (`is_user_defined = 0`), the entire batch is rejected with HTTP 422. Non-existent IDs are silently skipped.',
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
                                        'example' => [24, 31],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Attributes deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [24, 31],
                                    'message' => 'Attributes deleted successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(
                        description: 'Batch rejected — one or more IDs are system attributes.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'type'   => '/errors/422',
                                    'status' => 422,
                                    'detail' => 'System attributes cannot be deleted.',
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
            input: AdminAttributeMassDeleteInput::class,
            processor: AdminAttributeMassDeleteProcessor::class,
            description: 'Mass-delete a batch of user-defined attributes. Becomes createAdminAttributeMassDelete in GraphQL.',
        ),
    ],
)]
class AdminAttributeMassDelete
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
