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
 * EstimateShipping - GraphQL API Resource for Estimating Shipping Methods and Tax
 *
 * Provides mutation for estimating shipping methods and tax based on address.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'EstimateShipping',
    uriTemplate: '/estimate-shippings',
    operations: [
        new Post(
            uriTemplate: '/estimate-shippings',
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
            openapi: new Model\Operation(
                tags: ['Cart'],
                summary: 'Estimate shipping methods and tax',
                description: 'Estimates shipping and tax for the cart of the Bearer token from a country, state and postcode, without saving an address. Returns the cart with `shippingMethods`, the methods available for that address. Send `shippingMethod` to price one of them into the totals.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['country', 'state', 'postcode'],
                                'properties' => [
                                    'country' => ['type' => 'string', 'example' => 'US'],
                                    'state' => ['type' => 'string', 'example' => 'CA'],
                                    'postcode' => ['type' => 'string', 'example' => '90001'],
                                    'shippingMethod' => ['type' => 'string', 'example' => 'flatrate_flatrate'],
                                ],
                            ],
                            'example' => ['country' => 'US', 'state' => 'CA', 'postcode' => '90001'],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Returns the full cart.'),
                    '400' => new Model\Response(description: 'Missing or invalid input.'),
                    '404' => new Model\Response(description: 'Cart not found for the Bearer token.'),
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
            description: 'Estimate shipping methods and tax. Use token, country, state, postcode, and optional shippingMethod.',
        ),
    ]
)]
class EstimateShipping
{
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;
}
