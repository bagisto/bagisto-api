<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CheckoutAddressInput;
use Webkul\BagistoApi\State\CheckoutProcessor;

/**
 * CheckoutShippingMethod - GraphQL API Resource for Checkout Shipping Method
 *
 * Provides mutation for selecting and saving shipping method during checkout
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CheckoutShippingMethod',
    uriTemplate: '/checkout-shipping-methods',
    operations: [
        new Get(uriTemplate: '/checkout-shipping-methods/{id}'),
        new GetCollection(uriTemplate: '/checkout-shipping-methods'),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CheckoutAddressInput::class,
            output: self::class,
            processor: CheckoutProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Save selected shipping method for checkout. Returns success status and message.',
        ),
    ]
)]
class CheckoutShippingMethod
{
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public bool $success = false;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public string $message = '';

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $cartToken = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $shippingMethod = null;
}
