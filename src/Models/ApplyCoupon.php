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
 * ApplyCoupon - GraphQL & REST API Resource for Applying Coupon Code
 *
 * Provides mutation for applying a coupon code to cart.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ApplyCoupon',
    uriTemplate: '/apply-coupon',
    operations: [
        new Post(
            name: 'apply',
            uriTemplate: '/apply-coupon',
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            deserialize: false,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Apply coupon code to cart.',
            openapi: new Model\Operation(
                tags: ['Cart'],
                summary: 'Apply coupon to cart',
                description: 'Apply a discount coupon code to the cart.',
                requestBody: new Model\RequestBody(
                    description: 'Coupon code to apply',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['couponCode'],
                                'properties' => [
                                    'couponCode' => ['type' => 'string', 'example' => 'DISCOUNT20', 'description' => 'Coupon code to apply'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Coupon applied. Returns the full cart.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'                 => 6888,
                                    'cartToken'          => '6888',
                                    'customerId'         => 1537,
                                    'channelId'          => 1,
                                    'itemsCount'         => 1,
                                    'items'              => [
                                        [
                                            'id'             => 7766,
                                            'cartId'         => 6888,
                                            'productId'      => 1,
                                            'name'           => "Coastal Breeze Men's Blue Zipper Hoodie",
                                            'sku'            => 'COASTALBREEZEMENSHOODIE',
                                            'quantity'       => 1,
                                            'price'          => 100,
                                            'total'          => 100,
                                            'type'           => 'simple',
                                            'formattedPrice' => '$100.00',
                                            'formattedTotal' => '$100.00',
                                        ],
                                    ],
                                    'subtotal'            => 100,
                                    'grandTotal'          => 200,
                                    'taxAmount'           => 0,
                                    'discountAmount'      => 0,
                                    'couponCode'          => 'OLIVE99',
                                    'formattedSubtotal'   => '$100.00',
                                    'formattedGrandTotal' => '$200.00',
                                    'success'             => true,
                                    'message'             => 'Coupon applied successfully',
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(
                        description: 'Missing couponCode, or coupon invalid/inactive/not applicable to this cart.',
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
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Apply coupon code to cart. Use token and couponCode.',
        ),
    ]
)]
class ApplyCoupon
{
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;
}
