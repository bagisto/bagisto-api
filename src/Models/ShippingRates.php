<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\Dto\ShippingRateOutput;
use Webkul\BagistoApi\State\ShippingRatesProvider;
use Webkul\Checkout\Models\CartShippingRate;

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
class ShippingRates extends CartShippingRate {}
