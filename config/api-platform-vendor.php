<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use ApiPlatform\Metadata\Operation\DashPathSegmentNameGenerator;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\Serializer\NameConverter\SnakeCaseToCamelCaseNameConverter;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ValidationException;

return [
    'title'       => '',
    'description' => '',
    'version'     => '1.0.0',
    'show_webby'  => true,

    'routes' => [
        'domain' => null,
        // Global middleware applied to every API Platform routes
        // HandleInvalidInputException: Catches validation errors and returns RFC 7807 format
        // VerifyStorefrontKey: Validates X-STOREFRONT-KEY header and rate limiting for shop APIs
        // BagistoApiDocumentationMiddleware: Handles custom /api index and documentation pages
        // ForceApiJson: Ensures API responses have JSON content-type
        // CacheResponse: Using custom ApiAwareResponseCache profile that:
        // - Excludes API routes from caching (APIs need fresh data)
        // - Caches shop pages for performance
        // - Only caches HTML, not JSON responses
        'middleware' => [
            'Webkul\BagistoApi\Http\Middleware\HandleInvalidInputException',
            'Webkul\BagistoApi\Http\Middleware\SecurityHeaders',
            'Webkul\BagistoApi\Http\Middleware\LogApiRequests',
            'Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey',
            'Webkul\BagistoApi\Http\Middleware\BagistoApiDocumentationMiddleware',
            'Webkul\BagistoApi\Http\Middleware\ForceApiJson',
            'Spatie\ResponseCache\Middlewares\CacheResponse',
        ],
    ],

    'resources' => [
        base_path('vendor/bagisto/bagisto-api/src/Models/')
    ],

    'formats' => [
        'json'=> ['application/json'],
    ],

    'patch_formats' => [
        'json' => ['application/merge-patch+json'],
    ],

    'docs_formats' => [
        'jsonopenapi' => ['application/vnd.openapi+json'],
        'html'        => ['text/html'],
    ],

    'error_formats' => [
        'jsonproblem' => ['application/problem+json'],
    ],

    'defaults' => [
        'pagination_enabled'                => true,
        'pagination_partial'                => false,
        'pagination_client_enabled'         => false,
        'pagination_client_items_per_page'  => false,
        'pagination_client_partial'         => false,
        'pagination_items_per_page'         => 10,
        'pagination_maximum_items_per_page' => 50,
        'route_prefix'                      => '/api',
        'middleware'                        => [],
    ],

    'pagination' => [
        'page_parameter_name'           => 'page',
        'enabled_parameter_name'        => 'pagination',
        'items_per_page_parameter_name' => 'itemsPerPage',
        'partial_parameter_name'        => 'partial',
    ],

    'graphql' => [
        'enabled'              => true,
        'nesting_separator'    => '__',
        'introspection'        => ['enabled' => true],
        'max_query_complexity' => 400,
        'max_query_depth'      => 20,
        'graphiql'             => [
            'enabled'           => true,
            'default_query'     => null,
            'default_variables' => null,
        ],
        'graphql_playground' => [
            'enabled'           => true,
            'default_query'     => null,
            'default_variables' => null,
        ],
        // GraphQL middleware for authentication and rate limiting
        'middleware' => [
            'Webkul\BagistoApi\Http\Middleware\VerifyGraphQLStorefrontKey',
        ],
    ],

    'graphiql' => [
        'enabled' => true,
    ],

    'name_converter' => SnakeCaseToCamelCaseNameConverter::class,

    'path_segment_name_generator' => DashPathSegmentNameGenerator::class,

    'exception_to_status' => [
        AuthenticationException::class => 401,
        AuthorizationException::class  => 403,
        ValidationException::class     => 400,
        InvalidInputException::class   => 400,
    ],

    'swagger_ui' => [
        'enabled' => true,
        'apiKeys' => [
            'api' => [
                'name'   => 'Authorization',
                'type'   => 'header',
                'scheme' => 'bearer',
            ],
        ],
    ],

    'url_generation_strategy' => UrlGeneratorInterface::ABS_PATH,

    'serializer' => [
        'hydra_prefix'    => false,
        'datetime_format' => 'Y-m-d\TH:i:sP',
    ],

    'cache' => 'redis',

    'schema_cache' => [
        'enabled' => true,
        'store'   => 'redis',
    ],

    'security' => [
        'sanctum' => true,
    ],

    'rate_limit' => [
        'skip_localhost' => env('RATE_LIMIT_SKIP_LOCALHOST', true),
        'auth'           => env('RATE_LIMIT_AUTH', 5),
        'admin'          => env('RATE_LIMIT_ADMIN', 60),
        'shop'           => env('RATE_LIMIT_SHOP', 100),
        'graphql'        => env('RATE_LIMIT_GRAPHQL', 100),
        'cache_driver'   => env('RATE_LIMIT_CACHE', 'redis'),
        'cache_prefix'   => 'api:rate-limit:',
    ],

    'security_headers' => [
        'enabled'     => true,
        'force_https' => env('APP_FORCE_HTTPS', false),
        'csp_header'  => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'",
    ],

    'api_logging' => [
        'enabled'            => env('API_LOG_ENABLED', true),
        'log_sensitive_data' => env('API_LOG_SENSITIVE_DATA', false),
        'exclude_paths'      => ['docs', 'graphiql', 'swagger-ui', 'docs.json'],
        'channel'            => 'api',
        'async'              => env('API_LOG_ASYNC', true),
        'queue'              => env('API_LOG_QUEUE', 'api-logs'),
    ],

    'graphql_validation' => [
        'max_depth'      => env('GRAPHQL_MAX_DEPTH', 10),
        'max_complexity' => env('GRAPHQL_MAX_COMPLEXITY', 300),
    ],

    'request_limits' => [
        'max_size_mb'          => env('MAX_REQUEST_SIZE', 10),
        'max_pagination_limit' => env('MAX_PAGINATION_LIMIT', 100),
    ],

    'database' => [
        'log_queries'          => env('DB_QUERY_LOG_ENABLED', false),
        'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 1000),
    ],

    'caching' => [
        'enable_security_cache' => env('API_SECURITY_CACHE', true),
        'security_cache_ttl'    => env('API_SECURITY_CACHE_TTL', 3600),
        'enable_response_cache' => env('API_RESPONSE_CACHE', true),
        'response_cache_ttl'    => env('API_RESPONSE_CACHE_TTL', 3600),
    ],

    'http_cache' => [
        'etag'                   => true,
        'max_age'                => 3600,
        'shared_max_age'         => null,
        'vary'                   => null,
        'public'                 => true,
        'stale_while_revalidate' => 30,
        'stale_if_error'         => null,
        'invalidation'           => [
            'urls'              => [],
            'scoped_clients'    => [],
            'max_header_length' => 7500,
            'request_options'   => [],
            'purger'            => ApiPlatform\HttpCache\SouinPurger::class,
        ],
    ],

    'key_rotation_policy' => [
        'enabled'               => true,
        'expiration_months'     => env('API_KEY_EXPIRATION_MONTHS', 12),
        'transition_days'       => env('API_KEY_TRANSITION_DAYS', 7),
        'cleanup_days'          => env('API_KEY_CLEANUP_DAYS', 90),
        'cache_ttl'             => env('API_KEY_CACHE_TTL', 3600),
        'storefront_key_prefix' => env('STOREFRONT_KEY_PREFIX', 'pk_storefront_'),
    ],

    'storefront_key'      => env('STOREFRONT_PLAYGROUND_KEY', 'pk_storefront_xxxxx'),
    'auto_inject_key'     => env('API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY', 'true'),
];