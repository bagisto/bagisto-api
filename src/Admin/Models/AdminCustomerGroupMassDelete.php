<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerGroupMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminCustomerGroupMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerGroupMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customers/groups/mass-delete',
            input: AdminCustomerGroupMassDeleteInput::class,
            processor: AdminCustomerGroupMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customer Groups'],
                summary: 'Mass delete customer groups',
                description: 'System groups and groups with attached customers are skipped with a reason.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['indices'],
                                'properties' => [
                                    'indices' => ['type' => 'array', 'items' => ['type' => 'integer']],
                                ],
                            ],
                            'example' => ['indices' => [5, 6, 7]],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Mass-delete result.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [5, 7],
                                    'skipped' => [['id' => 6, 'reason' => 'Group has customers attached.']],
                                    'message' => 'Customer groups deleted successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'No indices supplied.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCustomerGroupMassDeleteInput::class,
            processor: AdminCustomerGroupMassDeleteProcessor::class,
        ),
    ],
)]
class AdminCustomerGroupMassDelete
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    /** @var array<int, array{id:int,reason:string}>|null */
    #[ApiProperty(writable: false)]
    public ?array $skipped = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
