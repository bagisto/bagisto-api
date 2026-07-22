<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaStatusMassDeleteInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaStatusMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/rma/statuses/mass-delete',
            input: AdminRmaStatusMassDeleteInput::class,
            processor: AdminRmaStatusMassDeleteProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Mass-delete RMA statuses',
                description: 'Default statuses are skipped. Permission: sales.rma.statuses.delete.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['indices'],
                                'properties' => [
                                    'indices' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [9, 10]],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'The deleted status ids (default statuses skipped).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['deleted' => [9, 10], 'message' => 'Selected RMA statuses deleted successfully.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaStatusMassDeleteInput::class, processor: AdminRmaStatusMassDeleteProcessor::class),
    ],
)]
class AdminRmaStatusMassDelete
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $deleted = null;

    public ?string $message = null;
}
