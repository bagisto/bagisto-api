<?php

declare(strict_types=1);

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\BuildSchema;

// Runs both from a host project (packages/Webkul/BagistoApi/...) and from the package
// repository, where the package itself is the root.
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
    private const SCHEMA_DIR = __DIR__.'/../generated';

    private string $outputDir;

    public function __construct(?string $outputDir = null)
    {
        // The package's own collections/ directory, so running the tool with no argument
        // regenerates the collections that ship with it.
        $outputDir ??= __DIR__.'/../../collections';

        $this->outputDir = rtrim($outputDir, '/');

        if (! is_dir($this->outputDir)) {
            fwrite(STDERR, "Output directory does not exist: {$this->outputDir}\n");

            exit(1);
        }
    }

    private const MAX_SELECTION_DEPTH = 1;

    private const HEADER_VARS = [
        'X-STOREFRONT-KEY' => '{{storefrontKey}}',
        'X-Locale' => '{{locale}}',
        'X-Channel' => '{{channel}}',
        'X-Currency' => '{{currency}}',
    ];

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
        ],

        'admin' => [],
    ];

    private const SURFACES = [
        'shop' => [
            'id' => 'e7c1a940-6b2d-4f18-9a35-1d8c05b7e240',
            'name' => 'Bagisto Shop API',
            'file' => 'Bagisto-Shop-API.postman_collection.json',
            'token' => '{{customerToken}}',
            'graphql' => '{{url}}/api/graphql',
        ],

        'admin' => [
            'id' => 'a3d5f218-4c60-4b9e-8f71-2b6e93c4a017',
            'name' => 'Bagisto Admin API',
            'file' => 'Bagisto-Admin-API.postman_collection.json',
            'token' => '{{adminToken}}',
            'graphql' => '{{url}}/api/admin/graphql',
        ],
    ];

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

                'variable' => [
                    [
                        'key' => 'baseUrl',
                        'value' => $this->baseUrl($spec),
                        'type' => 'string',
                    ],
                ],

                'item' => $items,
            ];

            $path = $this->outputDir.'/'.$config['file'];

            file_put_contents($path, json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");

            printf("Wrote %s (%d REST requests, %d GraphQL requests)\n", $config['file'], $this->countRequests($items[0] ?? []), $this->countRequests(end($items)));
        }
    }

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
| `storefrontKey` | Admin panel → Configuration → API |
| `customerEmail` / `customerPassword` | Any storefront customer account |

3. Run **REST → Customer → Customer login**. It saves `customerToken` to the environment, and every other request sends it as the bearer automatically.

## Shopping as a guest

Guest carts authenticate with a cart token instead of a customer token. Run **Create cart token** — it saves `cartToken` to the environment.

To act as that guest, send `{{cartToken}}` as the bearer: change it on the collection's **Authorization** tab, or override it on the individual request. Leave it as `{{customerToken}}` for logged-in flows. The cart and checkout endpoints serve both, so which token you send is what decides whose cart you are working on.

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

## Setup

1. Select the **Bagisto** environment (top right). Import it first if it is not in your workspace. One environment serves both the admin and the storefront collection.
2. In the admin panel, enable the module under **Configuration → API → Integration → Module Settings**, then go to **Settings → Integration**, create an integration and press **Generate**. The token is shown once — copy it immediately.
3. Set these values:

| Variable | Where it comes from |
| --- | --- |
| `url` | Your store, e.g. `http://localhost:8000` |
| `adminToken` | The generated token, copied whole — it includes the numeric id prefix |

There is no login request. Admin access is a pre-issued integration token, sent as the bearer on every request:

```
Authorization: Bearer 5|1dYWpciAn2Ro8dfsabA89ohhduVWWXqicyPyQeIH
```

## Permissions

A token inherits the permissions of the admin user it belongs to, so a valid token can still return **403** on an endpoint that user cannot reach. Tokens can be revoked at any time from the same screen, or from the link in the notification email.

## Layout

`REST/` and `GraphQL/` share one folder tree that mirrors the admin sidebar — `Settings/Currencies` holds exactly the endpoints behind that screen. GraphQL is split into `Queries/` and `Mutations/` under each area.

## Worth knowing

- GraphQL requests declare only the arguments an operation **requires**, plus `first` where it paginates. The complete argument list for any operation is in the GraphiQL playground at `/api/admin/graphiql`.
- Postman's **Auto Fetch** runs a schema introspection query, which costs far more than a normal request. If things feel slow, switch it off and fetch the schema when you need it.

Full reference: [api-docs.bagisto.com](https://api-docs.bagisto.com/)
MD;
    }

    /**
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

                $grouped[implode("\0", $segments)][] = $this->request($path, $method, $operation, $spec);
            }
        }

        ksort($grouped);

        $tree = [];

        foreach ($grouped as $key => $requests) {
            usort($requests, fn ($a, $b) => strcmp($a['name'], $b['name']));

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
     * @param  array<string, mixed>  $operation
     * @param  array<string, mixed>  $spec
     * @return array<string, mixed>
     */
    private function request(string $path, string $method, array $operation, array $spec): array
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

        $item = [
            'name' => $operation['summary'] ?? strtoupper($method).' '.$path,
            'request' => $request,
        ];

        if ($event = $this->tokenCaptureScript($path, $method)) {
            $item['event'] = $event;
        }

        return $item;
    }

    /**
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
     * The only three requests that carry a script: each writes a token back to the environment.
     *
     * @return list<array<string, mixed>>|null
     */
    private function tokenCaptureScript(string $path, string $method): ?array
    {
        if (strtolower($method) !== 'post') {
            return null;
        }

        $capture = match ($path) {
            '/customer/login' => ['customerToken', 'token'],
            '/cart-tokens' => ['cartToken', 'cartToken'],
            default => null,
        };

        if ($capture === null) {
            return null;
        }

        [$variable, $field] = $capture;

        return [$this->script([
            'const body = pm.response.json();',
            "const value = body?.{$field} ?? body?.data?.{$field};",
            '',
            'if (value) {',
            "    pm.environment.set('{$variable}', value);",
            '}',
        ])];
    }

    /**
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
            usort($requests, fn ($a, $b) => strcmp($a['name'], $b['name']));

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
     * @return list<array<string, mixed>>|null
     */
    private function graphQlTokenScript(string $field): ?array
    {
        return match ($field) {
            'createCustomerLogin' => [$this->script([
                'const data = pm.response.json()?.data?.createCustomerLogin?.customerLogin;',
                '',
                'if (data?.token || data?.apiToken) {',
                "    pm.environment.set('customerToken', data.token || data.apiToken);",
                '}',
            ])],
            'createCartToken' => [$this->script([
                'const data = pm.response.json()?.data?.createCartToken?.cartToken;',
                '',
                'if (data?.cartToken) {',
                "    pm.environment.set('cartToken', data.cartToken);",
                '}',
            ])],
            default => null,
        };
    }

    /**
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
