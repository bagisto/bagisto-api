<?php

/**
 * GraphQL Authentication Configuration
 *
 * Defines which GraphQL operations require X-STOREFRONT-KEY authentication.
 * X-STOREFRONT-KEY is the generic, header for all client types:
 * - Mobile apps
 * - Web storefronts
 * - Headless commerce
 * - Admin dashboards
 * - Third-party integrations *
 */

return [
    /**
     * Public GraphQL operations that don't require X-STOREFRONT-KEY header
     *
     * AUTHENTICATION STRATEGY:
     * - X-STOREFRONT-KEY: ALWAYS required (identifies client/app)
     * - Bearer Token: Required only for user-specific operations
     *
     * All operations require X-STOREFRONT-KEY. Then:
     * - Public operations: X-STOREFRONT-KEY only
     * - User operations: X-STOREFRONT-KEY + Bearer token (Sanctum)
     * - Admin operations: X-STOREFRONT-KEY + Admin Bearer token
     *
     * Empty list = All operations require X-STOREFRONT-KEY
     */
    'public_operations' => [
        '__schema',
        '__type',
    ],

    /**
     * Protected operations that require X-STOREFRONT-KEY header
     *
     * Leave this as an empty array to use blacklist approach (recommended).
     * If you list operations here, they WILL require authentication.
     * Unlisted operations with this non-empty array will NOT require auth.
     *
     * BEST PRACTICE: Keep this empty and use public_operations instead
     * This way: protected_operations = everything NOT in public_operations
     */
    'protected_operations' => [
    ],

    /**
     * Enable selective authentication
     *
     * true:  Use whitelist approach (public_operations)
     * false: Use blacklist approach (protected_operations)
     *
     * RECOMMENDED: true (whitelist is more secure)
     */
    'use_whitelist' => true,

    /**
     * Skip authentication for introspection queries
     * Allow GraphQL tools and playground to inspect schema without key
     */
    'allow_introspection' => true,

    /**
     * Detailed logging for authentication
     * Set to 'true' to log all authentication checks
     */
    'log_auth_checks' => env('GRAPHQL_AUTH_LOG', false),

    /**
     * Custom error messages
     */
    'messages' => [
        'missing_key' => 'X-STOREFRONT-KEY header is required for this operation',
        'invalid_key' => 'Invalid or expired API key',
        'rate_limit'  => 'Rate limit exceeded. Please try again later.',
    ],
];
