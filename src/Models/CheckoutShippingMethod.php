<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CheckoutAddressInput;
use Webkul\BagistoApi\Dto\ShippingRateOutput;
use Webkul\BagistoApi\State\CheckoutProcessor;
use Webkul\BagistoApi\State\ShippingRatesProvider;

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
        new GetCollection(
            uriTemplate: '/checkout-shipping-methods',
            output: ShippingRateOutput::class,
            provider: ShippingRatesProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['CheckoutShippingMethod'],
                summary: 'Get available shipping methods',
                description: 'Returns the shipping rates available for the authenticated customer\'s active cart. The cart must have a shipping address set (via POST /api/shop/checkout-addresses) before rates can be computed.',
            ),
        ),
        new Post(
            uriTemplate: '/checkout-shipping-methods',
            output: self::class,
            processor: CheckoutProcessor::class,
            normalizationContext: [
                'groups'            => ['mutation'],
                'skip_null_values'  => false,
            ],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['CheckoutShippingMethod'],
                summary: 'Save selected shipping method for checkout',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['shippingMethod'],
                                'properties' => [
                                    'shippingMethod' => ['type' => 'string', 'example' => 'flatrate_flatrate'],
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
