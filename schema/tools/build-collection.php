<?php

declare(strict_types=1);

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\BuildSchema;

$autoload = null;

foreach (['/../../../../../vendor/autoload.php', '/../../vendor/autoload.php'] as $candidate) {
    if (file_exists(__DIR__.$candidate)) {
        $autoload = __DIR__.$candidate;

        break;
    }
}

if ($autoload === null) {
    fwrite(STDERR, "Could not locate vendor/autoload.php. Run composer install first.\n");

    exit(1);
}

require_once $autoload;

final class CollectionBuilder
{
    /**
     * The directory holding the exported OpenAPI and GraphQL schemas.
     */
    private const SCHEMA_DIR = __DIR__.'/../generated';

    /**
     * How deep a generated GraphQL selection set goes.
     */
    private const MAX_SELECTION_DEPTH = 1;

    /**
     * The environment variable each documented request header is filled from.
     */
    private const HEADER_VARS = [
        'X-STOREFRONT-KEY' => '{{storefrontKey}}',
        'X-Locale' => '{{locale}}',
        'X-Channel' => '{{channel}}',
        'X-Currency' => '{{currency}}',
    ];

    /**
     * Tags renamed to the folder they belong in, per surface.
     */
    private const TAG_ALIASES = [
        'shop' => [
            'AddUpdateCustomerAddress' => 'Customer Address',
            'CustomerAddressToken' => 'Customer Address',
            'DeleteCustomerAddress' => 'Customer Address',
            'GetCustomerAddress' => 'Customer Address',
            'GetCustomerAddresses' => 'Customer Address',
            'CategoryAttributeFilter' => 'Category',
            'DefaultChannel' => 'Channel',
            'DownloadableProductDownloadLink' => 'Customer Order',
            'EstimateShipping' => 'Checkout',
            'MutationCheckoutAddress' => 'Checkout',
            'ShippingRates' => 'Checkout',
            'GetCartToken' => 'Cart',
            'GdprDataPdf' => 'GDPR',
            'MoveToWishlist' => 'Wishlist',
            'NewsletterUnsubscription' => 'Newsletter',
            'Customer Authentication' => 'Customer Auth',
        ],

        'admin' => [
            'Admin Authentication' => 'Auth',
        ],
    ];

    /**
     * The collection written for each surface.
     */
    private const SURFACES = [
        'shop' => [
            'id' => 'e7c1a940-6b2d-4f18-9a35-1d8c05b7e240',
            'name' => 'Bagisto Shop API',
            'file' => 'Bagisto-Shop-API.postman_collection.json',
            'token' => '{{customerAccessToken}}',
            'graphql' => '{{url}}/api/graphql',
        ],

        'admin' => [
            'id' => 'a3d5f218-4c60-4b9e-8f71-2b6e93c4a017',
            'name' => 'Bagisto Admin API',
            'file' => 'Bagisto-Admin-API.postman_collection.json',
            'token' => '{{adminAccessToken}}',
            'graphql' => '{{url}}/api/admin/graphql',
        ],
    ];

    /**
     * The admin REST paths that exchange credentials for tokens, which the refresh script leaves alone.
     */
    private const ADMIN_TOKEN_PATHS = [
        '/auth/login',
        '/auth/two-factor',
        '/auth/refresh',
        '/auth/forgot-password',
        '/auth/reset-password',
    ];

    /**
     * The storefront REST paths that exchange credentials for tokens, which the refresh script leaves alone.
     */
    private const CUSTOMER_TOKEN_PATHS = [
        '/customer/login',
        '/customer/refresh',
        '/customers',
        '/customers/social-login',
        '/forgot-passwords',
        '/reset-passwords',
    ];

    /**
     * The sign-in requests, REST as "METHOD path" and GraphQL by field, in the order a client uses them.
     */
    private const CLIENT_ORDER = [
        'POST /auth/login',
        'POST /auth/two-factor',
        'POST /auth/refresh',
        'GET /get',
        'GET /auth/sessions',
        'DELETE /auth/sessions/:id',
        'POST /auth/logout',
        'POST /auth/logout-all',
        'POST /auth/forgot-password',
        'POST /auth/reset-password',
        'PUT /account',
        'readAdminProfile',
        'adminSessions',
        'createAdminLogin',
        'createAdminTwoFactorVerify',
        'createAdminTokenRefresh',
        'deleteAdminSession',
        'createAdminLogout',
        'createAdminLogoutAll',
        'createAdminForgotPassword',
        'createAdminResetPassword',
        'createAdminAccountUpdate',
        'POST /customers',
        'POST /customer/login',
        'POST /customers/social-login',
        'POST /customer/refresh',
        'POST /verify-tokens',
        'GET /customer/sessions',
        'DELETE /customer/sessions/:id',
        'POST /customer/logout',
        'POST /customer/logout-all',
        'POST /forgot-passwords',
        'POST /reset-passwords',
        'POST /verification-emails',
        'POST /verify-accounts',
        'createCustomer',
        'createCustomerLogin',
        'createSocialLogin',
        'createCustomerTokenRefresh',
        'createVerifyToken',
        'customerSessions',
        'deleteCustomerSession',
        'createLogout',
        'createCustomerLogoutAll',
        'createForgotPassword',
        'createResetPassword',
        'createResendVerificationEmail',
        'createVerifyAccount',
    ];

    /**
     * The directory the collections are written to.
     */
    private string $outputDir;

    /**
     * Create a builder writing to the given directory, or to the package's own collections directory.
     */
    public function __construct(?string $outputDir = null)
    {
        $outputDir ??= __DIR__.'/../../collections';

        $this->outputDir = rtrim($outputDir, '/');

        if (! is_dir($this->outputDir)) {
            fwrite(STDERR, "Output directory does not exist: {$this->outputDir}\n");

            exit(1);
        }
    }

    /**
     * Write the collection of every surface.
     */
    public function build(): void
    {
        foreach (self::SURFACES as $surface => $config) {
            $spec = $this->loadSpec($surface);

            $items = $this->restFolder($spec, $surface);

            $items[] = $this->graphQlFolder($surface, $config['graphql']);

            $collection = [
                'info' => [
                    '_postman_id' => $config['id'],
                    'name' => $config['name'],
                    'description' => $this->collectionDescription($surface),
                    'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
                ],

                'auth' => $this->bearer($config['token']),

                'variable' => $this->collectionVariables($surface, $spec),

                'item' => $items,
            ];

            if ($events = $this->collectionEvents($surface)) {
                $collection['event'] = $events;
            }

            $path = $this->outputDir.'/'.$config['file'];

            file_put_contents($path, json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");

            printf("Wrote %s (%d REST requests, %d GraphQL requests)\n", $config['file'], $this->countRequests($items[0] ?? []), $this->countRequests(end($items)));
        }
    }

    /**
     * The overview page of a surface's collection.
     */
    private function collectionDescription(string $surface): string
    {
        if ($surface === 'shop') {
            return <<<'MD'
Storefront endpoints for the [Bagisto](https://bagisto.com) e-commerce platform — products, cart, checkout, orders, returns, reviews, wishlist and more. Both transports are covered: `REST/` and `GraphQL/`.

## Setup

1. Select the **Bagisto** environment (top right). Import it first if it is not in your workspace. One environment serves both the storefront and the admin collection.
2. Fill in these values:

| Variable | Where it comes from |
| --- | --- |
| `url` | Your store, e.g. `http://localhost:8000` |
| `storefrontKey` | `STOREFRONT_PLAYGROUND_KEY` in `.env` (written by the installer), or `php artisan bagisto-api:generate-key` |
| `customerEmail` / `customerPassword` | Any storefront customer account |

3. Run **REST → Customer Auth → Customer login**. It saves `customerAccessToken`, `customerRefreshToken` and `customerAccessTokenExpiresAt` to the environment, and every other request sends the access token as the bearer. Registration and social login save the pair the same way.

The access token is a JWT that lasts 15 minutes by default. Before each request the collection renews it through `POST /api/shop/customer/refresh` when it expires within 60 seconds, so you only log in again once the refresh token (30 days by default) or the session (90 days) ends. A refresh token works once: presenting a used one ends the whole session. A refused login is an HTTP error with a code: `401 invalid_credentials`, `403 account_inactive` or `account_unverified`, `429 too_many_login_attempts`. **Customer Auth** lists its requests in the order a client uses them. The full authentication guide is [docs/authentication.md](https://github.com/bagisto/bagisto-api/blob/main/docs/authentication.md).

## Shopping as a guest

Guest carts authenticate with a cart token instead of a customer token. Run **Create cart token** with `customerAccessToken` empty (or the request's **Authorization** set to *No Auth*) — it saves `cartToken` to the environment. Called with a customer token, it returns the customer's own cart instead.

To act as that guest, send `{{cartToken}}` as the bearer: change it on the collection's **Authorization** tab, or override it on the individual request. Leave it as `{{customerAccessToken}}` for logged-in flows. The cart and checkout endpoints serve both, so which token you send is what decides whose cart you are working on. To keep the guest cart when the guest logs in, put `{{cartToken}}` into the login's `guest_cart_token`: the answer's `cartId` is the customer's cart the guest cart was merged into.

## Layout

`REST/` and `GraphQL/` share one folder tree, grouped by feature, so `Cart` holds the cart endpoints on either transport. GraphQL is split into `Queries/` and `Mutations/` under each feature.

## Worth knowing

- `X-STOREFRONT-KEY` is sent on every request and is required by all storefront endpoints.
- `X-Locale`, `X-Channel` and `X-Currency` are optional; omit them to use the channel defaults.
- GraphQL requests declare only the arguments an operation **requires**, plus `first` where it paginates. The complete argument list for any operation is in the GraphiQL playground at `/api/graphiql`.
- Postman's **Auto Fetch** runs a schema introspection query, which costs far more than a normal request. If things feel slow, switch it off and fetch the schema when you need it.

Full reference: [api-docs.bagisto.com](https://api-docs.bagisto.com/)
MD;
        }

        return <<<'MD'
Administrative endpoints for the [Bagisto](https://bagisto.com) e-commerce platform — catalog, sales, customers, marketing, settings, CMS and reporting. Both transports are covered: `REST/` and `GraphQL/`.

The full authentication guide, with sequence diagrams, curl and GraphQL examples and client notes, is [docs/authentication.md](https://github.com/bagisto/bagisto-api/blob/main/docs/authentication.md).

## Setup

1. Select the **Bagisto** environment (top right). Import it first if it is not in your workspace. One environment serves both the admin and the storefront collection.
2. Choose how to authenticate.

**As an app (login and refresh).** Set `adminEmail` and `adminPassword`, then run **REST → Auth → Log in with email and password**. It saves `adminAccessToken`, `adminRefreshToken` and `adminAccessTokenExpiresAt` to the environment. The access token lasts 15 minutes by default; before each request the collection renews it through `POST /api/admin/auth/refresh` when it expires within 60 seconds, so you only log in again once the refresh token or the session ends.

**With two-factor authentication.** When the admin has it enabled, the login answers `202` with `twoFactorRequired: true` and a `challengeToken` instead of tokens. The login request stores the challenge token in the collection variable `adminChallengeToken`; put the 6-digit code (or a `backup_code` instead of `code`) into the **Auth** two-factor request and send it within 5 minutes. It saves the tokens the same way.

**As a server integration (API token).** Create an API token under **Settings → API Tokens** in the admin panel (or with `php artisan bagisto-api:api-token:create`), bound to the domain the integration calls from, and paste it into `adminApiToken`. While `adminApiToken` is set it is sent on every request and nothing is refreshed. An API token is shown once, so copy it immediately.

| Variable | Where it comes from |
| --- | --- |
| `url` | Your store, e.g. `http://localhost:8000` |
| `adminEmail` / `adminPassword` | Any active admin user |
| `adminAccessToken` / `adminRefreshToken` / `adminAccessTokenExpiresAt` | Written by the login, two-factor and refresh requests |
| `adminApiToken` | An API token from **Settings → API Tokens**; leave it empty to use the login session |
| `adminChallengeToken` (collection variable) | Written by a login that needs a second factor |

The token is sent as the bearer on every request:

```
Authorization: Bearer eyJhbGciOiJFUzI1NiIsImtpZCI6Ii4uLiIsInR5cCI6IkpXVCJ9...
```

## The login flow

1. **Auth → Log in** returns the access token, the refresh token, the session, the admin and the role's `permissions`.
2. Every request sends the access token. When it expires, a request answers `401` with `error: access_token_expired`.
3. **Auth → Refresh** exchanges the refresh token for a **new pair**. The old refresh token stops working, and presenting it again is treated as theft: the whole session is revoked and every later refresh answers `refresh_token_invalid`. Run one refresh at a time.
4. On `401 refresh_token_invalid` (expired, used or revoked, or the session reached its absolute lifetime, 90 days by default), log in again.
5. **Auth → Log out** ends the session, **Log out of every session** ends all of the admin's sessions, and the sessions requests list and revoke them one by one. These, and **Update my account**, need a login session's access token; an API token gets `403 access_token_required`.

The lifetimes, rate limits and revocation rules are set in the admin panel under **Configuration → API → Admin Authentication**.

## Permissions

A token has exactly the permissions of the admin user's role, read on every request, so a valid token can still return **403** on an endpoint that user cannot reach. Login sessions are listed and revoked under **Auth** and **Settings → API Sessions**, and API tokens under **Settings → API Tokens**. API tokens are created in the admin panel or the console only.

## Errors worth handling

REST bodies carry the code in `error`; GraphQL errors carry it in `extensions.code`.

| Status and code | What to do |
| --- | --- |
| `401 access_token_expired` | Refresh, then retry the request |
| `401 refresh_token_invalid` | Log in again |
| `401 unauthenticated` | The token is invalid, its session was revoked or the admin is inactive: log in again |
| `401 invalid_credentials` | Wrong email or password |
| `401 invalid_two_factor_code` / `two_factor_challenge_invalid` | Retry the code / log in again |
| `401 api_token_expired` / `api_token_revoked` | Create a new API token |
| `403 api_token_domain_mismatch` | Call from the domain the API token is bound to |
| `403 access_token_required` | Use a login session's access token, not an API token |
| `403 account_inactive` | An administrator must activate the admin |
| `429 too_many_login_attempts` / `too_many_password_requests` / `rate_limit_exceeded` | Wait for the `Retry-After` seconds |

## Layout

`REST/` and `GraphQL/` share one folder tree that mirrors the admin sidebar — `Settings/Currencies` holds exactly the endpoints behind that screen. `Auth` lists its requests in the order a client uses them. GraphQL is split into `Queries/` and `Mutations/` under each area.

## Worth knowing

- GraphQL requests declare only the arguments an operation **requires**, plus `first` where it paginates. The complete argument list for any operation is in the GraphiQL playground at `/api/admin/graphiql`.
- Postman's **Auto Fetch** runs a schema introspection query, which costs far more than a normal request. If things feel slow, switch it off and fetch the schema when you need it.

Full reference: [api-docs.bagisto.com](https://api-docs.bagisto.com/)
MD;
    }

    /**
     * The exported OpenAPI document of a surface.
     *
     * @return array<string, mixed>
     */
    private function loadSpec(string $surface): array
    {
        $file = self::SCHEMA_DIR."/openapi-{$surface}.json";

        if (! is_file($file)) {
            fwrite(STDERR, "Missing {$file}. Run: php artisan bagisto-api-platform:export-schema\n");

            exit(1);
        }

        return json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * The exported server carries the surface prefix and a `url` variable default.
     *
     * @param  array<string, mixed>  $spec
     */
    private function baseUrl(array $spec): string
    {
        $url = $spec['servers'][0]['url'] ?? '{url}';

        return str_replace(['{url}'], ['{{url}}'], $url);
    }

    /**
     * The collection variables: the base URL, and for the admin the challenge token a two-factor login hands over.
     *
     * @param  array<string, mixed>  $spec
     * @return list<array<string, string>>
     */
    private function collectionVariables(string $surface, array $spec): array
    {
        $variables = [[
            'key' => 'baseUrl',
            'value' => $this->baseUrl($spec),
            'type' => 'string',
        ]];

        if ($surface === 'admin') {
            $variables[] = [
                'key' => 'adminChallengeToken',
                'value' => '',
                'type' => 'string',
            ];
        }

        return $variables;
    }

    /**
     * Group every operation into a REST folder tree derived from its OpenAPI tag.
     *
     * @param  array<string, mixed>  $spec
     * @return list<array<string, mixed>>
     */
    private function restFolder(array $spec, string $surface): array
    {
        $grouped = [];

        foreach ($spec['paths'] as $path => $operations) {
            foreach ($operations as $method => $operation) {
                if (
                    ! is_array($operation)
                    || ! in_array(strtolower($method), ['get', 'post', 'put', 'patch', 'delete'], true)
                ) {
                    continue;
                }

                $segments = $this->folderSegments($operation['tags'][0] ?? 'Uncategorised', $surface);

                $grouped[implode("\0", $segments)][] = $this->request($path, $method, $operation, $spec, $surface);
            }
        }

        ksort($grouped);

        $tree = [];

        foreach ($grouped as $key => $requests) {
            usort($requests, fn ($a, $b) => $this->compareRequests($a, $b));

            $this->insert($tree, explode("\0", (string) $key), $requests);
        }

        return [[
            'name' => 'REST',
            'description' => 'Every REST endpoint, foldered by the API tag it belongs to.',
            'item' => $this->toItems($tree),
        ]];
    }

    /**
     * "Admin Settings: Currencies" -> ["Settings", "Currencies"] inside the admin collection.
     *
     * @return list<string>
     */
    private function folderSegments(string $tag, string $surface): array
    {
        $tag = self::TAG_ALIASES[$surface][$tag] ?? $tag;

        if ($surface === 'admin') {
            $tag = (string) preg_replace('/^Admin(?=\s|:)\s*:?\s*/', '', $tag);
        }

        $segments = array_values(array_filter(array_map('trim', explode(':', $tag)), fn ($s) => $s !== ''));

        return $segments === [] ? ['Uncategorised'] : $segments;
    }

    /**
     * Add requests to the folder tree under the given path segments.
     *
     * @param  array<string, mixed>  $tree
     * @param  list<string>  $segments
     * @param  list<array<string, mixed>>  $requests
     */
    private function insert(array &$tree, array $segments, array $requests): void
    {
        $head = array_shift($segments);

        $tree[$head] ??= [
            '__requests' => [],
            '__children' => [],
        ];

        if ($segments === []) {
            $tree[$head]['__requests'] = array_merge($tree[$head]['__requests'], $requests);

            return;
        }

        $this->insert($tree[$head]['__children'], $segments, $requests);
    }

    /**
     * The Postman items of a folder tree, folders sorted by name and before their requests.
     *
     * @param  array<string, mixed>  $tree
     * @return list<array<string, mixed>>
     */
    private function toItems(array $tree): array
    {
        ksort($tree);

        $items = [];

        foreach ($tree as $name => $node) {
            $items[] = [
                'name' => $name,
                'item' => array_merge($this->toItems($node['__children']), $node['__requests']),
            ];
        }

        return $items;
    }

    /**
     * Order two requests of a folder: the sign-in requests in client order first, then by name.
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    private function compareRequests(array $a, array $b): int
    {
        $position = $this->clientPosition($a) <=> $this->clientPosition($b);

        return $position !== 0
            ? $position
            : strcmp($a['name'], $b['name']);
    }

    /**
     * The place of a request in the client order, or PHP_INT_MAX for a request outside it.
     *
     * @param  array<string, mixed>  $item
     */
    private function clientPosition(array $item): int
    {
        $request = $item['request'];

        $key = ($request['body']['mode'] ?? null) === 'graphql'
            ? $item['name']
            : $request['method'].' /'.implode('/', $request['url']['path'] ?? []);

        $position = array_search($key, self::CLIENT_ORDER, true);

        return $position === false
            ? PHP_INT_MAX
            : $position;
    }

    /**
     * The Postman request of one OpenAPI operation.
     *
     * @param  array<string, mixed>  $operation
     * @param  array<string, mixed>  $spec
     * @return array<string, mixed>
     */
    private function request(string $path, string $method, array $operation, array $spec, string $surface): array
    {
        $request = [
            'method' => strtoupper($method),
            'header' => $this->headers($operation),
            'url' => $this->url($path, $operation),
        ];

        if ($description = $operation['description'] ?? null) {
            $request['description'] = $description;
        }

        if ($body = $this->body($operation, $spec)) {
            $request['body'] = $body;

            if ($body['mode'] === 'raw') {
                $request['header'][] = [
                    'key' => 'Content-Type',
                    'value' => 'application/json',
                ];
            }
        }

        if ($override = $this->bodyOverride($path, $method, $surface)) {
            $request['body'] = [
                'mode' => 'raw',
                'raw' => json_encode($override, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                'options' => ['raw' => ['language' => 'json']],
            ];
        }

        $item = [
            'name' => $operation['summary'] ?? strtoupper($method).' '.$path,
            'request' => $request,
        ];

        if ($event = $this->tokenCaptureScript($path, $method, $surface)) {
            $item['event'] = $event;
        }

        return $item;
    }

    /**
     * The headers an operation documents, filled from the environment.
     *
     * @param  array<string, mixed>  $operation
     * @return list<array<string, string>>
     */
    private function headers(array $operation): array
    {
        $headers = [];

        foreach ($operation['parameters'] ?? [] as $parameter) {
            if (
                ($parameter['in'] ?? '') !== 'header'
                || ($parameter['name'] ?? '') === 'Authorization'
            ) {
                continue;
            }

            $name = $parameter['name'];

            $headers[] = [
                'key' => $name,
                'value' => self::HEADER_VARS[$name] ?? $this->scalar($parameter['schema']['example'] ?? null, ''),
                'description' => (string) ($parameter['description'] ?? ''),
            ];
        }

        return $headers;
    }

    /**
     * An example value as Postman text, or the fallback when there is none.
     */
    private function scalar(mixed $example, string $fallback): string
    {
        if ($example === null) {
            return $fallback;
        }

        if (is_array($example)) {
            return (string) json_encode($example, JSON_UNESCAPED_SLASHES);
        }

        if (is_bool($example)) {
            return $example ? 'true' : 'false';
        }

        return (string) $example;
    }

    /**
     * The Postman URL of an operation, with its query and path variables.
     *
     * @param  array<string, mixed>  $operation
     * @return array<string, mixed>
     */
    private function url(string $path, array $operation): array
    {
        $postmanPath = (string) preg_replace('/\{([^}]+)\}/', ':$1', $path);

        $query = [];
        $variables = [];

        foreach ($operation['parameters'] ?? [] as $parameter) {
            $example = $parameter['schema']['example'] ?? $parameter['example'] ?? null;

            if (($parameter['in'] ?? '') === 'query') {
                $query[] = [
                    'key' => $parameter['name'],
                    'value' => $this->scalar($example, ''),
                    'description' => (string) ($parameter['description'] ?? ''),
                    'disabled' => ! ($parameter['required'] ?? false),
                ];
            }

            if (($parameter['in'] ?? '') === 'path') {
                $variables[] = [
                    'key' => $parameter['name'],
                    'value' => $this->scalar($example, '1'),
                    'description' => (string) ($parameter['description'] ?? ''),
                ];
            }
        }

        $raw = '{{baseUrl}}'.$postmanPath;

        if ($enabled = array_filter($query, fn ($q) => ! $q['disabled'])) {
            $raw .= '?'.implode('&', array_map(fn ($q) => $q['key'].'='.$q['value'], $enabled));
        }

        $url = [
            'raw' => $raw,
            'host' => ['{{baseUrl}}'],
            'path' => array_values(array_filter(explode('/', trim($postmanPath, '/')), fn ($s) => $s !== '')),
        ];

        if ($query) {
            $url['query'] = $query;
        }

        if ($variables) {
            $url['variable'] = $variables;
        }

        return $url;
    }

    /**
     * The request body of an operation, from its example or its schema.
     *
     * @param  array<string, mixed>  $operation
     * @param  array<string, mixed>  $spec
     * @return array<string, mixed>|null
     */
    private function body(array $operation, array $spec): ?array
    {
        $content = $operation['requestBody']['content'] ?? [];

        if (isset($content['application/json'])) {
            $json = $content['application/json'];

            $example = $json['example'] ?? (is_array($json['examples'] ?? null) ? (reset($json['examples'])['value'] ?? null) : null);

            $example ??= $this->skeleton($json['schema'] ?? [], $spec);

            return [
                'mode' => 'raw',
                'raw' => json_encode($example ?? new stdClass, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                'options' => ['raw' => ['language' => 'json']],
            ];
        }

        foreach ($content as $type => $definition) {
            if (! str_starts_with((string) $type, 'multipart/')) {
                continue;
            }

            return [
                'mode' => 'formdata',
                'formdata' => $this->formData($definition['schema'] ?? [], $spec),
            ];
        }

        return null;
    }

    /**
     * The form-data fields of a multipart schema.
     *
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $spec
     * @return list<array<string, string>>
     */
    private function formData(array $schema, array $spec): array
    {
        $schema = $this->resolve($schema, $spec);

        $fields = [];

        foreach ($schema['properties'] ?? [] as $name => $property) {
            $fields[] = [
                'key' => (string) $name,
                'type' => ($property['format'] ?? null) === 'binary' ? 'file' : 'text',
                'value' => ($property['format'] ?? null) === 'binary' ? '' : $this->scalar($property['example'] ?? null, ''),
                'description' => (string) ($property['description'] ?? ''),
            ];
        }

        return $fields;
    }

    /**
     * Minimal example body for the operations that ship no OpenAPI example.
     *
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $spec
     */
    private function skeleton(array $schema, array $spec, int $depth = 0): mixed
    {
        if ($depth > 3) {
            return null;
        }

        $schema = $this->resolve($schema, $spec);

        if (isset($schema['example'])) {
            return $schema['example'];
        }

        $type = $schema['type'] ?? 'object';

        if ($type === 'array') {
            $child = $this->skeleton($schema['items'] ?? [], $spec, $depth + 1);

            return $child === null ? [] : [$child];
        }

        if ($type !== 'object') {
            return match ($type) {
                'integer' => 1,
                'number' => 0,
                'boolean' => false,
                default => '',
            };
        }

        $object = [];

        foreach ($schema['properties'] ?? [] as $name => $property) {
            if ($property['readOnly'] ?? false) {
                continue;
            }

            $object[$name] = $this->skeleton($property, $spec, $depth + 1);
        }

        return $object === [] ? null : $object;
    }

    /**
     * The schema a reference points to, following at most ten references.
     *
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $spec
     * @return array<string, mixed>
     */
    private function resolve(array $schema, array $spec): array
    {
        $seen = 0;

        while (
            isset($schema['$ref'])
            && $seen++ < 10
        ) {
            $name = substr((string) $schema['$ref'], strlen('#/components/schemas/'));

            $schema = $spec['components']['schemas'][$name] ?? [];
        }

        return $schema;
    }

    /**
     * The test script of the requests that write a token back to the environment.
     *
     * @return list<array<string, mixed>>|null
     */
    private function tokenCaptureScript(string $path, string $method, string $surface): ?array
    {
        if (strtolower($method) !== 'post') {
            return null;
        }

        if ($surface === 'admin') {
            return in_array($path, ['/auth/login', '/auth/two-factor', '/auth/refresh'], true)
                ? [$this->adminTokenScript('pm.response.json()')]
                : null;
        }

        if (in_array($path, ['/customer/login', '/customer/refresh', '/customers', '/customers/social-login'], true)) {
            return [$this->customerTokenScript('pm.response.json()')];
        }

        if ($path !== '/cart-tokens') {
            return null;
        }

        return [$this->script([
            'const body = pm.response.json();',
            'const value = body?.cartToken ?? body?.data?.cartToken;',
            '',
            'if (value) {',
            "    pm.environment.set('cartToken', value);",
            '}',
        ])];
    }

    /**
     * The request body that makes a starter admin request runnable from the environment.
     *
     * @return array<string, string>|null
     */
    private function bodyOverride(string $path, string $method, string $surface): ?array
    {
        if (strtolower($method) !== 'post') {
            return null;
        }

        if ($surface === 'shop') {
            return match ($path) {
                '/customer/login' => [
                    'email' => '{{customerEmail}}',
                    'password' => '{{customerPassword}}',
                    'device_name' => 'Postman',
                    'client_type' => 'other',
                ],

                '/customer/refresh' => [
                    'refresh_token' => '{{customerRefreshToken}}',
                ],

                default => null,
            };
        }

        return match ($path) {
            '/auth/login' => [
                'email' => '{{adminEmail}}',
                'password' => '{{adminPassword}}',
                'device_name' => 'Postman',
                'client_type' => 'other',
            ],

            '/auth/two-factor' => [
                'challenge_token' => '{{adminChallengeToken}}',
                'code' => '123456',
            ],

            '/auth/refresh' => [
                'refresh_token' => '{{adminRefreshToken}}',
            ],

            default => null,
        };
    }

    /**
     * A test script storing the token pair of an answer, or the challenge token of a login that needs a second factor.
     *
     * @return array<string, mixed>
     */
    private function adminTokenScript(string $pairExpression): array
    {
        return $this->script([
            "const pair = {$pairExpression};",
            '',
            'if (pair?.accessToken && pair?.refreshToken) {',
            "    pm.environment.set('adminAccessToken', pair.accessToken);",
            "    pm.environment.set('adminRefreshToken', pair.refreshToken);",
            "    pm.environment.set('adminAccessTokenExpiresAt', pair.accessTokenExpiresAt || '');",
            "    pm.collectionVariables.set('adminChallengeToken', '');",
            '} else if (pair?.twoFactorRequired && pair?.challengeToken) {',
            "    pm.collectionVariables.set('adminChallengeToken', pair.challengeToken);",
            "    console.info('A second factor is required: send the code with the Auth two-factor request (POST /auth/two-factor).');",
            '}',
        ]);
    }

    /**
     * A test script storing the customer token pair of an answer.
     *
     * @return array<string, mixed>
     */
    private function customerTokenScript(string $pairExpression): array
    {
        return $this->script([
            "const pair = {$pairExpression};",
            '',
            'if (pair?.accessToken && pair?.refreshToken) {',
            "    pm.environment.set('customerAccessToken', pair.accessToken);",
            "    pm.environment.set('customerRefreshToken', pair.refreshToken);",
            "    pm.environment.set('customerAccessTokenExpiresAt', pair.accessTokenExpiresAt || '');",
            '}',
        ]);
    }

    /**
     * The collection-level scripts: each collection renews its access token before a request needs it.
     *
     * @return list<array<string, mixed>>
     */
    private function collectionEvents(string $surface): array
    {
        if ($surface === 'shop') {
            return [$this->customerRefreshEvent()];
        }

        $tokenPaths = json_encode(self::ADMIN_TOKEN_PATHS, JSON_UNESCAPED_SLASHES);

        return [[
            'listen' => 'prerequest',

            'script' => [
                'type' => 'text/javascript',

                'exec' => [
                    "const apiToken = pm.environment.get('adminApiToken');",
                    "const path = '/' + pm.request.url.getPath().replace(/^\\/+/, '');",
                    "const isTokenRequest = {$tokenPaths}.some(tokenPath => path.endsWith(tokenPath));",
                    "const graphQlQuery = String(pm.request.body?.graphql?.query || pm.request.body?.raw || '');",
                    'const isPublicMutation = /createAdmin(Login|TwoFactorVerify|TokenRefresh|ForgotPassword|ResetPassword)\b/.test(graphQlQuery);',
                    '',
                    'function expiresAt(token) {',
                    '    try {',
                    "        const part = token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');",
                    "        return JSON.parse(atob(part + '='.repeat((4 - part.length % 4) % 4))).exp * 1000;",
                    '    } catch (error) {',
                    "        return Date.parse(pm.environment.get('adminAccessTokenExpiresAt') || '') || 0;",
                    '    }',
                    '}',
                    '',
                    'if (apiToken) {',
                    "    pm.variables.set('adminAccessToken', apiToken);",
                    '} else if (',
                    '    !isTokenRequest',
                    '    && !isPublicMutation',
                    "    && pm.environment.get('adminRefreshToken')",
                    "    && expiresAt(pm.environment.get('adminAccessToken') || '') - Date.now() < 60000",
                    ') {',
                    '    pm.sendRequest({',
                    "        url: pm.environment.get('url') + '/api/admin/auth/refresh',",
                    "        method: 'POST',",
                    "        header: { 'Content-Type': 'application/json', 'Accept': 'application/json' },",
                    "        body: { mode: 'raw', raw: JSON.stringify({ refresh_token: pm.environment.get('adminRefreshToken') }) },",
                    '    }, (error, response) => {',
                    '        if (',
                    '            !error',
                    '            && response.code === 200',
                    '        ) {',
                    '            const pair = response.json();',
                    '',
                    "            pm.environment.set('adminAccessToken', pair.accessToken);",
                    "            pm.environment.set('adminRefreshToken', pair.refreshToken);",
                    "            pm.environment.set('adminAccessTokenExpiresAt', pair.accessTokenExpiresAt || '');",
                    "            pm.variables.set('adminAccessToken', pair.accessToken);",
                    '        } else {',
                    "            console.warn('The admin refresh token was refused; run Auth → Log in with email and password again.');",
                    '        }',
                    '    });',
                    '}',
                ],
            ],
        ]];
    }

    /**
     * The storefront pre-request script renewing the customer access token when it expires within a minute.
     *
     * @return array<string, mixed>
     */
    private function customerRefreshEvent(): array
    {
        $tokenPaths = json_encode(self::CUSTOMER_TOKEN_PATHS, JSON_UNESCAPED_SLASHES);

        return [
            'listen' => 'prerequest',

            'script' => [
                'type' => 'text/javascript',

                'exec' => [
                    "const path = '/' + pm.request.url.getPath().replace(/^\\/+/, '');",
                    "const isTokenRequest = {$tokenPaths}.some(tokenPath => path.endsWith('/api/shop' + tokenPath));",
                    "const graphQlQuery = String(pm.request.body?.graphql?.query || pm.request.body?.raw || '');",
                    'const isPublicMutation = /create(CustomerLogin|CustomerTokenRefresh|Customer|SocialLogin|ForgotPassword|ResetPassword)\\b/.test(graphQlQuery);',
                    '',
                    'function expiresAt(token) {',
                    '    try {',
                    "        const part = token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');",
                    "        return JSON.parse(atob(part + '='.repeat((4 - part.length % 4) % 4))).exp * 1000;",
                    '    } catch (error) {',
                    "        return Date.parse(pm.environment.get('customerAccessTokenExpiresAt') || '') || 0;",
                    '    }',
                    '}',
                    '',
                    'if (',
                    '    !isTokenRequest',
                    '    && !isPublicMutation',
                    "    && pm.environment.get('customerRefreshToken')",
                    "    && expiresAt(pm.environment.get('customerAccessToken') || '') - Date.now() < 60000",
                    ') {',
                    '    pm.sendRequest({',
                    "        url: pm.environment.get('url') + '/api/shop/customer/refresh',",
                    "        method: 'POST',",
                    "        header: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-STOREFRONT-KEY': pm.environment.get('storefrontKey') },",
                    "        body: { mode: 'raw', raw: JSON.stringify({ refresh_token: pm.environment.get('customerRefreshToken') }) },",
                    '    }, (error, response) => {',
                    '        if (',
                    '            !error',
                    '            && response.code < 300',
                    '        ) {',
                    '            const pair = response.json();',
                    '',
                    "            pm.environment.set('customerAccessToken', pair.accessToken);",
                    "            pm.environment.set('customerRefreshToken', pair.refreshToken);",
                    "            pm.environment.set('customerAccessTokenExpiresAt', pair.accessTokenExpiresAt || '');",
                    "            pm.variables.set('customerAccessToken', pair.accessToken);",
                    '        } else {',
                    "            console.warn('The customer refresh token was refused; run Customer Auth → Customer login again.');",
                    '        }',
                    '    });',
                    '}',
                ],
            ],
        ];
    }

    /**
     * A Postman test event running the given script lines.
     *
     * @param  list<string>  $lines
     * @return array<string, mixed>
     */
    private function script(array $lines): array
    {
        return [
            'listen' => 'test',

            'script' => [
                'type' => 'text/javascript',
                'exec' => $lines,
            ],
        ];
    }

    /**
     * A Postman bearer authorisation with the given token.
     *
     * @return array<string, mixed>
     */
    private function bearer(string $token): array
    {
        return [
            'type' => 'bearer',
            'bearer' => [[
                'key' => 'token',
                'value' => $token,
                'type' => 'string',
            ]],
        ];
    }

    /**
     * Build the GraphQL folder: every root query and mutation, grouped under the same tag tree
     * the REST folder uses.
     *
     * @return array<string, mixed>
     */
    private function graphQlFolder(string $surface, string $url): array
    {
        $schema = BuildSchema::build((string) file_get_contents(self::SCHEMA_DIR."/{$surface}.graphql"));

        $map = json_decode((string) file_get_contents(self::SCHEMA_DIR."/graphql-operations-{$surface}.json"), true, 512, JSON_THROW_ON_ERROR);

        $roots = [
            'query' => $schema->getQueryType()?->getFields() ?? [],
            'mutation' => $schema->getMutationType()?->getFields() ?? [],
        ];

        $grouped = [];

        foreach ($map as $field => $meta) {
            $definition = $roots[$meta['kind']][$field] ?? null;

            if (! $definition instanceof FieldDefinition) {
                continue;
            }

            $segments = $this->folderSegments($meta['tag'], $surface);

            $segments[] = $meta['kind'] === 'query' ? 'Queries' : 'Mutations';

            $grouped[implode("\0", $segments)][] = $this->graphQlRequest($field, $definition, $meta, $url, $surface);
        }

        ksort($grouped);

        $tree = [];

        foreach ($grouped as $key => $requests) {
            usort($requests, fn ($a, $b) => $this->compareRequests($a, $b));

            $this->insert($tree, explode("\0", (string) $key), $requests);
        }

        return [
            'name' => 'GraphQL',
            'description' => 'Every root query and mutation, grouped by the same tags as the REST folder. Optional arguments are omitted from `variables` — the full argument list is in `schema/generated/'.$surface.'.graphql` and in the GraphiQL playground.',
            'item' => $this->toItems($tree),
        ];
    }

    /**
     * Compose one Postman request from a root field definition.
     *
     * @return array<string, mixed>
     */
    private function graphQlRequest(string $field, FieldDefinition $definition, array $meta, string $url, string $surface): array
    {
        $kind = $meta['kind'];

        [$arguments, $variables] = $this->arguments($definition, $field, $meta['example'] ?? null);

        $tree = $this->selectionTree($definition->getType(), 0);

        $query = sprintf(
            "%s %s%s {\n  %s%s%s\n}\n",
            $kind,
            ucfirst($field),
            $arguments['signature'],
            $field,
            $arguments['call'],
            $tree === null ? '' : ' '.$this->renderSelection($tree, 1),
        );

        $request = [
            'method' => 'POST',
            'header' => [[
                'key' => 'Content-Type',
                'value' => 'application/json',
            ]],
            'url' => $this->graphQlUrl($url),

            'body' => [
                'mode' => 'graphql',

                'graphql' => [
                    'query' => $query,
                    'variables' => json_encode($variables === [] ? new stdClass : $variables, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ],
            ],
        ];

        if ($surface === 'shop') {
            $request['header'][] = [
                'key' => 'X-STOREFRONT-KEY',
                'value' => '{{storefrontKey}}',
            ];
        }

        $item = [
            'name' => $field,
            'request' => $request,
        ];

        if ($description = $definition->description) {
            $item['request']['description'] = $description;
        }

        if ($event = $this->graphQlTokenScript($field)) {
            $item['event'] = $event;
        }

        return $item;
    }

    /**
     * Postman expects host and path as separate segments; cramming the whole URL into `host`
     * leaves the request rendering as a single opaque string.
     *
     * @return array<string, mixed>
     */
    private function graphQlUrl(string $url): array
    {
        $host = '{{url}}';

        $path = str_starts_with($url, $host) ? substr($url, strlen($host)) : $url;

        return [
            'raw' => $url,
            'host' => [$host],
            'path' => array_values(array_filter(explode('/', trim($path, '/')), fn ($segment) => $segment !== '')),
        ];
    }

    /**
     * Required arguments only — an optional argument left at null is more likely to trip
     * validation than to help.
     *
     * @return array{0: array{signature: string, call: string}, 1: array<string, mixed>}
     */
    private function arguments(FieldDefinition $definition, string $field, ?array $example = null): array
    {
        $signature = [];
        $call = [];
        $variables = [];

        $identity = null;

        foreach (['id', 'urlKey', 'slug', 'code'] as $candidate) {
            foreach ($definition->args as $argument) {
                if ($argument->name === $candidate) {
                    $identity = $candidate;

                    break 2;
                }
            }
        }

        foreach ($definition->args as $argument) {
            $required = $argument->getType() instanceof NonNull;

            $paging = $argument->name === 'first';

            if (
                ! $required
                && ! $paging
                && $argument->name !== $identity
            ) {
                continue;
            }

            $name = $argument->name;

            $signature[] = '$'.$name.': '.$argument->getType()->toString();
            $call[] = $name.': $'.$name;
            $variables[$name] = $paging && ! $required
                ? 10
                : $this->inputSkeleton($argument->getType(), 0, $name === 'input' ? $example : null, $name);

            if (
                $name === $identity
                && ! $required
            ) {
                $variables[$name] = $this->dummyValue($name, Type::getNamedType($argument->getType())->name);
            }
        }

        $variables = $this->overrideVariables($field, $variables);

        return [[
            'signature' => $signature === [] ? '' : '('.implode(', ', $signature).')',
            'call' => $call === [] ? '' : '('.implode(', ', $call).')',
        ], $variables];
    }

    /**
     * Seed the handful of operations whose values make the starter flows runnable.
     *
     * @param  array<string, mixed>  $variables
     * @return array<string, mixed>
     */
    private function overrideVariables(string $field, array $variables): array
    {
        return match ($field) {
            'createCustomerLogin' => ['input' => [
                'email' => '{{customerEmail}}',
                'password' => '{{customerPassword}}',
                'deviceName' => 'Postman',
                'clientType' => 'other',
            ]] + $variables,

            'createCustomerTokenRefresh' => ['input' => [
                'refreshToken' => '{{customerRefreshToken}}',
            ]] + $variables,

            'createAdminLogin' => ['input' => [
                'email' => '{{adminEmail}}',
                'password' => '{{adminPassword}}',
                'deviceName' => 'Postman',
                'clientType' => 'other',
            ]] + $variables,

            'createAdminTwoFactorVerify' => ['input' => [
                'challengeToken' => '{{adminChallengeToken}}',
                'code' => '123456',
            ]] + $variables,

            'createAdminTokenRefresh' => ['input' => [
                'refreshToken' => '{{adminRefreshToken}}',
            ]] + $variables,

            default => $variables,
        };
    }

    /**
     * A usable value for an argument, built from its input type.
     *
     * @param  array<string, mixed>|null  $example
     */
    private function inputSkeleton(Type $type, int $depth, ?array $example = null, string $argumentName = ''): mixed
    {
        $named = Type::getNamedType($type);

        if ($named instanceof InputObjectType) {
            if ($depth > 2) {
                return new stdClass;
            }

            $fields = $named->getFields();

            $mapped = $example === null ? [] : $this->matchExample($fields, $example);

            if ($mapped !== []) {
                return $mapped;
            }

            $value = [];

            foreach ($fields as $inputField) {
                if (in_array($inputField->name, ['clientMutationId', '_id'], true)) {
                    continue;
                }

                $child = Type::getNamedType($inputField->getType());

                if (
                    $child instanceof InputObjectType
                    && $depth >= 1
                ) {
                    continue;
                }

                $value[$inputField->name] = $this->inputSkeleton($inputField->getType(), $depth + 1, null, $inputField->name);
            }

            return $value === [] ? new stdClass : $value;
        }

        if ($named instanceof EnumType) {
            $values = $named->getValues();

            return $values === [] ? '' : reset($values)->name;
        }

        return $this->dummyValue($argumentName, $named->name);
    }

    /**
     * The example values whose snake_case or camelCase key names an input field.
     *
     * @param  array<string, mixed>  $fields
     * @param  array<string, mixed>  $example
     * @return array<string, mixed>
     */
    private function matchExample(array $fields, array $example): array
    {
        $matched = [];

        foreach ($example as $key => $value) {
            $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', (string) $key))));

            foreach ([$key, $camel] as $candidate) {
                if (isset($fields[$candidate])) {
                    $matched[$candidate] = $value;

                    continue 2;
                }
            }
        }

        return $matched;
    }

    /**
     * A placeholder value for a GraphQL argument, guessed from its name and scalar type.
     */
    private function dummyValue(string $name, string $scalar): mixed
    {
        $lower = strtolower($name);

        return match (true) {
            $scalar === 'Int' => str_contains($lower, 'quantity') || str_contains($lower, 'qty') ? 1 : 1,
            $scalar === 'Float' => str_contains($lower, 'price') || str_contains($lower, 'amount') ? 100 : 1,
            $scalar === 'Boolean' => false,
            $scalar === 'ID' => '1',
            str_contains($lower, 'email') => 'customer@example.com',
            str_contains($lower, 'password') => 'password',
            str_contains($lower, 'phone') => '1234567890',
            $lower === 'locale' => '{{locale}}',
            $lower === 'channel' => '{{channel}}',
            $lower === 'currency' => '{{currency}}',
            str_contains($lower, 'urlkey') || str_contains($lower, 'slug') => 'sample-url-key',
            str_contains($lower, 'sku') => 'sample-sku',
            str_contains($lower, 'code') => 'SAMPLE',
            str_contains($lower, 'date') || str_ends_with($lower, 'at') || str_contains($lower, 'from') || str_contains($lower, 'till') => '2026-01-01',
            str_contains($lower, 'description') || str_contains($lower, 'comment') || str_contains($lower, 'message') || str_contains($lower, 'content') => 'Sample text',
            str_contains($lower, 'name') || str_contains($lower, 'title') || str_contains($lower, 'label') => 'Sample',
            default => 'sample',
        };
    }

    /**
     * The fields to select for a GraphQL type, down to the maximum depth, or null for a leaf.
     *
     * @return array<string, mixed>|null
     */
    private function selectionTree(Type $type, int $depth): ?array
    {
        $named = Type::getNamedType($type);

        if (
            ! $named instanceof ObjectType
            || $depth > self::MAX_SELECTION_DEPTH
        ) {
            return null;
        }

        $fields = $named->getFields();

        if (isset($fields['edges'])) {
            $node = $this->selectionTree(Type::getNamedType($fields['edges']->getType())->getFields()['node']->getType(), $depth + 1);

            return $node === null ? null : [
                'edges' => ['node' => $node],

                'pageInfo' => [
                    'hasNextPage' => null,
                    'endCursor' => null,
                ],
            ];
        }

        $tree = [];

        foreach ($fields as $child) {
            foreach ($child->args as $argument) {
                if ($argument->getType() instanceof NonNull) {
                    continue 2;
                }
            }

            $subTree = $this->selectionTree($child->getType(), $depth + 1);

            if (
                $subTree === null
                && Type::getNamedType($child->getType()) instanceof ObjectType
            ) {
                continue;
            }

            $tree[$child->name] = $subTree;
        }

        return $tree === [] ? null : $tree;
    }

    /**
     * The GraphQL selection set of a field tree, indented to the level.
     *
     * @param  array<string, mixed>  $tree
     */
    private function renderSelection(array $tree, int $level): string
    {
        $pad = str_repeat('  ', $level);

        $lines = ['{'];

        foreach ($tree as $name => $child) {
            $lines[] = $pad.'  '.$name.(is_array($child) ? ' '.$this->renderSelection($child, $level + 1) : '');
        }

        $lines[] = $pad.'}';

        return implode("\n", $lines);
    }

    /**
     * The test script that stores the token a GraphQL login or refresh returns, or null for other fields.
     *
     * @return list<array<string, mixed>>|null
     */
    private function graphQlTokenScript(string $field): ?array
    {
        return match ($field) {
            'createCustomerLogin' => [$this->customerTokenScript('pm.response.json()?.data?.createCustomerLogin?.customerLogin')],
            'createCustomerTokenRefresh' => [$this->customerTokenScript('pm.response.json()?.data?.createCustomerTokenRefresh?.customerTokenRefresh')],
            'createCustomer' => [$this->customerTokenScript('pm.response.json()?.data?.createCustomer?.customer')],
            'createSocialLogin' => [$this->customerTokenScript('pm.response.json()?.data?.createSocialLogin?.socialLogin')],
            'createCartToken' => [$this->script([
                'const data = pm.response.json()?.data?.createCartToken?.cartToken;',
                '',
                'if (data?.cartToken) {',
                "    pm.environment.set('cartToken', data.cartToken);",
                '}',
            ])],
            'createAdminLogin' => [$this->adminTokenScript('pm.response.json()?.data?.createAdminLogin?.adminLogin')],
            'createAdminTwoFactorVerify' => [$this->adminTokenScript('pm.response.json()?.data?.createAdminTwoFactorVerify?.adminTwoFactorVerify')],
            'createAdminTokenRefresh' => [$this->adminTokenScript('pm.response.json()?.data?.createAdminTokenRefresh?.adminTokenRefresh')],
            default => null,
        };
    }

    /**
     * The number of requests in an item and its folders.
     *
     * @param  array<string, mixed>  $item
     */
    private function countRequests(array $item): int
    {
        if (isset($item['request'])) {
            return 1;
        }

        return array_sum(array_map(fn ($child) => $this->countRequests($child), $item['item'] ?? []));
    }
}

(new CollectionBuilder($argv[1] ?? null))->build();
