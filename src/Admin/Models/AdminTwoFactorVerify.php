<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminTwoFactorVerifyInput;
use Webkul\BagistoApi\Admin\State\AdminTwoFactorVerifyProcessor;
use Webkul\BagistoApi\OpenApi\AdminAuthResponses;
use Webkul\BagistoApi\OpenApi\ApiSchemas;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminTwoFactorVerify',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/auth/two-factor',
            input: AdminTwoFactorVerifyInput::class,
            output: self::class,
            processor: AdminTwoFactorVerifyProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Finish a login with a two-factor code',
                description: 'Exchanges the `challengeToken` of a `202` login, with the 6-digit authenticator `code` or a `backupCode`, for the access and refresh token pair. A challenge works once and for 5 minutes; five wrong codes revoke it, and five wrong codes a minute for the same admin lock further codes for that minute. A code from an already used time window is refused, and a backup code is used up.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['challenge_token'],

                                'properties' => [
                                    'challenge_token' => [
                                        'type' => 'string',
                                        'example' => '41|9b1e7c...',
                                    ],

                                    'code' => [
                                        'type' => 'string',
                                        'pattern' => '^[0-9]{6}$',
                                        'example' => '123456',
                                    ],

                                    'backup_code' => [
                                        'type' => 'string',
                                        'example' => '804213',
                                    ],
                                ],
                            ],

                            'example' => [
                                'challenge_token' => '41|9b1e7c...',
                                'code' => '123456',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Logged in: the token pair, as a login without a second factor returns it.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::ADMIN_TOKEN_PAIR_REF],
                                'example' => AdminAuthResponses::TOKEN_PAIR,
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(
                        description: 'Wrong code (`invalid_two_factor_code`): retry with the same challenge. The challenge is unknown, used or expired, or a fifth wrong code revoked it (`two_factor_challenge_invalid`): log in again.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::AUTH_ERROR_REF],
                                'examples' => [
                                    'invalid_two_factor_code' => ['value' => AdminAuthResponses::INVALID_TWO_FACTOR_CODE],
                                    'two_factor_challenge_invalid' => ['value' => AdminAuthResponses::TWO_FACTOR_CHALLENGE_INVALID],
                                    'two_factor_attempts_exceeded' => ['value' => AdminAuthResponses::TWO_FACTOR_ATTEMPTS_EXCEEDED],
                                ],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(
                        description: 'The admin was deactivated after the password step (`account_inactive`).',
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
                        description: 'Validation failure: no `challenge_token`, neither or both of `code` and `backup_code`, or a `code` that is not 6 digits. `detail` holds the first message.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::PROBLEM_REF],
                                'example' => [
                                    'type' => '/errors/422',
                                    'title' => 'Bad Request',
                                    'status' => 422,
                                    'detail' => 'The challenge token field is required.',
                                ],
                            ],
                        ]),
                    ),
                    '429' => new Model\Response(
                        description: 'Five wrong codes for this admin in a minute (`too_many_login_attempts`), or more than 60 requests a minute from this address (`rate_limit_exceeded`). Wait for `Retry-After` seconds.',
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
            input: AdminTwoFactorVerifyInput::class,
            output: self::class,
            processor: AdminTwoFactorVerifyProcessor::class,
            description: 'Finish a login that returned twoFactorRequired with an authenticator code or a backup code. Becomes createAdminTwoFactorVerify; reachable without a token.',
        ),
    ],
)]
class AdminTwoFactorVerify extends AdminAuthTokenResponse {}
