<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\State\CartTokenMutationProvider;
use Webkul\BagistoApi\State\CartTokenProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'MoveToWishlist',
    uriTemplate: '/move-to-wishlists',
    operations: [
        new Post(
            uriTemplate: '/move-to-wishlists',
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Cart'],
                summary: 'Move cart items to the wishlist',
                description: 'Moves the specified items from the bearer cart to the authenticated customer\'s wishlist. Item IDs are scoped to the current cart.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['itemIds'],

                                'properties' => [
                                    'itemIds' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'integer'],
                                        'example' => [1, 2],
                                    ],

                                    'quantities' => [
                                        'type' => 'array',

                                        'items' => [
                                            'type' => 'integer',
                                            'minimum' => 1,
                                        ],

                                        'example' => [1, 2],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    201 => new Response(description: 'The updated cart after its items were moved.'),
                    400 => new Response(description: 'Item IDs are missing or invalid.'),
                    401 => new Response(description: 'A bearer cart token is required.'),
                    404 => new Response(description: 'The cart or its items were not found.'),
                ],
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
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Move items to wishlist. Use token, itemIds array, and quantities.',
        ),
    ]
)]
class MoveToWishlist
{
    /**
     * The id.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    /**
     * The cart token.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;
}
