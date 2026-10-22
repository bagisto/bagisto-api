<?php

namespace Webkul\BagistoApi\OpenApi;

class AdminAuthResponses
{
    /**
     * The example token pair of a login, a two-factor verification or a refresh.
     */
    public const TOKEN_PAIR = [
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
    ];

    /**
     * The example 200 answer of a login without a second factor.
     */
    public const LOGIN = [
        ...self::TOKEN_PAIR,
        'twoFactorRequired' => false,
        'challengeToken' => null,
        'challengeTokenExpiresAt' => null,
        'message' => null,
    ];

    /**
     * The example 202 answer of a login that needs a second factor: every token field is null.
     */
    public const LOGIN_CHALLENGE = [
        'tokenType' => null,
        'accessToken' => null,
        'expiresIn' => null,
        'accessTokenExpiresAt' => null,
        'refreshToken' => null,
        'refreshTokenExpiresAt' => null,
        'session' => null,
        'admin' => null,
        'permissions' => null,
        'twoFactorRequired' => true,
        'challengeToken' => '41|9b1e7c...',
        'challengeTokenExpiresAt' => '2026-10-21T10:05:00+05:30',
        'message' => 'Enter the 6-digit code from your authenticator app, or one of your backup codes, to finish logging in.',
    ];

    /**
     * The schema of the fields a login adds to the token pair.
     */
    public const LOGIN_SCHEMA = [
        'allOf' => [
            ['$ref' => ApiSchemas::ADMIN_TOKEN_PAIR_REF],
            [
                'type' => 'object',

                'properties' => [
                    'twoFactorRequired' => [
                        'type' => 'boolean',
                        'description' => 'True on a `202` answer: no token is returned, send `challengeToken` with the code to `POST /auth/two-factor`.',
                    ],

                    'challengeToken' => [
                        'type' => ['string', 'null'],
                        'description' => 'The single-use challenge token, on a `202` answer only.',
                    ],

                    'challengeTokenExpiresAt' => [
                        'type' => ['string', 'null'],
                        'format' => 'date-time',
                    ],

                    'message' => [
                        'type' => ['string', 'null'],
                        'description' => 'What to do next, on a `202` answer only.',
                    ],
                ],
            ],
        ],
    ];

    /**
     * The header that says how long to wait after a 429.
     */
    public const RETRY_AFTER = [
        'Retry-After' => [
            'description' => 'Seconds to wait before trying again.',
            'schema' => [
                'type' => 'integer',
                'example' => 42,
            ],
        ],
    ];

    /**
     * A wrong email or password.
     */
    public const INVALID_CREDENTIALS = [
        'type' => '/errors/401',
        'title' => 'Unauthorized',
        'status' => 401,
        'detail' => 'Please check your credentials and try again.',
        'message' => 'Please check your credentials and try again.',
        'error' => 'invalid_credentials',
    ];

    /**
     * A login or two-factor verification by an inactive admin.
     */
    public const ACCOUNT_INACTIVE = [
        'type' => '/errors/403',
        'title' => 'Forbidden',
        'status' => 403,
        'detail' => 'Your account is yet to be activated, please contact administrator.',
        'message' => 'Your account is yet to be activated, please contact administrator.',
        'error' => 'account_inactive',
    ];

    /**
     * Too many failed logins or wrong two-factor codes.
     */
    public const TOO_MANY_LOGIN_ATTEMPTS = [
        'type' => '/errors/429',
        'title' => 'Too Many Requests',
        'status' => 429,
        'detail' => 'Too many login attempts. Please try again in 42 seconds.',
        'message' => 'Too many login attempts. Please try again in 42 seconds.',
        'error' => 'too_many_login_attempts',
    ];

    /**
     * Too many forgot or reset password requests from one address.
     */
    public const TOO_MANY_PASSWORD_REQUESTS = [
        'type' => '/errors/429',
        'title' => 'Too Many Requests',
        'status' => 429,
        'detail' => 'Too many password requests. Please try again in 42 seconds.',
        'message' => 'Too many password requests. Please try again in 42 seconds.',
        'error' => 'too_many_password_requests',
    ];

    /**
     * Too many admin API requests in a minute, from the request rate limiter.
     */
    public const RATE_LIMIT_EXCEEDED = [
        'message' => 'Too Many Attempts.',
        'error' => 'rate_limit_exceeded',
    ];

    /**
     * A wrong authenticator or backup code.
     */
    public const INVALID_TWO_FACTOR_CODE = [
        'type' => '/errors/401',
        'title' => 'Unauthorized',
        'status' => 401,
        'detail' => 'Invalid verification code.',
        'message' => 'Invalid verification code.',
        'error' => 'invalid_two_factor_code',
    ];

    /**
     * A two-factor challenge that is unknown, used or expired.
     */
    public const TWO_FACTOR_CHALLENGE_INVALID = [
        'type' => '/errors/401',
        'title' => 'Unauthorized',
        'status' => 401,
        'detail' => 'The two-factor challenge is invalid or has expired. Please log in again.',
        'message' => 'The two-factor challenge is invalid or has expired. Please log in again.',
        'error' => 'two_factor_challenge_invalid',
    ];

    /**
     * A two-factor challenge revoked by its fifth wrong code.
     */
    public const TWO_FACTOR_ATTEMPTS_EXCEEDED = [
        'type' => '/errors/401',
        'title' => 'Unauthorized',
        'status' => 401,
        'detail' => 'Too many wrong codes. Please log in again.',
        'message' => 'Too many wrong codes. Please log in again.',
        'error' => 'two_factor_challenge_invalid',
    ];

    /**
     * A refresh token that is expired, used or revoked, or whose session ended.
     */
    public const REFRESH_TOKEN_INVALID = [
        'type' => '/errors/401',
        'title' => 'Unauthorized',
        'status' => 401,
        'detail' => 'The refresh token is invalid or has expired. Please log in again.',
        'message' => 'The refresh token is invalid or has expired. Please log in again.',
        'error' => 'refresh_token_invalid',
    ];

    /**
     * A request without a valid bearer token, refused before the operation runs.
     */
    public const UNAUTHENTICATED = [
        'message' => 'Unauthenticated.',
        'error' => 'unauthenticated',
    ];

    /**
     * An expired access token, refused before the operation runs.
     */
    public const ACCESS_TOKEN_EXPIRED = [
        'message' => 'The access token has expired. Refresh it to continue.',
        'error' => 'access_token_expired',
    ];

    /**
     * A session operation called with an API token.
     */
    public const ACCESS_TOKEN_REQUIRED = [
        'type' => '/errors/403',
        'title' => 'Forbidden',
        'status' => 403,
        'detail' => 'This action needs the access token of a login session; API tokens cannot use it.',
        'message' => 'This action needs the access token of a login session; API tokens cannot use it.',
        'error' => 'access_token_required',
    ];

    /**
     * A wrong current password on an account update.
     */
    public const INVALID_CURRENT_PASSWORD = [
        'type' => '/errors/422',
        'title' => 'Unprocessable Content',
        'status' => 422,
        'detail' => 'The current password you entered is incorrect.',
        'message' => 'The current password you entered is incorrect.',
        'error' => 'invalid_current_password',
    ];

    /**
     * A password reset token or email that does not match a reset request.
     */
    public const PASSWORD_RESET_TOKEN_INVALID = [
        'type' => '/errors/422',
        'title' => 'Unprocessable Content',
        'status' => 422,
        'detail' => 'This password reset token is invalid or has expired.',
        'message' => 'This password reset token is invalid or has expired.',
        'error' => 'password_reset_token_invalid',
    ];

    /**
     * A body that fails validation: the first message, as a problem document.
     */
    public const VALIDATION_FAILED = [
        'type' => '/errors/422',
        'title' => 'Bad Request',
        'status' => 422,
        'detail' => 'The email field is required.',
    ];

    /**
     * A session id that is not one of the caller's live sessions.
     */
    public const SESSION_NOT_FOUND = [
        'type' => '/errors/404',
        'title' => 'Not Found',
        'status' => 404,
        'detail' => 'Session not found.',
    ];
}
