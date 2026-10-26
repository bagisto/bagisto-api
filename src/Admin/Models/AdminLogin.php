<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminLoginInput;
use Webkul\BagistoApi\Admin\State\AdminLoginProcessor;
use Webkul\BagistoApi\OpenApi\AdminAuthResponses;
use Webkul\BagistoApi\OpenApi\ApiSchemas;

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
                description: 'Starts a session and returns a short-lived JWT access token and a refresh token. Send the access token as `Authorization: Bearer <accessToken>` and exchange the refresh token at `/api/admin/auth/refresh` before or after the access token expires. When the admin has two-factor authentication enabled, the response is `202` with `twoFactorRequired: true` and a single-use `challengeToken` valid for 5 minutes by default, and no tokens: send it with the code to `/api/admin/auth/two-factor`. Five failed attempts per email and address lock the login for 60 seconds by default; both are configurable under Configuration → API → Admin Authentication → Login Protection.',
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

                            'example' => [
                                'email' => 'admin@example.com',
                                'password' => 'admin123',
                                'device_name' => 'iPhone',
                                'client_type' => 'mobile',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Logged in: the token pair, with `twoFactorRequired: false`.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => AdminAuthResponses::LOGIN_SCHEMA,
                                'example' => AdminAuthResponses::LOGIN,
                            ],
                        ]),
                    ),
                    '202' => new Model\Response(
                        description: 'Password accepted; a second factor is required. `twoFactorRequired` is true, `challengeToken` is valid once for 5 minutes, and every token field is null.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => AdminAuthResponses::LOGIN_SCHEMA,
                                'example' => AdminAuthResponses::LOGIN_CHALLENGE,
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(
                        description: 'Wrong email or password (`invalid_credentials`). The message is the same whether or not the email exists.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::AUTH_ERROR_REF],
                                'examples' => [
                                    'invalid_credentials' => ['value' => AdminAuthResponses::INVALID_CREDENTIALS],
                                ],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(
                        description: 'The admin is inactive (`account_inactive`).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::AUTH_ERROR_REF],
                                'examples' => [
                                    'account_inactive' => ['value' => AdminAuthResponses::ACCOUNT_INACTIVE],
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(
                        description: 'Validation failure: a missing or malformed `email`, a missing `password`, or an unknown `client_type`. `detail` holds the first message.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::PROBLEM_REF],
                                'example' => AdminAuthResponses::VALIDATION_FAILED,
                            ],
                        ]),
                    ),
                    '429' => new Model\Response(
                        description: 'Five failed logins for this email and address in a minute (`too_many_login_attempts`), or more than 60 requests a minute from this address (`rate_limit_exceeded`). Wait for `Retry-After` seconds.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::AUTH_ERROR_REF],
                                'examples' => [
                                    'too_many_login_attempts' => ['value' => AdminAuthResponses::TOO_MANY_LOGIN_ATTEMPTS],
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
