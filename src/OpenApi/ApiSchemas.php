<?php

namespace Webkul\BagistoApi\OpenApi;

use Webkul\BagistoApi\Exception\AdminAuthException;

class ApiSchemas
{
    /**
     * The reference to the admin authentication error schema.
     */
    public const AUTH_ERROR_REF = '#/components/schemas/AuthError';

    /**
     * The reference to the problem document schema of every other error.
     */
    public const PROBLEM_REF = '#/components/schemas/ApiProblem';

    /**
     * The reference to the token pair a login, two-factor verification or refresh returns.
     */
    public const ADMIN_TOKEN_PAIR_REF = '#/components/schemas/AdminTokenPair';

    /**
     * The reference to the storefront key error schema.
     */
    public const STOREFRONT_KEY_ERROR_REF = '#/components/schemas/StorefrontKeyError';

    /**
     * The problem document every error outside admin authentication and the storefront key is answered with.
     */
    public const PROBLEM = [
        'type' => 'object',
        'description' => 'An error as a problem document. `detail` holds the translated message.',
        'required' => ['type', 'title', 'status', 'detail'],

        'properties' => [
            'type' => [
                'type' => 'string',
                'description' => '`/errors/` followed by the HTTP status.',
                'example' => '/errors/404',
            ],

            'title' => [
                'type' => 'string',
                'example' => 'Not Found',
            ],

            'status' => [
                'type' => 'integer',
                'example' => 404,
            ],

            'detail' => [
                'type' => 'string',
                'description' => 'The translated message, naming what failed.',
            ],
        ],
    ];

    /**
     * The admin authentication error, with its machine-readable code in `error`.
     */
    public const AUTH_ERROR = [
        'type' => 'object',
        'description' => 'An admin authentication error. `error` is the machine-readable code to branch on and `message` the translated text; over GraphQL the code is in `extensions.code`. The bearer token check answers with `message` and `error` only; an error raised while the operation runs, such as a wrong password or refresh token, also carries the problem fields `type`, `title`, `status` and `detail`.',
        'required' => ['message', 'error'],

        'properties' => [
            'message' => [
                'type' => 'string',
                'example' => 'The access token has expired. Refresh it to continue.',
            ],

            'error' => [
                'type' => 'string',
                'example' => AdminAuthException::CODE_ACCESS_TOKEN_EXPIRED,

                'enum' => [
                    AdminAuthException::CODE_UNAUTHENTICATED,
                    AdminAuthException::CODE_ACCESS_TOKEN_EXPIRED,
                    AdminAuthException::CODE_REFRESH_TOKEN_INVALID,
                    AdminAuthException::CODE_API_TOKEN_EXPIRED,
                    AdminAuthException::CODE_API_TOKEN_REVOKED,
                    AdminAuthException::CODE_API_TOKEN_DOMAIN_MISMATCH,
                    AdminAuthException::CODE_ACCESS_TOKEN_REQUIRED,
                    AdminAuthException::CODE_INVALID_CREDENTIALS,
                    AdminAuthException::CODE_ACCOUNT_INACTIVE,
                    AdminAuthException::CODE_INVALID_TWO_FACTOR_CODE,
                    AdminAuthException::CODE_TWO_FACTOR_CHALLENGE_INVALID,
                    AdminAuthException::CODE_INVALID_CURRENT_PASSWORD,
                    AdminAuthException::CODE_PASSWORD_RESET_TOKEN_INVALID,
                    AdminAuthException::CODE_TOO_MANY_PASSWORD_REQUESTS,
                    AdminAuthException::CODE_TOO_MANY_LOGIN_ATTEMPTS,
                    'rate_limit_exceeded',
                ],
            ],

            'type' => [
                'type' => 'string',
                'example' => '/errors/401',
            ],

            'title' => [
                'type' => 'string',
                'example' => 'Unauthorized',
            ],

            'status' => [
                'type' => 'integer',
                'example' => 401,
            ],

            'detail' => [
                'type' => 'string',
                'example' => 'Please check your credentials and try again.',
            ],
        ],
    ];

    /**
     * The error the storefront key check answers a missing, invalid or rate-limited key with.
     */
    public const STOREFRONT_KEY_ERROR = [
        'type' => 'object',
        'description' => 'A storefront key error. `error` is `missing_key` (401), `invalid_key` (403) or `rate_limit_exceeded` (429).',
        'required' => ['message', 'error'],

        'properties' => [
            'message' => [
                'type' => 'string',
                'example' => 'X-STOREFRONT-KEY header is required',
            ],

            'error' => [
                'type' => 'string',
                'enum' => ['missing_key', 'invalid_key', 'rate_limit_exceeded'],
                'example' => 'missing_key',
            ],

            'header_name' => [
                'type' => 'string',
                'description' => 'The header to send; only with `missing_key`.',
                'example' => 'X-STOREFRONT-KEY',
            ],

            'key_type' => [
                'type' => 'string',
                'description' => 'The kind of key expected; only with `missing_key`.',
                'example' => 'shop',
            ],

            'retry_after' => [
                'type' => 'integer',
                'description' => 'Seconds to wait; only with `rate_limit_exceeded`.',
                'example' => 60,
            ],
        ],
    ];

    /**
     * The token pair a login, a two-factor verification and a refresh return.
     */
    public const ADMIN_TOKEN_PAIR = [
        'type' => 'object',
        'description' => 'A JWT access token and an opaque refresh token for one session. Send the access token as `Authorization: Bearer <accessToken>`; send the refresh token only to `POST /auth/refresh`.',

        'properties' => [
            'tokenType' => [
                'type' => 'string',
                'enum' => ['Bearer'],
                'example' => 'Bearer',
            ],

            'accessToken' => [
                'type' => 'string',
                'description' => 'The ES256-signed JWT access token.',
                'example' => 'eyJhbGciOiJFUzI1NiIsImtpZCI6Ii4uLiIsInR5cCI6IkpXVCJ9.eyJ...',
            ],

            'expiresIn' => [
                'type' => 'integer',
                'description' => 'Seconds until the access token expires (900 by default).',
                'example' => 900,
            ],

            'accessTokenExpiresAt' => [
                'type' => 'string',
                'format' => 'date-time',
                'example' => '2026-10-21T10:15:00+05:30',
            ],

            'refreshToken' => [
                'type' => 'string',
                'description' => 'The refresh token; it works once.',
                'example' => '77|3f9c0d...',
            ],

            'refreshTokenExpiresAt' => [
                'type' => 'string',
                'format' => 'date-time',
                'example' => '2026-11-20T10:00:00+05:30',
            ],

            'session' => [
                'type' => 'object',

                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'example' => 12,
                    ],

                    'name' => [
                        'type' => ['string', 'null'],
                        'example' => 'iPhone',
                    ],

                    'clientType' => [
                        'type' => ['string', 'null'],
                        'example' => 'mobile',
                    ],

                    'expiresAt' => [
                        'type' => ['string', 'null'],
                        'format' => 'date-time',
                        'description' => 'The absolute end of the session, however often it is refreshed.',
                        'example' => '2027-01-19T10:00:00+05:30',
                    ],
                ],
            ],

            'admin' => [
                'type' => 'object',

                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'example' => 3,
                    ],

                    'name' => [
                        'type' => 'string',
                        'example' => 'Example Admin',
                    ],

                    'email' => [
                        'type' => 'string',
                        'example' => 'admin@example.com',
                    ],

                    'role' => [
                        'type' => ['object', 'null'],

                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],

                            'name' => [
                                'type' => 'string',
                                'example' => 'Administrator',
                            ],

                            'permissionType' => [
                                'type' => 'string',
                                'enum' => ['all', 'custom'],
                                'example' => 'all',
                            ],
                        ],
                    ],
                ],
            ],

            'permissions' => [
                'type' => 'array',
                'description' => 'The ACL keys the role grants, or `["*"]` for every permission.',
                'items' => ['type' => 'string'],
                'example' => ['*'],
            ],
        ],
    ];

    /**
     * JSON content of one schema reference, or of one of several references or inline schemas, with any named examples.
     *
     * @param  string|list<string|array>  $refs
     * @param  array<string, array>  $examples
     */
    public static function content(string|array $refs, array $examples): \ArrayObject
    {
        $schema = is_array($refs)
            ? ['oneOf' => array_map(fn (string|array $ref) => is_array($ref) ? $ref : ['$ref' => $ref], $refs)]
            : ['$ref' => $refs];

        $mediaType = ['schema' => $schema];

        if ($examples !== []) {
            $mediaType['examples'] = array_map(fn (array $value) => ['value' => $value], $examples);
        }

        return new \ArrayObject(['application/json' => $mediaType]);
    }

    /**
     * The body of a problem document.
     */
    public static function problem(int $status, string $title, string $detail): array
    {
        return [
            'type' => '/errors/'.$status,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
        ];
    }
}
