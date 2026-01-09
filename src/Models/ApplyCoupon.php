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
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Apply coupon code to cart.',
            openapi: new Model\Operation(
                summary: 'Apply coupon to cart',
                description: 'Apply a discount coupon code to the shopping cart.',
                requestBody: new Model\RequestBody(
                    description: 'Coupon code to apply',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'couponCode' => [
                                        'type'        => 'string',
                                        'example'     => 'DISCOUNT10',
                                        'description' => 'Coupon code',
                                    ],
                                ],
                            ],
                            'examples' => [
                                'apply_coupon' => [
                                    'summary'     => 'Apply Coupon',
                                    'description' => 'Apply a discount coupon code',
                                    'value'       => [
                                        'couponCode' => 'DISCOUNT10',
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
