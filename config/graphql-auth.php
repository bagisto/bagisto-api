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
     * Skip authentication for introspection queries
     * Allow GraphQL tools and playground to inspect schema without key
     */
    'allow_introspection' => true,

    /**
     * Detailed logging for authentication
     * Set to 'true' to log all authentication checks
     */
    'log_auth_checks' => env('GRAPHQL_AUTH_LOG', false),
];
