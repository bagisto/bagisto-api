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
            description: 'Get available shipping rates for a cart by token',
        ),
    ]
)]
class ShippingRates extends \Webkul\Checkout\Models\CartShippingRate {}
