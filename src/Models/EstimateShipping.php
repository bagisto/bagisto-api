<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\State\CartTokenMutationProvider;
use Webkul\BagistoApi\State\CartTokenProcessor;

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
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Checkout'],
                summary: 'Estimate shipping and tax for the current cart',
                description: 'Estimates the cart using the supplied country, state, postcode, and optional shipping method. The cart is identified by the Bearer token.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['country', 'state', 'postcode'],

                                'properties' => [
                                    'country' => [
                                        'type' => 'string',
                                        'example' => 'US',
                                    ],

                                    'state' => [
                                        'type' => 'string',
                                        'example' => 'CA',
                                    ],

                                    'postcode' => [
                                        'type' => 'string',
                                        'example' => '90001',
                                    ],

                                    'shippingMethod' => [
                                        'type' => 'string',
                                        'example' => 'flatrate_flatrate',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    201 => new Response(description: 'Estimated cart totals and shipping information.'),
                    400 => new Response(description: 'The address details are incomplete.'),
                    401 => new Response(description: 'A bearer cart token is required.'),
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
    /**
     * The id.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    /**
     * The cart token.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;
}
