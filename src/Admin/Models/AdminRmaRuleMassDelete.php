<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaRuleMassDeleteInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaRuleMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(uriTemplate: '/rma/rules/mass-delete', input: AdminRmaRuleMassDeleteInput::class, processor: AdminRmaRuleMassDeleteProcessor::class, openapi: new Model\Operation(
            tags: ['Admin Sales: RMA'],
            summary: 'Mass-delete RMA rules',
            description: 'Permission: sales.rma.rules.delete.',
            requestBody: new Model\RequestBody(
                required: true,
                content: new \ArrayObject([
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'required' => ['indices'],
                            'properties' => [
                                'indices' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [3, 5]],
                            ],
                        ],
                    ],
                ]),
            ),
            responses: [
                '200' => new Model\Response(
                    description: 'The deleted rule ids.',
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => ['deleted' => [3, 5], 'message' => 'Selected RMA rules deleted successfully.'],
                        ],
                    ]),
                ),
            ],
        )),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaRuleMassDeleteInput::class, processor: AdminRmaRuleMassDeleteProcessor::class),
    ],
)]
class AdminRmaRuleMassDelete
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $deleted = null;

    public ?string $message = null;
}
