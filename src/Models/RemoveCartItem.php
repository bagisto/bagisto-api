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
    shortName: 'RemoveCartItem',
    operations: [
        new Post(
            name: 'removeItem',
            uriTemplate: '/remove-cart-item',
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
            description: 'Remove item from cart. Use token and cartItemId.',
            openapi: new Operation(
                tags: ['Cart'],
                summary: 'Remove item from cart',
                description: 'Remove a single item from the cart by cart item ID.',
                requestBody: new RequestBody(
                    description: 'Cart item to remove',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['cartItemId'],

                                'properties' => [
                                    'cartItemId' => [
                                        'type' => 'integer',
                                        'example' => 1,
                                        'description' => 'Cart item ID to remove',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Updated cart with the item removed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 6888,
                                    'cartToken' => '6888',
                                    'customerId' => 1537,
                                    'channelId' => 1,
                                    'itemsCount' => 0,
                                    'items' => [],
                                    'subtotal' => 0,
                                    'grandTotal' => 0,
                                    'taxAmount' => 0,
                                    'discountAmount' => 0,
                                    'couponCode' => null,
                                    'formattedSubtotal' => '$0.00',
                                    'formattedGrandTotal' => '$0.00',
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Response(
                        description: 'Missing cartItemId, or item not in cart.',
                    ),
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
            description: 'Remove item from cart. Use token and cartItemId.',
        ),
    ]
)]
class RemoveCartItem
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

    /**
     * The items count.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $itemsCount = null;

    /**
     * The subtotal.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $subtotal = null;

    /**
     * The grand total.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $grandTotal = null;

    /**
     * The discount amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $discountAmount = null;
}
