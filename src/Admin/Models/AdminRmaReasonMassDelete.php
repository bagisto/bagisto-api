<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaReasonMassDeleteInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaReasonMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/rma/reasons/mass-delete',
            input: AdminRmaReasonMassDeleteInput::class,
            processor: AdminRmaReasonMassDeleteProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Mass-delete RMA reasons',
                description: 'Permission: sales.rma.reasons.delete.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['indices'],
                                'properties' => [
                                    'indices' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [2, 3, 4]],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'The deleted reason ids.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['deleted' => [2, 3, 4], 'message' => 'Selected RMA reasons deleted successfully.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaReasonMassDeleteInput::class, processor: AdminRmaReasonMassDeleteProcessor::class),
    ],
)]
class AdminRmaReasonMassDelete
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $deleted = null;

    public ?string $message = null;
}
