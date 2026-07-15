<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\State\AdminCustomerMassUpdateStatusProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerMassUpdateStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customers/mass-update-status',
            input: AdminCustomerMassUpdateStatusInput::class,
            processor: AdminCustomerMassUpdateStatusProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: 'Mass update customer status',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['indices', 'value'],
                                'properties' => [
                                    'indices' => ['type' => 'array', 'items' => ['type' => 'integer']],
                                    'value' => ['type' => 'integer', 'enum' => [0, 1]],
                                ],
                            ],
                            'example' => ['indices' => [14, 15, 16], 'value' => 1],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Mass-update result.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'updated' => [14, 15, 16],
                                    'value' => 1,
                                    'message' => 'Customer status updated successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Missing indices or invalid value.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCustomerMassUpdateStatusInput::class,
            processor: AdminCustomerMassUpdateStatusProcessor::class,
        ),
    ],
)]
class AdminCustomerMassUpdateStatus
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $updated = null;

    #[ApiProperty(writable: false)]
    public ?int $value = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
