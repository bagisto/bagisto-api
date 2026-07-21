<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaCustomFieldMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldMassUpdateStatusProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaCustomFieldMassUpdateStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(uriTemplate: '/rma/custom-fields/mass-update-status', input: AdminRmaCustomFieldMassUpdateStatusInput::class, processor: AdminRmaCustomFieldMassUpdateStatusProcessor::class, openapi: new Model\Operation(
            tags: ['Admin Sales: RMA'],
            summary: 'Mass-update RMA custom field status',
            description: 'Permission: sales.rma.custom-fields.edit.',
            requestBody: new Model\RequestBody(
                required: true,
                content: new \ArrayObject([
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'required' => ['indices', 'value'],
                            'properties' => [
                                'indices' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [4, 6]],
                                'value' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                            ],
                        ],
                    ],
                ]),
            ),
            responses: [
                '200' => new Model\Response(
                    description: 'The updated custom-field ids.',
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => ['updated' => [4, 6], 'message' => 'Selected RMA custom fields updated successfully.'],
                        ],
                    ]),
                ),
            ],
        )),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: AdminRmaCustomFieldMassUpdateStatusInput::class, processor: AdminRmaCustomFieldMassUpdateStatusProcessor::class),
    ],
)]
class AdminRmaCustomFieldMassUpdateStatus
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = 0;

    /** @var array<int,int>|null */
    public ?array $updated = null;

    public ?string $message = null;
}
