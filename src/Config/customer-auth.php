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
     * Live sessions a customer may hold (0 to 100); 0 means no limit, otherwise the oldest is ended.
     */
    'max_sessions' => 0,

    /**
     * Failed logins an email and client address may make before the lockout (3 to 20).
     */
    'login_max_attempts' => 5,

    /**
     * Seconds failed logins are remembered for, and so how long a lockout lasts (60 to 3600).
     */
    'login_lockout_seconds' => 60,

    /**
     * Token refreshes a session may make in a minute (1 to 60).
     */
    'refresh_requests_per_minute' => 10,

    /**
     * Requests per minute allowed for each signed-in customer (1 to 100000).
     */
    'requests_per_minute' => 120,

    /**
     * Whether a password change ends the customer's other sessions.
     */
    'revoke_sessions_on_password_change' => true,

    /**
     * Days a revoked or expired session is kept before the prune command deletes it (1 to 365).
     */
    'prune_after_days' => 30,

    /**
     * The JWT settings; tokens are signed with the same keys as the admin API's.
     */
    'jwt' => [
        'audience' => 'bagisto-shop-api',
    ],
];
