<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingCartRuleMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/cart-rules/mass-delete',
            input: AdminMarketingCartRuleMassDeleteInput::class,
            processor: AdminMarketingCartRuleMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Mass delete cart rules',
                description: 'Deletes a batch of cart rules. Non-existent IDs are silently skipped.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['indices'],

                                'properties' => [
                                    'indices' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'integer'],
                                        'example' => [3, 5],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Cart rules deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [12, 18],
                                    'skipped' => [],
                                    'message' => 'Cart rules deleted.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Empty indices.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminMarketingCartRuleMassDeleteInput::class,
            processor: AdminMarketingCartRuleMassDeleteProcessor::class,
            description: 'Becomes createAdminMarketingCartRuleMassDelete.',
        ),
    ],
)]
class AdminMarketingCartRuleMassDelete
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * The ids of the cart rules that were deleted.
     *
     * @var int[]|null
     */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    /**
     * The cart rules that were not deleted, each with the reason.
     *
     * @var array<int, array{id:int,reason:string}>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $skipped = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
