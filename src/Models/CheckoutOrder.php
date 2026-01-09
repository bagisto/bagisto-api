<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
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
        new Get(uriTemplate: '/checkout-orders/{id}'),
        new GetCollection(uriTemplate: '/checkout-orders'),
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
