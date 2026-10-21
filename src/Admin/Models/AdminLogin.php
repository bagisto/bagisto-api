<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminLoginInput;
use Webkul\BagistoApi\Admin\State\AdminLoginProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminLogin',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/auth/login',
            input: AdminLoginInput::class,
            output: self::class,
            processor: AdminLoginProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Log in with email and password',
                description: 'Starts a session and returns a short-lived JWT access token and a refresh token. Send the access token as `Authorization: Bearer <accessToken>` and exchange the refresh token at `/api/admin/auth/refresh` before or after the access token expires. Five failed attempts per email and address in a minute lock the login for that minute.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['email', 'password'],

                                'properties' => [
                                    'email' => [
                                        'type' => 'string',
                                        'format' => 'email',
                                        'example' => 'admin@example.com',
                                    ],

                                    'password' => [
                                        'type' => 'string',
                                        'format' => 'password',
                                        'example' => 'admin123',
                                    ],

                                    'device_name' => [
                                        'type' => 'string',
                                        'maxLength' => 100,
                                        'example' => 'iPhone',
                                    ],

                                    'client_type' => [
                                        'type' => 'string',
                                        'enum' => AdminApiSession::CLIENT_TYPES,
                                        'example' => 'mobile',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Logged in.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'tokenType' => 'Bearer',
                                    'accessToken' => 'eyJhbGciOiJFUzI1NiIsImtpZCI6Ii4uLiIsInR5cCI6IkpXVCJ9.eyJ...',
                                    'expiresIn' => 900,
                                    'accessTokenExpiresAt' => '2026-10-21T10:15:00+05:30',
                                    'refreshToken' => '77|3f9c0d...',
                                    'refreshTokenExpiresAt' => '2026-11-20T10:00:00+05:30',
                                    'session' => [
                                        'id' => 12,
                                        'name' => 'iPhone',
                                        'clientType' => 'mobile',
                                        'expiresAt' => '2027-01-19T10:00:00+05:30',
                                    ],
                                    'admin' => [
                                        'id' => 3,
                                        'name' => 'Example Admin',
                                        'email' => 'admin@example.com',
                                        'role' => [
                                            'id' => 1,
                                            'name' => 'Administrator',
                                            'permissionType' => 'all',
                                        ],
                                    ],
                                    'permissions' => ['*'],
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Wrong email or password (`invalid_credentials`).'),
                    '403' => new Model\Response(description: 'The admin is inactive (`account_inactive`) or has two-factor authentication enabled (`two_factor_required`).'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                    '429' => new Model\Response(description: 'Too many failed attempts (`too_many_login_attempts`); see `Retry-After`.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminLoginInput::class,
            output: self::class,
            processor: AdminLoginProcessor::class,
            description: 'Log in with email and password and get an access and refresh token. Becomes createAdminLogin; reachable without a token.',
        ),
    ],
)]
class AdminLogin extends AdminAuthTokenResponse {}
