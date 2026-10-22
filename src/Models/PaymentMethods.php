<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Dto\PaymentMethodOutput;
use Webkul\BagistoApi\OpenApi\ApiSchemas;
use Webkul\BagistoApi\State\PaymentMethodsProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'PaymentMethods',
    operations: [
        new GetCollection(
            uriTemplate: '/payment-methods',
            output: PaymentMethodOutput::class,
            provider: PaymentMethodsProvider::class,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Operation(
                tags: ['Checkout'],
                summary: 'Get available payment methods',
                description: 'Returns the payment methods available for the cart the bearer token names: a guest cart token or a customer token. A missing or unknown token answers 404, not 401.',
                responses: [
                    '200' => new Response(
                        description: 'Available payment methods.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 'moneytransfer',
                                        'method' => 'moneytransfer',
                                        'title' => 'Money Transfer',
                                        'description' => 'Money Transfer',
                                        'icon' => 'http://127.0.0.1:8000/themes/shop/default/build/assets/money-transfer-BNjtOcYo.png',
                                        'additionalData' => null,
                                        'isAllowed' => true,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Response(
                        description: 'The `X-STOREFRONT-KEY` header is missing (`missing_key`).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::STOREFRONT_KEY_ERROR_REF],
                                'example' => [
                                    'message' => 'X-STOREFRONT-KEY header is required',
                                    'error' => 'missing_key',
                                    'header_name' => 'X-STOREFRONT-KEY',
                                    'key_type' => 'shop',
                                ],
                            ],
                        ]),
                    ),
                    '403' => new Response(
                        description: 'The storefront key is invalid or inactive, or not allowed from this IP address (`invalid_key`).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::STOREFRONT_KEY_ERROR_REF],
                                'example' => [
                                    'message' => 'Invalid or inactive shop API key',
                                    'error' => 'invalid_key',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'No bearer token was sent, or it names no cart (a problem document).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::PROBLEM_REF],
                                'examples' => [
                                    'token_missing' => [
                                        'value' => [
                                            'type' => '/errors/404',
                                            'title' => 'Not Found',
                                            'status' => 404,
                                            'detail' => 'Authentication token is required',
                                        ],
                                    ],
                                    'invalid_token' => [
                                        'value' => [
                                            'type' => '/errors/404',
                                            'title' => 'Not Found',
                                            'status' => 404,
                                            'detail' => 'Invalid or expired authentication token',
                                        ],
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
