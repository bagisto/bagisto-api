<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminTokenRefreshInput;
use Webkul\BagistoApi\Admin\State\AdminTokenRefreshProcessor;

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
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'A new token pair, in the same shape as the login response.'),
                    '401' => new Model\Response(description: 'The refresh token is expired, used or revoked, or the session ended (`refresh_token_invalid`).'),
                    '422' => new Model\Response(description: 'Validation failure.'),
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
