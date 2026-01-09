<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CheckoutAddressInput;
use Webkul\BagistoApi\Dto\CheckoutAddressOutput;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CheckoutAddressProvider;
use Webkul\BagistoApi\State\CheckoutProcessor;

/**
 * CheckoutAddress - GraphQL API Resource for Checkout Address
 *
 * Provides mutation for saving billing and shipping addresses during checkout
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CheckoutAddress',
    uriTemplate: '/checkout-addresses',
    operations: [
        new Get(uriTemplate: '/checkout-addresses/{id}'),
        new GetCollection(uriTemplate: '/checkout-addresses'),
    ],
    graphQlOperations: [
        new Query(
            name: 'read',
            output: CheckoutAddressOutput::class,
            provider: CheckoutAddressProvider::class,
            resolver: BaseQueryItemResolver::class,
            normalizationContext: [
                'groups'                 => ['query'],
            ],
            description: 'Get billing and shipping addresses for a cart by token',
        ),
        new Mutation(
            name: 'create',
            input: CheckoutAddressInput::class,
            output: CheckoutAddressOutput::class,
            processor: CheckoutProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Save billing and shipping addresses for checkout. Returns the created address.',
        ),
    ]
)]
class CheckoutAddress
{
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;
}
