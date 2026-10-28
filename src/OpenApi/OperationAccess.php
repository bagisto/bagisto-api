<?php

namespace Webkul\BagistoApi\OpenApi;

use ApiPlatform\OpenApi\Model\SecurityScheme;
use Webkul\BagistoApi\Http\Middleware\EnforceAdminApiAuth;

class OperationAccess
{
    /**
     * An operation reachable without a bearer token: the admin sign-in operations, or a storefront key only.
     */
    public const PUBLIC = 'public';

    /**
     * An admin operation that needs a login session's access token and refuses an API token.
     */
    public const ADMIN_SESSION = 'admin_session';

    /**
     * An admin operation that takes an access token or an API token.
     */
    public const ADMIN_TOKEN = 'admin_token';

    /**
     * A storefront operation that needs a customer token.
     */
    public const CUSTOMER = 'customer';

    /**
     * A storefront cart or checkout operation that takes a guest cart token or a customer token.
     */
    public const CART = 'cart';

    /**
     * A storefront operation open to guests that acts as the customer when a customer token is sent.
     */
    public const OPTIONAL_CUSTOMER = 'optional_customer';

    /**
     * The admin paths whose state classes use RequiresAdminSession, so an API token gets 403 access_token_required.
     */
    public const ADMIN_SESSION_PATHS = [
        '/auth/logout',
        '/auth/logout-all',
        '/auth/sessions',
        '/auth/sessions/{id}',
        '/account',
    ];

    /**
     * Storefront operations, as "METHOD path", that take an optional customer token.
     */
    public const SHOP_OPTIONAL_CUSTOMER_OPERATIONS = [
        'POST /cart-tokens',
        'POST /reviews',
    ];

    /**
     * Storefront operations, as "METHOD path", that need a customer token although their path is public.
     */
    public const SHOP_CUSTOMER_OPERATIONS = [
        'PATCH /reviews/{id}',
        'DELETE /reviews/{id}',
    ];

    /**
     * Storefront paths open to guests below a customer path.
     */
    public const SHOP_PUBLIC_PATHS = [
        '/eu-withdrawals/guest',
    ];

    /**
     * Storefront paths, with every path below them, whose state classes resolve the customer from the access token.
     */
    public const SHOP_CUSTOMER_PATHS = [
        '/customer/logout',
        '/customer/logout-all',
        '/customer/sessions',
        '/verify-tokens',
        '/customer-profile',
        '/customer-profile-deletes',
        '/customer-profile-updates',
        '/customer-addresses',
        '/customer-orders',
        '/customer-order-shipments',
        '/customer-order-shipment-items',
        '/customer-invoices',
        '/customer-downloadable-products',
        '/customer-reviews',
        '/cancel-order',
        '/reorder',
        '/returns',
        '/return-messages',
        '/return-reasons',
        '/return-custom-fields',
        '/returnable-items',
        '/returnable-orders',
        '/wishlists',
        '/delete-all-wishlists',
        '/move-wishlist-to-carts',
        '/move-to-wishlists',
        '/merge-carts',
        '/compare_items',
        '/delete-all-compare-items',
        '/gdpr-data',
        '/gdpr-requests',
        '/eu-withdrawals',
    ];

    /**
     * Storefront paths, with every path below them, whose state classes resolve the cart from the bearer token.
     */
    public const SHOP_CART_PATHS = [
        '/cart',
        '/add-product-in-cart',
        '/update-cart-item',
        '/remove-cart-item',
        '/remove-cart-items',
        '/apply-coupon',
        '/remove-coupon',
        '/estimate-shippings',
        '/customizable-option-files',
        '/checkout-addresses',
        '/checkout-shipping-methods',
        '/checkout-payment-methods',
        '/checkout-orders',
        '/checkout-payments',
        '/checkout-messages',
        '/payment-methods',
    ];

    /**
     * The access an admin operation needs: public for the sign-in allowlist, a session or any token otherwise.
     */
    public static function admin(string $path, string $method): string
    {
        if (
            strtoupper($method) === 'POST'
            && in_array('/api/admin'.$path, EnforceAdminApiAuth::PUBLIC_REST_PATHS, true)
        ) {
            return self::PUBLIC;
        }

        return in_array($path, self::ADMIN_SESSION_PATHS, true)
            ? self::ADMIN_SESSION
            : self::ADMIN_TOKEN;
    }

    /**
     * The access a storefront operation needs, by its method and path.
     */
    public static function shop(string $path, string $method): string
    {
        $operation = strtoupper($method).' '.$path;

        if (in_array($operation, self::SHOP_OPTIONAL_CUSTOMER_OPERATIONS, true)) {
            return self::OPTIONAL_CUSTOMER;
        }

        if (in_array($operation, self::SHOP_CUSTOMER_OPERATIONS, true)) {
            return self::CUSTOMER;
        }

        if (self::under($path, self::SHOP_PUBLIC_PATHS)) {
            return self::PUBLIC;
        }

        if (self::under($path, self::SHOP_CUSTOMER_PATHS)) {
            return self::CUSTOMER;
        }

        return self::under($path, self::SHOP_CART_PATHS)
            ? self::CART
            : self::PUBLIC;
    }

    /**
     * The OpenAPI security requirements of an access level; alternatives are separate entries.
     */
    public static function security(string $surface, string $access): array
    {
        if ($surface === 'admin') {
            return match ($access) {
                self::PUBLIC => [],
                self::ADMIN_SESSION => [['adminAccessToken' => []]],
                default => [['adminAccessToken' => []], ['adminApiToken' => []]],
            };
        }

        return match ($access) {
            self::CUSTOMER => [['storefrontKey' => [], 'customerToken' => []]],
            self::CART => [
                ['storefrontKey' => [], 'customerToken' => []],
                ['storefrontKey' => [], 'guestCartToken' => []],
            ],
            self::OPTIONAL_CUSTOMER => [
                ['storefrontKey' => []],
                ['storefrontKey' => [], 'customerToken' => []],
            ],
            default => [['storefrontKey' => []]],
        };
    }

    /**
     * The security requirement of the whole document, which every operation then states for itself.
     */
    public static function documentSecurity(string $surface): array
    {
        return self::security($surface, $surface === 'admin' ? self::ADMIN_TOKEN : self::PUBLIC);
    }

    /**
     * The security schemes of a surface, keyed by name.
     *
     * @return array<string, SecurityScheme>
     */
    public static function securitySchemes(string $surface): array
    {
        if ($surface === 'admin') {
            return [
                'adminAccessToken' => new SecurityScheme(
                    type: 'http',
                    description: 'A JWT access token for apps that sign in, returned by `POST /auth/login`, by `POST /auth/two-factor` after a `202` challenge and by every `POST /auth/refresh`. Send it as `Authorization: Bearer <accessToken>`. It lasts 15 minutes by default (`expiresIn`, `accessTokenExpiresAt`): refresh it before then, or when a request answers `401` with `access_token_expired`, with the refresh token, which lasts 30 days by default and works once. Both lifetimes and the absolute session lifetime (90 days by default) are configurable in the admin panel under Configuration → API → Admin Authentication.',
                    scheme: 'bearer',
                    bearerFormat: 'JWT',
                ),

                'adminApiToken' => new SecurityScheme(
                    type: 'http',
                    description: 'A JWT API token for server integrations, created in the admin panel under Settings → API Tokens or with `php artisan bagisto-api:api-token:create`, never through the API, and shown once. Send it as `Authorization: Bearer <token>`. It acts as one admin user with that admin\'s role permissions and works only from the domain it is bound to (`403 api_token_domain_mismatch` otherwise). It has no refresh, and lasts until its expiry date or, when *Allow API Tokens Without Expiry* is on (the default), until it is revoked. It cannot call the session operations (`403 access_token_required`).',
                    scheme: 'bearer',
                    bearerFormat: 'JWT',
                ),
            ];
        }

        return [
            'storefrontKey' => new SecurityScheme(
                type: 'apiKey',
                description: 'The storefront API key, required on every storefront request. Create one with `php artisan bagisto-api:generate-key` and manage keys with `php artisan bagisto-api:key:manage`. A missing key is answered with `401 missing_key`, an invalid or inactive key, or one used from an IP address it does not allow, with `403 invalid_key`, and a key over its rate limit with `429 rate_limit_exceeded`.',
                name: 'X-STOREFRONT-KEY',
                in: 'header',
            ),

            'customerToken' => new SecurityScheme(
                type: 'http',
                description: 'A customer access token: an ES256 JWT returned as `accessToken` by `POST /customer/login`, `POST /customers` and `POST /customers/social-login`, and by every `POST /customer/refresh`. Send it as `Authorization: Bearer <accessToken>`. It lasts 15 minutes by default (`expiresIn`): refresh it before then, or when a request answers `401` with `access_token_expired`, with the refresh token, which lasts 30 days by default and works once. It is tied to the channel it was issued on. The lifetimes are set under Configuration → API → Customer Authentication.',
                scheme: 'bearer',
                bearerFormat: 'JWT',
            ),

            'guestCartToken' => new SecurityScheme(
                type: 'http',
                description: 'A guest cart token, returned as `cartToken` by `POST /cart-tokens` called without a customer token. Send it as `Authorization: Bearer <cartToken>` on the cart and checkout operations, in place of a customer token, to work on the guest\'s cart.',
                scheme: 'bearer',
            ),
        ];
    }

    /**
     * Whether a path is one of the given paths or below one of them.
     */
    protected static function under(string $path, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (
                $path === $prefix
                || str_starts_with($path, $prefix.'/')
            ) {
                return true;
            }
        }

        return false;
    }
}
