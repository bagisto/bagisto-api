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
 * UpdateCartItem - GraphQL & REST API Resource for Updating Cart Items
 *
 * Provides mutation for updating cart item quantity without requiring resource ID.
 * Uses 'create' operation name to bypass API Platform's ID requirement.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'UpdateCartItem',
    operations: [
        new Post(
            name: 'updateItem',
            uriTemplate: '/update-cart-item',
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
            description: 'Update cart item quantity.',
            openapi: new Model\Operation(
                tags: ['Cart'],
                summary: 'Update cart item quantity',
                description: 'Update the quantity of an item in the cart.',
                requestBody: new Model\RequestBody(
                    description: 'Cart item update data',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['cartItemId', 'quantity'],
                                'properties' => [
                                    'cartItemId' => ['type' => 'integer', 'example' => 1, 'description' => 'Cart item ID to update'],
                                    'quantity' => ['type' => 'integer', 'example' => 3, 'description' => 'New quantity'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Cart item quantity updated. Returns the full cart.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 6888,
                                    'cartToken' => '6888',
                                    'customerId' => 1537,
                                    'channelId' => 1,
                                    'itemsCount' => 1,
                                    'items' => [
                                        [
                                            'id' => 7765,
                                            'cartId' => 6888,
                                            'productId' => 1,
                                            'name' => "Coastal Breeze Men's Blue Zipper Hoodie",
                                            'sku' => 'COASTALBREEZEMENSHOODIE',
                                            'quantity' => 2,
                                            'price' => 100,
                                            'total' => 200,
                                            'type' => 'simple',
                                            'formattedPrice' => '$100.00',
                                            'formattedTotal' => '$200.00',
                                        ],
                                    ],
                                    'subtotal' => 200,
                                    'grandTotal' => 200,
                                    'taxAmount' => 0,
                                    'discountAmount' => 0,
                                    'couponCode' => null,
                                    'formattedSubtotal' => '$200.00',
                                    'formattedGrandTotal' => '$200.00',
                                    'success' => true,
                                    'message' => 'Cart item updated successfully',
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(
                        description: 'Missing cartItemId/quantity, or item not in cart.',
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
            description: 'Update cart item quantity. Use token, cartItemId, and quantity.',
        ),
    ]
)]
class UpdateCartItem
{
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $itemsCount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $subtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $grandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $discountAmount = null;
}
