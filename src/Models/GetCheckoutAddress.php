<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\State\GetCheckoutAddressCollectionProvider;
use Webkul\Checkout\Models\CartAddress;

/**
 * GetCheckoutAddress - GraphQL Query Collection for Cart Addresses
 *
 * Extends CartAddress to inherit all address fields and expose them via
 * a custom GraphQL collection query for fetching addresses by cart token
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'GetCheckoutAddress',
    operations: [
        new GetCollection(
            uriTemplate: '/checkout-addresses',
            provider: GetCheckoutAddressCollectionProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Checkout'],
                summary: 'Get billing and shipping addresses for the authenticated cart',
                description: 'Returns the CartAddress rows (billing + shipping) attached to the cart identified by the Bearer token. Mirrors the GraphQL `collectionGetCheckoutAddresses` query.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'The current cart\'s saved billing and shipping addresses.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id'              => 5564,
                                        'addressType'     => 'cart_billing',
                                        'parentAddressId' => null,
                                        'orderId'         => null,
                                        'firstName'       => 'John',
                                        'lastName'        => 'Doe',
                                        'gender'          => null,
                                        'companyName'     => null,
                                        'address'         => '123 Main St',
                                        'city'            => 'New York',
                                        'state'           => 'NY',
                                        'country'         => 'US',
                                        'postcode'        => '10001',
                                        'email'           => 'john@example.com',
                                        'phone'           => '1234567890',
                                        'vatId'           => null,
                                        'defaultAddress'  => false,
                                        'useForShipping'  => false,
                                        'additional'      => null,
                                        'createdAt'       => '2026-07-02T13:03:53+05:30',
                                        'updatedAt'       => '2026-07-02T13:03:53+05:30',
                                        'name'            => 'John Doe',
                                    ],
                                    [
                                        'id'              => 5565,
                                        'addressType'     => 'cart_shipping',
                                        'parentAddressId' => null,
                                        'orderId'         => null,
                                        'firstName'       => 'John',
                                        'lastName'        => 'Doe',
                                        'gender'          => null,
                                        'companyName'     => null,
                                        'address'         => '123 Main St',
                                        'city'            => 'New York',
                                        'state'           => 'NY',
                                        'country'         => 'US',
                                        'postcode'        => '10001',
                                        'email'           => 'john@example.com',
                                        'phone'           => '1234567890',
                                        'vatId'           => null,
                                        'defaultAddress'  => false,
                                        'useForShipping'  => false,
                                        'additional'      => null,
                                        'createdAt'       => '2026-07-02T13:03:53+05:30',
                                        'updatedAt'       => '2026-07-02T13:03:53+05:30',
                                        'name'            => 'John Doe',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            name: 'collection',
            provider: GetCheckoutAddressCollectionProvider::class,
            args: [
                'first' => [
                    'type'        => 'Int',
                    'description' => 'Limit the number of addresses returned (pagination)',
                ],
                'after' => [
                    'type'        => 'String',
                    'description' => 'Relay cursor for forward pagination',
                ],
                'before' => [
                    'type'        => 'String',
                    'description' => 'Relay cursor for backward pagination',
                ],
                'last' => [
                    'type'        => 'Int',
                    'description' => 'Return the last N items (used with before cursor)',
                ],
            ],
            description: 'Get billing and shipping addresses for a cart.',
        ),
    ]
)]
class GetCheckoutAddress extends CartAddress
{
    // Inherits all CartAddress properties for GraphQL serialization
}
