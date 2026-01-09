<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\State\CartTokenMutationProvider;
use Webkul\BagistoApi\State\CartTokenProcessor;

/**
 * RemoveCartItems - GraphQL & REST API Resource for Removing Multiple Items
 *
 * Provides mutation for removing multiple items from cart in a single operation.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'RemoveCartItems',
    operations: [
        new Post(
            name: 'removeItems',
            uriTemplate: '/remove-cart-items',
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Remove multiple cart items.',
            openapi: new Model\Operation(
                summary: 'Remove multiple items from cart',
                description: 'Remove multiple items from the shopping cart in a single operation.',
                requestBody: new Model\RequestBody(
                    description: 'Cart items to remove',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'itemIds' => [
                                        'type'        => 'array',
                                        'items'       => ['type' => 'integer'],
                                        'example'     => [7, 8, 9],
                                        'description' => 'Array of cart item IDs',
                                    ],
                                ],
                            ],
                            'examples' => [
                                'remove_items' => [
                                    'summary'     => 'Remove Multiple Items',
                                    'description' => 'Remove multiple items from cart',
                                    'value'       => [
                                        'itemIds' => [7, 8, 9],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Remove multiple items from cart. Use token and itemIds array.',
        ),
    ]
)]
class RemoveCartItems
{
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;
}
