<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Dto\CustomerTokenRefreshInput;
use Webkul\BagistoApi\OpenApi\CustomerAuthResponses;
use Webkul\BagistoApi\State\CustomerTokenRefreshProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerTokenRefresh',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customer/refresh',
            input: CustomerTokenRefreshInput::class,
            output: self::class,
            processor: CustomerTokenRefreshProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Customer Authentication'],
                summary: 'Exchange a customer refresh token for a new token pair',
                description: 'Returns a new access token and a new refresh token in the same session; the refresh token sent stops working. Presenting a refresh token that was already used revokes the whole session. Once the refresh token expires, or the session reaches its absolute lifetime, log in again. Needs the storefront key only.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['refresh_token'],

                                'properties' => [
                                    'refresh_token' => [
                                        'type' => 'string',
                                        'example' => '88|7c1f4e2a9b...',
                                    ],
                                ],
                            ],

                            'example' => [
                                'refresh_token' => '88|7c1f4e2a9b...',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'A new token pair for the same session. Store both tokens: the refresh token sent no longer works.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => CustomerAuthResponses::TOKEN_PAIR,
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(
                        description: 'The refresh token is unknown, expired, already used (which also revokes its session) or revoked, the session ended, or the customer is inactive (`refresh_token_invalid`). Log in again.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => CustomerAuthResponses::REFRESH_TOKEN_INVALID,
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'No `refresh_token` in the body.'),
                    '429' => new Model\Response(
                        description: 'More token refreshes in this session than allowed in a minute, 10 by default (`too_many_refresh_requests`). Wait for `Retry-After` seconds.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => CustomerAuthResponses::TOO_MANY_REFRESH_REQUESTS,
                            ],
                        ]),
                        headers: new \ArrayObject(CustomerAuthResponses::RETRY_AFTER),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CustomerTokenRefreshInput::class,
            output: self::class,
            processor: CustomerTokenRefreshProcessor::class,
            description: 'Exchange a customer refresh token for a new access and refresh token. Becomes createCustomerTokenRefresh; needs the storefront key only.',
        ),
    ],
)]
class CustomerTokenRefresh extends CustomerAuthTokenResponse {}
