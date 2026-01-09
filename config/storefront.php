<?php

/**
 * Storefront API Key Configuration
 *
 * Settings for X-STOREFRONT-KEY authentication for shop/storefront APIs
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Rate Limit
    |--------------------------------------------------------------------------
    |
    | Default number of requests allowed per minute for each storefront key.
    | Can be overridden per key in the database.
    |
    */
    'default_rate_limit' => env('STOREFRONT_DEFAULT_RATE_LIMIT', 100),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | Time-to-live for cached key validation results in minutes.
    | Reduces database queries for repeated requests using the same key.
    |
    */
    'cache_ttl' => env('STOREFRONT_CACHE_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix used for cache keys to avoid collisions with other cache entries.
    |
    */
    'key_prefix' => env('STOREFRONT_KEY_PREFIX', 'storefront_key_'),

    /*
    |--------------------------------------------------------------------------
    | Playground API Key
    |--------------------------------------------------------------------------
    |
    | API key used for API documentation and GraphQL playground.
    | Generate a dedicated key and set it in your .env file.
    |
    | Example: STOREFRONT_PLAYGROUND_KEY=pk_storefront_xxx
    |
    */
    'playground_key' => env('STOREFRONT_PLAYGROUND_KEY'),
];
