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
     * Requests per minute allowed for each signed-in admin.
     */
    'session_requests_per_minute' => 60,

    /**
     * Requests per minute allowed for each API token.
     */
    'api_token_requests_per_minute' => 60,

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
     * Days a revoked or expired session is kept before the prune command deletes it.
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
