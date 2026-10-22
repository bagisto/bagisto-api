<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminTokenRefreshInput;
use Webkul\BagistoApi\Admin\State\AdminTokenRefreshProcessor;
use Webkul\BagistoApi\OpenApi\AdminAuthResponses;
use Webkul\BagistoApi\OpenApi\ApiSchemas;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminTokenRefresh',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/auth/refresh',
            input: AdminTokenRefreshInput::class,
            output: self::class,
            processor: AdminTokenRefreshProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Exchange a refresh token for a new token pair',
                description: 'Returns a new access token and a new refresh token in the same session; the refresh token sent stops working. Presenting a refresh token that was already used revokes the whole session. Once the refresh token expires, or the session reaches its absolute lifetime, log in again.',
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
                                        'example' => '77|3f9c0d...',
                                    ],
                                ],
                            ],

                            'example' => [
                                'refresh_token' => '77|3f9c0d...',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'A new token pair for the same session. Store both tokens: the refresh token sent no longer works.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::ADMIN_TOKEN_PAIR_REF],
                                'example' => AdminAuthResponses::TOKEN_PAIR,
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(
                        description: 'The refresh token is unknown, expired, already used (which also revokes its session) or revoked, the session ended or reached its absolute lifetime, or the admin is inactive (`refresh_token_invalid`). Log in again.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::AUTH_ERROR_REF],
                                'examples' => [
                                    'refresh_token_invalid' => ['value' => AdminAuthResponses::REFRESH_TOKEN_INVALID],
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(
                        description: 'Validation failure: no `refresh_token` in the body.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::PROBLEM_REF],
                                'example' => [
                                    'type' => '/errors/422',
                                    'title' => 'Bad Request',
                                    'status' => 422,
                                    'detail' => 'The refresh token field is required.',
                                ],
                            ],
                        ]),
                    ),
                    '429' => new Model\Response(
                        description: 'More than 60 requests a minute from this address (`rate_limit_exceeded`). Wait for `Retry-After` seconds.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::AUTH_ERROR_REF],
                                'examples' => [
                                    'rate_limit_exceeded' => ['value' => AdminAuthResponses::RATE_LIMIT_EXCEEDED],
                                ],
                            ],
                        ]),
                        headers: new \ArrayObject(AdminAuthResponses::RETRY_AFTER),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminTokenRefreshInput::class,
            output: self::class,
            processor: AdminTokenRefreshProcessor::class,
            description: 'Exchange a refresh token for a new access and refresh token. Becomes createAdminTokenRefresh; reachable without a token.',
        ),
    ],
)]
class AdminTokenRefresh extends AdminAuthTokenResponse {}
