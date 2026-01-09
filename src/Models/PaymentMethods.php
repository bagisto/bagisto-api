<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\Dto\PaymentMethodOutput;
use Webkul\BagistoApi\State\PaymentMethodsProvider;

/**
 * PaymentMethods - GraphQL API Resource for Payment Methods
 *
 * Provides query for fetching available payment methods during checkout
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'PaymentMethods',
    operations: [],
    graphQlOperations: [
        new QueryCollection(
            name: 'collection',
            output: PaymentMethodOutput::class,
            provider: PaymentMethodsProvider::class,
            paginationEnabled: false,
            args: [
                'token' => [
                    'type'        => 'String!',
                    'description' => 'Cart token',
                ],
            ],
            description: 'Get available payment methods for a cart by token',
        ),
    ]
)]
class PaymentMethods {}
