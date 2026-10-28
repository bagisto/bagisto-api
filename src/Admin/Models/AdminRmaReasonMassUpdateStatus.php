<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaReasonMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonMassUpdateStatusProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaReasonMassUpdateStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/rma/reasons/mass-update-status',
            input: AdminRmaReasonMassUpdateStatusInput::class,
            processor: AdminRmaReasonMassUpdateStatusProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Mass-update RMA reason status',
                description: 'Permission: sales.rma.reasons.edit.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['indices', 'value'],
                                'properties' => [
                                    'indices' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [2, 3]],
                                    'value' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'The updated reason ids.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['updated' => [2, 3], 'message' => 'Selected RMA reasons updated successfully.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaReasonMassUpdateStatusInput::class, processor: AdminRmaReasonMassUpdateStatusProcessor::class),
    ],
)]
class AdminRmaReasonMassUpdateStatus
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $updated = null;

    public ?string $message = null;
}
