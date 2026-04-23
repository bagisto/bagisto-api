<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CheckoutAddressInput;
use Webkul\BagistoApi\State\CheckoutProcessor;

/**
 * CheckoutOrder - GraphQL API Resource for Creating Order from Cart
 *
 * Provides mutation for finalizing checkout and creating order
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CheckoutOrder',
    uriTemplate: '/checkout-orders',
    operations: [
        new Post(
            uriTemplate: '/checkout-orders',
            processor: CheckoutProcessor::class,
            normalizationContext: [
                'groups'           => ['mutation'],
                'skip_null_values' => false,
            ],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Checkout'],
                summary: 'Create order from cart',
                description: 'Finalizes checkout and creates an order from the current cart. The cart is identified by the Bearer token in the Authorization header; all address, shipping, and payment data must already be saved on the cart.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => new \ArrayObject,
                            ],
                            'example' => new \ArrayObject,
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CheckoutAddressInput::class,
            output: CartData::class,
            processor: CheckoutProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Create order from cart. Validates all required fields and creates order. Returns order ID and redirect URL if payment redirect required.',
        ),
    ]
)]
class CheckoutOrder
{
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $orderId = null;
}
