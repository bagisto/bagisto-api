<?php

namespace Webkul\BagistoApi\Tests\Feature;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Utils\BuildSchema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Webkul\BagistoApi\Exception\AdminAuthException;
use Webkul\BagistoApi\Tests\BagistoApiTest;

class ApiDocumentationTest extends BagistoApiTest
{
    /**
     * The HTTP methods an OpenAPI path item documents an operation under.
     */
    private const METHODS = ['get', 'post', 'put', 'patch', 'delete'];

    /**
     * The HTTP methods whose operations take a request body.
     */
    private const WRITE_METHODS = ['post', 'put', 'patch'];

    /**
     * The shortest operation description that says more than a label.
     */
    private const MIN_DESCRIPTION_LENGTH = 15;

    /**
     * The sentence API Platform writes in place of a missing summary or description.
     */
    private const GENERATED_SENTENCE = '/^(Retrieves|Creates|Replaces|Updates|Removes) (the collection of |an? |the )\w+ resources?\.$/';

    /**
     * The admin sign-in, session, account, API token and API session operations.
     */
    private const ADMIN_AUTH_OPERATIONS = [
        'POST /auth/login',
        'POST /auth/two-factor',
        'POST /auth/refresh',
        'GET /get',
        'GET /auth/sessions',
        'DELETE /auth/sessions/{id}',
        'POST /auth/logout',
        'POST /auth/logout-all',
        'POST /auth/forgot-password',
        'POST /auth/reset-password',
        'PUT /account',
        'GET /settings/api-tokens',
        'GET /settings/api-tokens/{id}',
        'DELETE /settings/api-tokens/{id}',
        'GET /settings/api-sessions',
        'GET /settings/users/{id}/sessions',
        'POST /settings/users/{id}/revoke-sessions',
    ];

    /**
     * The statuses each public admin sign-in operation documents.
     */
    private const ADMIN_SIGN_IN_STATUSES = [
        '/auth/login' => ['200', '202', '401', '403', '422', '429'],
        '/auth/two-factor' => ['200', '401', '422', '429'],
        '/auth/refresh' => ['200', '401', '422', '429'],
        '/auth/forgot-password' => ['200', '422', '429'],
        '/auth/reset-password' => ['200', '422', '429'],
    ];

    /**
     * Route paths under a surface prefix that are not API operations: docs, GraphQL and API Platform internals.
     */
    private const UNDOCUMENTED_ROUTE_PATTERN = '#^(/(docs|graphql|graphiql|\.well-known|contexts|validation_errors|errors)([/.{].*)?)?$#';

    /**
     * The export path.
     */
    private static string $exportPath = '';

    /**
     * Remove the exported schema once the class has run.
     */
    public static function tearDownAfterClass(): void
    {
        if (self::$exportPath !== '') {
            File::deleteDirectory(self::$exportPath);

            self::$exportPath = '';
        }

        parent::tearDownAfterClass();
    }

    /**
     * The admin document holds every sign-in, session, account, API token and API session operation.
     */
    public function test_admin_document_holds_the_auth_operations(): void
    {
        foreach ($this->documents('admin') as $label => $document) {
            $missing = array_values(array_diff(self::ADMIN_AUTH_OPERATIONS, $this->operationKeys($document)));

            expect($missing)->toBe([], "{$label} misses admin auth operations:\n".implode("\n", $missing));
        }
    }

    /**
     * The admin sign-in operations document every status they answer, with Retry-After on the 429.
     */
    public function test_admin_sign_in_operations_document_their_statuses(): void
    {
        foreach ($this->documents('admin') as $label => $document) {
            foreach (self::ADMIN_SIGN_IN_STATUSES as $path => $statuses) {
                $responses = $document['paths'][$path]['post']['responses'] ?? [];

                $missing = array_values(array_diff($statuses, array_map('strval', array_keys($responses))));

                expect($missing)->toBe([], "{$label} POST {$path} documents no ".implode(', ', $missing));

                expect($responses['429']['headers'] ?? [])
                    ->toHaveKey('Retry-After', "{$label} POST {$path} documents no Retry-After on its 429");
            }

            expect($document['components']['schemas'] ?? [])
                ->toHaveKeys(['AdminTokenPair', 'AuthError'], "{$label} lacks the shared auth schemas");
        }
    }

    /**
     * The admin document declares its two JWT bearer schemes and the shop document its storefront key and customer token.
     */
    public function test_documents_declare_their_security_schemes(): void
    {
        foreach ($this->documents('admin') as $label => $document) {
            $schemes = $document['components']['securitySchemes'] ?? [];

            foreach (['adminAccessToken', 'adminApiToken'] as $name) {
                expect($schemes)->toHaveKey($name, "{$label} declares no {$name} scheme");

                expect([
                    $schemes[$name]['type'] ?? null,
                    $schemes[$name]['scheme'] ?? null,
                    $schemes[$name]['bearerFormat'] ?? null,
                ])->toBe(['http', 'bearer', 'JWT'], "{$label} {$name} is not an HTTP bearer JWT scheme");

                expect(trim((string) ($schemes[$name]['description'] ?? '')))
                    ->not->toBe('', "{$label} {$name} does not say where the token comes from");
            }
        }

        foreach ($this->documents('shop') as $label => $document) {
            $schemes = $document['components']['securitySchemes'] ?? [];

            expect($schemes)->toHaveKeys(['storefrontKey', 'customerToken'], "{$label} lacks the storefront schemes");

            expect([
                $schemes['storefrontKey']['type'] ?? null,
                $schemes['storefrontKey']['in'] ?? null,
                $schemes['storefrontKey']['name'] ?? null,
            ])->toBe(['apiKey', 'header', 'X-STOREFRONT-KEY'], "{$label} storefrontKey is not the X-STOREFRONT-KEY header");

            expect([
                $schemes['customerToken']['type'] ?? null,
                $schemes['customerToken']['scheme'] ?? null,
            ])->toBe(['http', 'bearer'], "{$label} customerToken is not an HTTP bearer scheme");
        }
    }

    /**
     * No security scheme in either document still declares the old UUID bearer format.
     */
    public function test_no_scheme_declares_a_uuid_bearer_format(): void
    {
        foreach (['shop', 'admin'] as $surface) {
            foreach ($this->documents($surface) as $label => $document) {
                $uuid = array_keys(array_filter(
                    $document['components']['securitySchemes'] ?? [],
                    fn (array $scheme) => strcasecmp((string) ($scheme['bearerFormat'] ?? ''), 'UUID') === 0,
                ));

                expect($uuid)->toBe([], "{$label} declares UUID bearer schemes: ".implode(', ', $uuid));
            }
        }
    }

    /**
     * The admin guide covers login, refresh and every admin authentication error code.
     */
    public function test_admin_guide_covers_the_login_flow_and_error_codes(): void
    {
        $codes = array_merge(
            array_values(array_filter(
                (new ReflectionClass(AdminAuthException::class))->getConstants(),
                fn ($value, string $name) => str_starts_with($name, 'CODE_'),
                ARRAY_FILTER_USE_BOTH,
            )),
            ['rate_limit_exceeded'],
        );

        foreach ($this->documents('admin') as $label => $document) {
            $guide = (string) ($document['info']['description'] ?? '');

            expect($guide)
                ->toContain('POST /auth/login')
                ->toContain('POST /auth/refresh')
                ->toContain('POST /auth/two-factor');

            $missing = array_values(array_filter($codes, fn (string $code) => ! str_contains($guide, "`{$code}`")));

            expect($missing)->toBe([], "{$label} info.description omits error codes: ".implode(', ', $missing));
        }
    }

    /**
     * The storefront guide covers the customer login, the absence of a refresh and the storefront key error codes.
     */
    public function test_shop_guide_covers_the_login_flow_and_error_codes(): void
    {
        foreach ($this->documents('shop') as $label => $document) {
            $guide = (string) ($document['info']['description'] ?? '');

            expect($guide)
                ->toContain('POST /customer/login')
                ->toContain('refresh')
                ->toContain('POST /cart-tokens');

            $missing = array_values(array_filter(
                ['missing_key', 'invalid_key', 'rate_limit_exceeded'],
                fn (string $code) => ! str_contains($guide, $code),
            ));

            expect($missing)->toBe([], "{$label} info.description omits error codes: ".implode(', ', $missing));
        }
    }

    /**
     * Every REST operation has its own summary and a description that says more than the summary.
     */
    public function test_every_operation_has_a_summary_and_a_description(): void
    {
        foreach (['shop', 'admin'] as $surface) {
            foreach ($this->documents($surface) as $label => $document) {
                $offenders = [];

                foreach ($this->operations($document) as $key => $operation) {
                    $summary = trim((string) ($operation['summary'] ?? ''));

                    $description = trim((string) ($operation['description'] ?? ''));

                    if (
                        $summary === ''
                        || preg_match(self::GENERATED_SENTENCE, $summary)
                    ) {
                        $offenders[] = "{$key}: no summary of its own";
                    }

                    if (mb_strlen($description) < self::MIN_DESCRIPTION_LENGTH) {
                        $offenders[] = "{$key}: description shorter than ".self::MIN_DESCRIPTION_LENGTH.' characters';
                    }

                    if (
                        $description === $summary
                        || preg_match(self::GENERATED_SENTENCE, $description)
                    ) {
                        $offenders[] = "{$key}: no description of its own (it repeats the summary or API Platform's default)";
                    }
                }

                expect($offenders)->toBe([], "{$label} has undocumented operations:\n".implode("\n", $offenders));
            }
        }
    }

    /**
     * Every REST operation documents at least one 4xx response.
     */
    public function test_every_operation_documents_a_client_error(): void
    {
        foreach (['shop', 'admin'] as $surface) {
            foreach ($this->documents($surface) as $label => $document) {
                $offenders = [];

                foreach ($this->operations($document) as $key => $operation) {
                    $clientErrors = array_filter(
                        array_map('strval', array_keys($operation['responses'] ?? [])),
                        fn (string $status) => str_starts_with($status, '4'),
                    );

                    if ($clientErrors === []) {
                        $offenders[] = $key;
                    }
                }

                expect($offenders)->toBe([], "{$label} has operations documenting no 4xx response:\n".implode("\n", $offenders));
            }
        }
    }

    /**
     * Every media type of every write operation's request body carries an example.
     */
    public function test_every_request_body_has_an_example(): void
    {
        foreach (['shop', 'admin'] as $surface) {
            foreach ($this->documents($surface) as $label => $document) {
                $offenders = [];

                foreach ($this->operations($document) as $key => $operation) {
                    if (! in_array(strtolower(explode(' ', $key, 2)[0]), self::WRITE_METHODS, true)) {
                        continue;
                    }

                    foreach ($operation['requestBody']['content'] ?? [] as $mediaType => $media) {
                        if (! $this->hasExample((array) $media)) {
                            $offenders[] = "{$key} ({$mediaType})";
                        }
                    }
                }

                expect($offenders)->toBe([], "{$label} has request bodies without an example:\n".implode("\n", $offenders));
            }
        }
    }

    /**
     * Every package route under api/shop and api/admin is an operation of its document.
     */
    public function test_every_package_route_is_in_its_document(): void
    {
        foreach (['shop', 'admin'] as $surface) {
            $routes = $this->packageRoutes($surface);

            foreach ($this->documents($surface) as $label => $document) {
                $missing = array_values(array_diff($routes, $this->operationKeys($document)));

                expect($missing)->toBe([], "{$label} misses package routes:\n".implode("\n", $missing));
            }
        }
    }

    /**
     * Every operation of a document is a route the package registers, so nothing is documented that cannot be called.
     */
    public function test_every_documented_operation_is_a_route(): void
    {
        foreach (['shop', 'admin'] as $surface) {
            $routes = $this->packageRoutes($surface);

            foreach ($this->documents($surface) as $label => $document) {
                $phantom = array_values(array_diff($this->operationKeys($document), $routes));

                expect($phantom)->toBe([], "{$label} documents operations no route serves:\n".implode("\n", $phantom));
            }
        }
    }

    /**
     * Every root Query and Mutation field of the exported schemas has a description, except the built-in relay node.
     */
    public function test_every_graphql_root_field_has_a_description(): void
    {
        foreach (['shop', 'admin'] as $surface) {
            $schema = BuildSchema::build((string) file_get_contents(self::$exportPath."/{$surface}.graphql"));

            $offenders = [];

            foreach ([$schema->getQueryType(), $schema->getMutationType()] as $type) {
                if (! $type instanceof ObjectType) {
                    continue;
                }

                foreach ($type->getFields() as $field) {
                    if (
                        $field->name !== 'node'
                        && trim((string) $field->description) === ''
                    ) {
                        $offenders[] = "{$type->name}.{$field->name}";
                    }
                }
            }

            expect($offenders)->toBe([], "{$surface}.graphql has root fields without a description:\n".implode("\n", $offenders));
        }
    }

    /**
     * Export the schema once for the whole class.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (self::$exportPath !== '') {
            return;
        }

        self::$exportPath = sys_get_temp_dir().'/bagisto-api-documentation-'.getmypid();

        File::deleteDirectory(self::$exportPath);

        Artisan::call('bagisto-api-platform:export-schema', ['--path' => self::$exportPath]);
    }

    /**
     * The OpenAPI document of a surface as the docs endpoint serves it and as export-schema writes it, keyed by a label.
     *
     * @return array<string, array<string, mixed>>
     */
    private function documents(string $surface): array
    {
        return [
            "GET /api/{$surface}/docs" => $this->get("/api/{$surface}/docs")->assertOk()->json(),
            "openapi-{$surface}.json" => json_decode((string) file_get_contents(self::$exportPath."/openapi-{$surface}.json"), true),
        ];
    }

    /**
     * Every operation of a document, keyed by "METHOD path".
     *
     * @param  array<string, mixed>  $document
     * @return array<string, array<string, mixed>>
     */
    private function operations(array $document): array
    {
        $operations = [];

        foreach ($document['paths'] ?? [] as $path => $item) {
            foreach ((array) $item as $method => $operation) {
                if (
                    in_array($method, self::METHODS, true)
                    && is_array($operation)
                ) {
                    $operations[strtoupper($method).' '.$path] = $operation;
                }
            }
        }

        return $operations;
    }

    /**
     * The "METHOD path" of every operation of a document.
     *
     * @param  array<string, mixed>  $document
     * @return list<string>
     */
    private function operationKeys(array $document): array
    {
        return array_keys($this->operations($document));
    }

    /**
     * Whether a request body media type carries an example, on itself, on its schema or on a schema property.
     *
     * @param  array<string, mixed>  $media
     */
    private function hasExample(array $media): bool
    {
        if (
            array_key_exists('example', $media)
            || ! empty($media['examples'])
        ) {
            return true;
        }

        $schema = (array) ($media['schema'] ?? []);

        if (array_key_exists('example', $schema)) {
            return true;
        }

        foreach ((array) ($schema['properties'] ?? []) as $property) {
            if (
                is_array($property)
                && array_key_exists('example', $property)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * The "METHOD path" of every route under a surface prefix, without API Platform's unexposed, docs and GraphQL routes.
     *
     * @return list<string>
     */
    private function packageRoutes(string $surface): array
    {
        $prefix = "api/{$surface}";

        $routes = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $uri = $route->uri();

            if (
                ! str_starts_with($uri.'/', $prefix.'/')
                || str_contains((string) $route->getActionName(), 'NotExposedController')
            ) {
                continue;
            }

            $path = str_replace('{_format?}', '', substr($uri, strlen($prefix)));

            if (preg_match(self::UNDOCUMENTED_ROUTE_PATTERN, $path)) {
                continue;
            }

            foreach ($route->methods() as $method) {
                if (in_array(strtolower($method), self::METHODS, true)) {
                    $routes[] = $method.' '.$path;
                }
            }
        }

        return array_values(array_unique($routes));
    }
}
