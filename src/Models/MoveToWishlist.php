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
 * MoveToWishlist - GraphQL API Resource for Moving Items to Wishlist
 *
 * Provides mutation for moving multiple items from cart to wishlist.
 */
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
            deserialize: false,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            openapi: new Model\Operation(
                tags: ['Cart'],
                summary: 'Move cart items to the wishlist',
                description: 'Moves cart items of the Bearer token cart into the wishlist of the customer and removes them from the cart. Requires a customer login. `quantities` matches `itemIds` by position and defaults to 1.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['itemIds'],
                                'properties' => [
                                    'itemIds' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [7766]],
                                    'quantities' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1]],
                                ],
                            ],
                            'example' => ['itemIds' => [7766], 'quantities' => [1]],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Returns the full cart.'),
                    '400' => new Model\Response(description: 'Missing or invalid input.'),
                    '404' => new Model\Response(description: 'Cart not found for the Bearer token.'),
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
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;
}
