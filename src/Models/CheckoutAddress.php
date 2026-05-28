<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\Post;
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
        new Post(
            uriTemplate: '/checkout-addresses',
            output: CheckoutAddressOutput::class,
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
                tags: ['Checkout'],
                summary: 'Save billing and shipping addresses for checkout',
                description: 'Saves billing and shipping addresses to the current cart. Use `useForShipping: true` to copy billing as shipping, or provide shipping fields for a different shipping address.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'     => 'object',
                                'required' => [
                                    'billingFirstName',
                                    'billingLastName',
                                    'billingEmail',
                                    'billingAddress',
                                    'billingCity',
                                    'billingCountry',
                                    'billingState',
                                    'billingPostcode',
                                    'billingPhoneNumber',
                                ],
                                'properties' => [
                                    'billingFirstName'    => ['type' => 'string', 'example' => 'John'],
                                    'billingLastName'     => ['type' => 'string', 'example' => 'Doe'],
                                    'billingEmail'        => ['type' => 'string', 'example' => 'john@example.com'],
                                    'billingCompanyName'  => ['type' => 'string', 'example' => ''],
                                    'billingAddress'      => ['type' => 'string', 'example' => '123 Main St'],
                                    'billingCity'         => ['type' => 'string', 'example' => 'Los Angeles'],
                                    'billingCountry'      => ['type' => 'string', 'example' => 'US'],
                                    'billingState'        => ['type' => 'string', 'example' => 'CA'],
                                    'billingPostcode'     => ['type' => 'string', 'example' => '90001'],
                                    'billingPhoneNumber'  => ['type' => 'string', 'example' => '2125551234'],
                                    'useForShipping'      => ['type' => 'boolean', 'example' => true],
                                    'shippingFirstName'   => ['type' => 'string', 'example' => 'Jane'],
                                    'shippingLastName'    => ['type' => 'string', 'example' => 'Doe'],
                                    'shippingEmail'       => ['type' => 'string', 'example' => 'jane@example.com'],
                                    'shippingCompanyName' => ['type' => 'string', 'example' => ''],
                                    'shippingAddress'     => ['type' => 'string', 'example' => '456 Oak Ave'],
                                    'shippingCity'        => ['type' => 'string', 'example' => 'San Francisco'],
                                    'shippingCountry'     => ['type' => 'string', 'example' => 'US'],
                                    'shippingState'       => ['type' => 'string', 'example' => 'CA'],
                                    'shippingPostcode'    => ['type' => 'string', 'example' => '94102'],
                                    'shippingPhoneNumber' => ['type' => 'string', 'example' => '4155559876'],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
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
