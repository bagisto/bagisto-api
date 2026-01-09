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
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Remove coupon from cart.',
            openapi: new Model\Operation(
                summary: 'Remove coupon from cart',
                description: 'Remove the applied discount coupon from the shopping cart.',
                requestBody: new Model\RequestBody(
                    description: 'Remove coupon request',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [],
                            ],
                            'examples' => [
                                'remove_coupon' => [
                                    'summary'     => 'Remove Coupon',
                                    'description' => 'Remove the applied coupon code',
                                    'value'       => new \stdClass,
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
