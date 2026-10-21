<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminTwoFactorVerifyInput;
use Webkul\BagistoApi\Admin\State\AdminTwoFactorVerifyProcessor;

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
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Logged in; the same shape as the login response.'),
                    '401' => new Model\Response(description: 'Wrong code (`invalid_two_factor_code`), or the challenge is unknown, used, expired or revoked after five wrong codes (`two_factor_challenge_invalid`).'),
                    '403' => new Model\Response(description: 'The admin was deactivated meanwhile (`account_inactive`).'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                    '429' => new Model\Response(description: 'Too many wrong codes for this admin (`too_many_login_attempts`); see `Retry-After`.'),
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
