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
 * RemoveCoupon - GraphQL & REST API Resource for Removing Coupon
 *
 * Provides mutation for removing applied coupon code from cart.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'RemoveCoupon',
    operations: [
        new Post(
            name: 'removeCoupon',
            uriTemplate: '/remove-coupon',
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
            description: 'Remove coupon from cart.',
            openapi: new Model\Operation(
                tags: ['Cart'],
                summary: 'Remove coupon from cart',
                description: 'Remove the applied coupon code from the cart.',
                requestBody: new Model\RequestBody(
                    description: 'Empty body',
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => new \ArrayObject,
                            ],
                            'example' => new \ArrayObject,
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Coupon removed. Returns the full cart.',
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
                                            'id' => 7766,
                                            'cartId' => 6888,
                                            'productId' => 1,
                                            'name' => "Coastal Breeze Men's Blue Zipper Hoodie",
                                            'sku' => 'COASTALBREEZEMENSHOODIE',
                                            'quantity' => 1,
                                            'price' => 100,
                                            'total' => 100,
                                            'type' => 'simple',
                                            'formattedPrice' => '$100.00',
                                            'formattedTotal' => '$100.00',
                                        ],
                                    ],
                                    'subtotal' => 100,
                                    'grandTotal' => 100,
                                    'taxAmount' => 0,
                                    'discountAmount' => 0,
                                    'couponCode' => null,
                                    'formattedSubtotal' => '$100.00',
                                    'formattedGrandTotal' => '$100.00',
                                    'success' => true,
                                    'message' => 'Coupon removed successfully',
                                ],
                            ],
                        ]),
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
            description: 'Remove coupon code from cart. Use token.',
        ),
    ]
)]
class RemoveCoupon
{
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;
}
