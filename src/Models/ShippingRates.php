<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\Dto\ShippingRateOutput;
use Webkul\BagistoApi\State\ShippingRatesProvider;

/**
 * ShippingRates - GraphQL API Resource for Shipping Rates
 *
 * Provides query for fetching available shipping rates during checkout
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ShippingRates',
    operations: [],
    graphQlOperations: [
        new QueryCollection(
            name: 'collection',
            output: ShippingRateOutput::class,
            provider: ShippingRatesProvider::class,
            paginationEnabled: false,
            args: [
                'token' => [
                    'type'        => 'String!',
                    'description' => 'Guest cart token or Customer Auth token',
                ],
            ],
            description: 'Get available shipping rates for a cart by token',
        ),
    ]
)]
class ShippingRates {}
