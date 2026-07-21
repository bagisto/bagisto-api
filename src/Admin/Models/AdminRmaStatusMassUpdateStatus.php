<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaStatusMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusMassUpdateStatusProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaStatusMassUpdateStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/rma/statuses/mass-update-status',
            input: AdminRmaStatusMassUpdateStatusInput::class,
            processor: AdminRmaStatusMassUpdateStatusProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Mass-update RMA status active flag',
                description: 'Permission: sales.rma.statuses.edit.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['indices', 'value'],
                                'properties' => [
                                    'indices' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [9, 10]],
                                    'value' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'The updated status ids.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['updated' => [9, 10], 'message' => 'Selected RMA statuses updated successfully.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaStatusMassUpdateStatusInput::class, processor: AdminRmaStatusMassUpdateStatusProcessor::class),
    ],
)]
class AdminRmaStatusMassUpdateStatus
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $updated = null;

    public ?string $message = null;
}
