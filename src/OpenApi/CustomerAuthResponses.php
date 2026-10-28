<?php

namespace Webkul\BagistoApi\OpenApi;

class CustomerAuthResponses
{
    /**
     * A customer token pair, as login, registration, social login and refresh return it.
     */
    public const TOKEN_PAIR = [
        'tokenType' => 'Bearer',
        'accessToken' => 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6Ii4uLiJ9.eyJ0eXAiOiJjdXN0b21lcl9hY2Nlc3MiLCJzdWIiOjE1Mjl9.c2lnbmF0dXJl',
        'expiresIn' => 900,
        'accessTokenExpiresAt' => '2026-10-23T10:15:00+00:00',
        'refreshToken' => '88|7c1f4e2a9b...',
        'refreshTokenExpiresAt' => '2026-11-22T10:00:00+00:00',
        'session' => [
            'id' => 41,
            'name' => 'iPhone 15',
            'clientType' => 'mobile',
            'expiresAt' => '2027-01-21T10:00:00+00:00',
        ],
        'customer' => [
            'id' => 1529,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
        ],
    ];

    /**
     * Wrong email or password.
     */
    public const INVALID_CREDENTIALS = [
        'message' => 'Invalid email or password',
        'error' => 'invalid_credentials',
    ];

    /**
     * An inactive customer.
     */
    public const ACCOUNT_INACTIVE = [
        'message' => 'Your account is not activated. Please contact support.',
        'error' => 'account_inactive',
    ];

    /**
     * A customer who has not verified their email address.
     */
    public const ACCOUNT_UNVERIFIED = [
        'message' => 'Please verify your email address before logging in.',
        'error' => 'account_unverified',
    ];

    /**
     * Too many failed logins.
     */
    public const TOO_MANY_LOGIN_ATTEMPTS = [
        'message' => 'Too many login attempts. Please try again in 60 seconds.',
        'error' => 'too_many_login_attempts',
    ];

    /**
     * A refresh token that cannot be exchanged.
     */
    public const REFRESH_TOKEN_INVALID = [
        'message' => 'The refresh token is invalid or has expired. Please log in again.',
        'error' => 'refresh_token_invalid',
    ];

    /**
     * Too many token refreshes in one session.
     */
    public const TOO_MANY_REFRESH_REQUESTS = [
        'message' => 'Too many token refreshes. Please try again in 42 seconds.',
        'error' => 'too_many_refresh_requests',
    ];

    /**
     * A missing, invalid or revoked customer access token.
     */
    public const UNAUTHENTICATED = [
        'message' => 'Invalid or expired token',
        'error' => 'unauthenticated',
    ];

    /**
     * The Retry-After header of a 429 answer.
     */
    public const RETRY_AFTER = [
        'Retry-After' => [
            'description' => 'Seconds to wait before trying again.',
            'schema' => ['type' => 'integer'],
        ],
    ];
}
