<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminCustomerMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customers/mass-delete',
            input: AdminCustomerMassDeleteInput::class,
            processor: AdminCustomerMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Mass delete customers',
                description: 'Customers with pending/processing orders are skipped with a reason.',
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
                            'example' => ['indices' => [14, 15, 16]],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Mass-delete result.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [14, 16],
                                    'skipped' => [['id' => 15, 'reason' => 'Customer has pending or processing orders.']],
                                    'message' => 'Customers deleted successfully.',
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
            input: AdminCustomerMassDeleteInput::class,
            processor: AdminCustomerMassDeleteProcessor::class,
        ),
    ],
)]
class AdminCustomerMassDelete
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
