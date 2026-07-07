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

    /*
    |--------------------------------------------------------------------------
    | Cart Customizable File Upload
    |--------------------------------------------------------------------------
    |
    | Staging settings for file-type customizable options added to the cart.
    | The upload endpoint stages the file and returns a token; add-to-cart
    | resolves the token. A persistent cache driver is required.
    |
    */
    'cart' => [
        'customizable_file' => [
            'max_size_kb' => (int) env('STOREFRONT_CART_FILE_MAX_KB', 2048),
            'ttl_minutes' => (int) env('STOREFRONT_CART_FILE_TTL', 60),
            'disk'        => env('STOREFRONT_CART_FILE_DISK', 'private'),
            'stage_dir'   => 'cart-uploads',
        ],
    ],
];
