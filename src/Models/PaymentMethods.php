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
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Available payment methods.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id'             => 'moneytransfer',
                                        'method'         => 'moneytransfer',
                                        'title'          => 'Money Transfer',
                                        'description'    => 'Money Transfer',
                                        'icon'           => 'http://127.0.0.1:8000/themes/shop/default/build/assets/money-transfer-BNjtOcYo.png',
                                        'additionalData' => null,
                                        'isAllowed'      => true,
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
            output: PaymentMethodOutput::class,
            provider: PaymentMethodsProvider::class,
            paginationEnabled: false,
            description: 'Get available payment methods for a cart by token',
        ),
    ]
)]
class PaymentMethods {}
