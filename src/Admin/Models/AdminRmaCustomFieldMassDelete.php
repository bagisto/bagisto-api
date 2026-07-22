<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaCustomFieldMassDeleteInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaCustomFieldMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(uriTemplate: '/rma/custom-fields/mass-delete', input: AdminRmaCustomFieldMassDeleteInput::class, processor: AdminRmaCustomFieldMassDeleteProcessor::class, openapi: new Model\Operation(
            tags: ['Admin Sales: RMA'],
            summary: 'Mass-delete RMA custom fields',
            description: 'Permission: sales.rma.custom-fields.delete.',
            requestBody: new Model\RequestBody(
                required: true,
                content: new \ArrayObject([
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'required' => ['indices'],
                            'properties' => [
                                'indices' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [4, 6]],
                            ],
                        ],
                    ],
                ]),
            ),
            responses: [
                '200' => new Model\Response(
                    description: 'The deleted custom-field ids.',
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => ['deleted' => [4, 6], 'message' => 'Selected RMA custom fields deleted successfully.'],
                        ],
                    ]),
                ),
            ],
        )),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaCustomFieldMassDeleteInput::class, processor: AdminRmaCustomFieldMassDeleteProcessor::class),
    ],
)]
class AdminRmaCustomFieldMassDelete
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $deleted = null;

    public ?string $message = null;
}
