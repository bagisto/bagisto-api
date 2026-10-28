<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaRuleMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleMassUpdateStatusProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaRuleMassUpdateStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(uriTemplate: '/rma/rules/mass-update-status', input: AdminRmaRuleMassUpdateStatusInput::class, processor: AdminRmaRuleMassUpdateStatusProcessor::class, openapi: new Model\Operation(
            tags: ['Admin Sales: RMA'],
            summary: 'Mass-update RMA rule status',
            description: 'Permission: sales.rma.rules.edit.',
            requestBody: new Model\RequestBody(
                required: true,
                content: new \ArrayObject([
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'required' => ['indices', 'value'],
                            'properties' => [
                                'indices' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [3, 5]],
                                'value' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                            ],
                        ],
                    ],
                ]),
            ),
            responses: [
                '200' => new Model\Response(
                    description: 'The updated rule ids.',
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => ['updated' => [3, 5], 'message' => 'Selected RMA rules updated successfully.'],
                        ],
                    ]),
                ),
            ],
        )),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaRuleMassUpdateStatusInput::class, processor: AdminRmaRuleMassUpdateStatusProcessor::class),
    ],
)]
class AdminRmaRuleMassUpdateStatus
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $updated = null;

    public ?string $message = null;
}
