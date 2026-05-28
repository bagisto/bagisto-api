<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
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
    operations: [
        new GetCollection(
            uriTemplate: '/payment-methods',
            output: PaymentMethodOutput::class,
            provider: PaymentMethodsProvider::class,
            normalizationContext: ['skip_null_values' => false],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Checkout'],
                summary: 'Get available payment methods',
                description: 'Returns the payment methods available for the authenticated customer\'s active cart.',
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            name: 'collection',
            output: PaymentMethodOutput::class,
            provider: PaymentMethodsProvider::class,
            paginationEnabled: false,
            description: 'Get available payment methods for a cart by token',
        ),
    ]
)]
class PaymentMethods {}
