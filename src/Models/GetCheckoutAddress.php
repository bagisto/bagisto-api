<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
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
    operations: [],
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
