<?php

return [
    /**
     * Access token lifetime in minutes (5 to 60), used when the admin configuration has none.
     */
    'access_token_ttl' => 15,

    /**
     * Refresh token lifetime in days (1 to 90), used when the admin configuration has none.
     */
    'refresh_token_ttl' => 30,

    /**
     * Absolute session lifetime in days (1 to 365), however often the session is refreshed.
     */
    'session_lifetime' => 90,

    /**
     * Requests per minute allowed for each signed-in admin (1 to 100000).
     */
    'session_requests_per_minute' => 60,

    /**
     * Requests per minute allowed for each API token (1 to 100000).
     */
    'api_token_requests_per_minute' => 60,

    /**
     * Requests per minute allowed without a token, for each client address (10 to 1000).
     */
    'unauthenticated_requests_per_minute' => 60,

    /**
     * Failed logins an email and client address may make before the lockout (3 to 20).
     */
    'login_max_attempts' => 5,

    /**
     * Seconds failed logins are remembered for, and so how long a lockout lasts (60 to 3600).
     */
    'login_lockout_seconds' => 60,

    /**
     * Minutes a two-factor challenge stays usable (1 to 15).
     */
    'two_factor_challenge_lifetime' => 5,

    /**
     * Wrong two-factor codes an admin may enter in a minute, across all challenges (3 to 20).
     */
    'two_factor_max_failures' => 5,

    /**
     * Forgot and reset password requests a client address may make in a minute (1 to 20).
     */
    'password_requests_per_minute' => 5,

    /**
     * Whether a password change revokes the admin's sessions.
     */
    'revoke_sessions_on_password_change' => true,

    /**
     * Whether a password change revokes the admin's API tokens.
     */
    'revoke_api_tokens_on_password_change' => false,

    /**
     * Whether an API token may be created without an expiry date.
     */
    'allow_non_expiring_api_tokens' => true,

    /**
     * How an API token's domain is enforced: "dns" (client IP) or "origin" (Origin or Referer host).
     */
    'domain_binding_mode' => 'dns',

    /**
     * Seconds a domain's resolved addresses are cached for.
     */
    'domain_dns_cache_seconds' => 300,

    /**
     * Days a revoked or expired session is kept before the prune command deletes it (1 to 365).
     */
    'prune_after_days' => 30,

    /**
     * The JWT signing settings.
     */
    'jwt' => [
        'audience' => 'bagisto-admin-api',

        'leeway' => 30,

        'path' => storage_path('app/private/bagisto-api/jwt'),

        'private_key' => env('BAGISTO_API_JWT_PRIVATE_KEY'),

        'public_key' => env('BAGISTO_API_JWT_PUBLIC_KEY'),

        'kid' => env('BAGISTO_API_JWT_KID'),

        'previous_public_keys' => json_decode((string) env('BAGISTO_API_JWT_PREVIOUS_PUBLIC_KEYS', '{}'), true) ?: [],
    ],
];
