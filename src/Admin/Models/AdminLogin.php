<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
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
                description: 'Starts a session and returns a short-lived JWT access token and a refresh token. Send the access token as `Authorization: Bearer <accessToken>` and exchange the refresh token at `/api/admin/auth/refresh` before or after the access token expires. When the admin has two-factor authentication enabled, the response is `202` with `twoFactorRequired: true` and a single-use `challengeToken` valid for 5 minutes, and no tokens: send it with the code to `/api/admin/auth/two-factor`. Five failed attempts per email and address in a minute lock the login for that minute.',
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
                                    'twoFactorRequired' => false,
                                    'challengeToken' => null,
                                    'challengeTokenExpiresAt' => null,
                                    'message' => null,
                                ],
                            ],
                        ]),
                    ),
                    '202' => new Model\Response(
                        description: 'Password accepted; a second factor is required. Every token field is null.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'twoFactorRequired' => true,
                                    'challengeToken' => '41|9b1e7c...',
                                    'challengeTokenExpiresAt' => '2026-10-21T10:05:00+05:30',
                                    'message' => 'Enter the 6-digit code from your authenticator app, or one of your backup codes, to finish logging in.',
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Wrong email or password (`invalid_credentials`).'),
                    '403' => new Model\Response(description: 'The admin is inactive (`account_inactive`).'),
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
            description: 'Log in with email and password and get an access and refresh token, or a challengeToken for createAdminTwoFactorVerify when twoFactorRequired is true. Becomes createAdminLogin; reachable without a token.',
        ),
    ],
)]
class AdminLogin extends AdminAuthTokenResponse
{
    /**
     * Whether the admin must still pass a second factor, in which case no token is returned.
     */
    #[ApiProperty(writable: false, example: false)]
    public ?bool $two_factor_required = null;

    /**
     * The single-use token to send with the two-factor code.
     */
    #[ApiProperty(writable: false, example: '41|9b1e7c...')]
    public ?string $challenge_token = null;

    /**
     * When the challenge token expires.
     */
    #[ApiProperty(writable: false, example: '2026-10-21T10:05:00+05:30')]
    public ?string $challenge_token_expires_at = null;

    /**
     * What the client has to do next, when a second factor is required.
     */
    #[ApiProperty(writable: false, example: null)]
    public ?string $message = null;
}
