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
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Update cart item quantity.',
            openapi: new Model\Operation(
                summary: 'Update cart item quantity',
                description: 'Update the quantity of an item in the shopping cart.',
                requestBody: new Model\RequestBody(
                    description: 'Cart item update data',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'cartItemId' => [
                                        'type'        => 'integer',
                                        'example'     => 7,
                                        'description' => 'Cart item ID',
                                    ],
                                    'quantity' => [
                                        'type'        => 'integer',
                                        'example'     => 2,
                                        'description' => 'New quantity',
                                    ],
                                ],
                            ],
                            'examples' => [
                                'update_quantity' => [
                                    'summary'     => 'Update Item Quantity',
                                    'description' => 'Update the quantity of a cart item',
                                    'value'       => [
                                        'cartItemId' => 7,
                                        'quantity'   => 2,
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
