<?php

namespace Webkul\BagistoApi\Tests\Feature;

use GraphQL\Language\Parser;
use GraphQL\Utils\BuildSchema;
use GraphQL\Validator\DocumentValidator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Webkul\BagistoApi\Tests\BagistoApiTest;

class SchemaExportTest extends BagistoApiTest
{
    /**
     * The storefront key prefix constant.
     */
    private const STOREFRONT_KEY_PREFIX = 'pk_storefront_';

    /**
     * The export path.
     */
    private static string $exportPath = '';

    /**
     * Tear down the shared test environment.
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
     * Test export writes both surfaces for both transports.
     */
    public function test_export_writes_both_surfaces_for_both_transports(): void
    {
        $expected = [
            'openapi-shop.json',
            'openapi-admin.json',
            'shop.graphql',
            'admin.graphql',
            'graphql-operations-shop.json',
            'graphql-operations-admin.json',
        ];

        foreach ($expected as $file) {
            $this->assertFileExists(self::$exportPath.'/'.$file);
        }
    }

    /**
     * Test storefront spec carries no admin definition.
     */
    public function test_storefront_spec_carries_no_admin_definition(): void
    {
        $spec = $this->spec('shop');

        $schemas = array_filter(
            array_keys($spec['components']['schemas']),
            fn ($name) => str_starts_with($name, 'Admin'),
        );

        $tags = array_filter(
            array_column($spec['tags'], 'name'),
            fn ($name) => str_starts_with($name, 'Admin'),
        );

        expect($schemas)->toBe([]);
        expect($tags)->toBe([]);
    }

    /**
     * Test every schema reference resolves.
     */
    public function test_every_schema_reference_resolves(): void
    {
        foreach (['shop', 'admin'] as $surface) {
            $spec = $this->spec($surface);

            $missing = array_diff(
                $this->reachable($spec),
                array_keys($spec['components']['schemas']),
            );

            expect($missing)->toBe([], "openapi-{$surface}.json has unresolved schema references");
        }
    }

    /**
     * Test no schema is orphaned.
     */
    public function test_no_schema_is_orphaned(): void
    {
        foreach (['shop', 'admin'] as $surface) {
            $spec = $this->spec($surface);

            $orphans = array_diff(
                array_keys($spec['components']['schemas']),
                $this->reachable($spec),
            );

            expect(array_values($orphans))->toBe([], "openapi-{$surface}.json carries schemas no path uses");
        }
    }

    /**
     * Test spec is import ready.
     */
    public function test_spec_is_import_ready(): void
    {
        foreach ([
            'shop' => '/api/shop',
            'admin' => '/api/admin',
        ] as $surface => $prefix) {
            $spec = $this->spec($surface);

            expect($spec['info']['title'])->not->toBe('');
            expect($spec['servers'][0]['url'])->toBe('{url}'.$prefix);
            expect($spec['servers'][0]['variables']['url']['default'])->not->toBe('');
        }
    }

    /**
     * Test top level tags cover every operation tag.
     */
    public function test_top_level_tags_cover_every_operation_tag(): void
    {
        foreach (['shop', 'admin'] as $surface) {
            $spec = $this->spec($surface);

            $declared = array_column($spec['tags'], 'name');

            expect($declared)->not->toBe([], "openapi-{$surface}.json declares no tags");

            $used = [];

            foreach ($spec['paths'] as $operations) {
                foreach ($operations as $operation) {
                    if (is_array($operation)) {
                        $used = array_merge($used, $operation['tags'] ?? []);
                    }
                }
            }

            expect(array_values(array_diff(array_unique($used), $declared)))->toBe([]);
            expect(array_values(array_diff($declared, array_unique($used))))->toBe([]);
        }
    }

    /**
     * Test no exported artifact contains a storefront key.
     */
    public function test_no_exported_artifact_contains_a_storefront_key(): void
    {
        $files = array_merge(
            glob(self::$exportPath.'/*') ?: [],
            glob(dirname(__DIR__, 2).'/schema/generated/*') ?: [],
            glob(self::$exportPath.'/collections/*') ?: [],
        );

        foreach (array_filter($files, 'is_file') as $file) {
            expect(str_contains((string) file_get_contents($file), self::STOREFRONT_KEY_PREFIX))
                ->toBeFalse(basename($file).' contains a storefront key');
        }
    }

    /**
     * Test graphql schemas do not overlap.
     */
    public function test_graphql_schemas_do_not_overlap(): void
    {
        $shop = (string) file_get_contents(self::$exportPath.'/shop.graphql');
        $admin = (string) file_get_contents(self::$exportPath.'/admin.graphql');

        expect(preg_match('/^type Admin\w+/m', $shop))->toBe(0);

        expect(array_values(array_intersect(
            $this->rootFields($shop, 'Mutation'),
            $this->rootFields($admin, 'Mutation'),
        )))->toBe([]);

        expect(array_values(array_diff(
            array_intersect($this->rootFields($shop, 'Query'), $this->rootFields($admin, 'Query')),
            ['node'],
        )))->toBe([]);
    }

    /**
     * Test exported graphql keeps array typed fields.
     */
    public function test_exported_graphql_keeps_array_typed_fields(): void
    {
        foreach (['shop', 'admin'] as $surface) {
            $sdl = (string) file_get_contents(self::$exportPath."/{$surface}.graphql");

            expect(substr_count($sdl, 'Iterable'))->toBeGreaterThan(0, "{$surface}.graphql lost every array-typed field");

            expect(preg_match('/^scalar Iterable$/m', $sdl))->toBe(1, "{$surface}.graphql uses Iterable without defining it");
        }
    }

    /**
     * Test exported graphql matches the running schema.
     */
    public function test_exported_graphql_matches_the_running_schema(): void
    {
        $this->skipUnlessCoreSupports($this->core()->hasAppearanceSections(), 'The storefront Theme type');

        $sdl = (string) file_get_contents(self::$exportPath.'/shop.graphql');

        preg_match('/^type Theme implements Node \{(.*?)^\}/ms', $sdl, $matches);

        expect($matches[1] ?? '')->toContain('sectionTypes: Iterable!');
    }

    /**
     * Test collection graphql requests validate against the schema.
     */
    public function test_collection_graphql_requests_validate_against_the_schema(): void
    {
        foreach ([
            'shop' => 'Bagisto-Shop-API',
            'admin' => 'Bagisto-Admin-API',
        ] as $surface => $name) {
            $schema = BuildSchema::build((string) file_get_contents(dirname(__DIR__, 2)."/schema/generated/{$surface}.graphql"));

            $requests = $this->graphQlRequests($name);

            expect(count($requests))->toBeGreaterThan(100, "{$name} exposes too few GraphQL requests to be full coverage");

            foreach ($requests as $request) {
                $errors = DocumentValidator::validate($schema, Parser::parse($request['query']));

                expect(array_map(fn ($error) => $error->getMessage(), $errors))
                    ->toBe([], "{$name} / {$request['name']} does not validate");
            }
        }
    }

    /**
     * Test graphql folder covers every root operation.
     */
    public function test_graphql_folder_covers_every_root_operation(): void
    {
        $pairs = [
            'shop' => 'Bagisto-Shop-API',
            'admin' => 'Bagisto-Admin-API',
        ];

        foreach ($pairs as $surface => $collection) {
            $map = json_decode((string) file_get_contents(dirname(__DIR__, 2)."/schema/generated/graphql-operations-{$surface}.json"), true);

            $shipped = array_column($this->graphQlRequests($collection), 'name');

            expect(array_values(array_diff(array_keys($map), $shipped)))
                ->toBe([], "{$collection} is missing GraphQL operations the schema exposes");
        }
    }

    /**
     * Test graphql folders mirror the rest folders.
     */
    public function test_graphql_folders_mirror_the_rest_folders(): void
    {
        foreach (['Bagisto-Shop-API', 'Bagisto-Admin-API'] as $collection) {
            $body = json_decode((string) file_get_contents(self::$exportPath."/collections/{$collection}.postman_collection.json"), true);

            $folders = [];

            foreach ($body['item'] as $folder) {
                $folders[$folder['name']] = array_column($folder['item'], 'name');
            }

            expect($folders['GraphQL'])->not->toBe([]);

            expect(array_values(array_diff($folders['GraphQL'], $folders['REST'])))
                ->toBe([], "{$collection} has GraphQL folders with no REST counterpart");
        }
    }

    /**
     * Test every request url is well formed.
     */
    public function test_every_request_url_is_well_formed(): void
    {
        foreach (['Bagisto-Shop-API', 'Bagisto-Admin-API'] as $collection) {
            $body = json_decode((string) file_get_contents(self::$exportPath."/collections/{$collection}.postman_collection.json"), true);

            $requests = [];

            $this->collectAllRequests($body, $requests);

            expect(count($requests))->toBeGreaterThan(250, "{$collection} holds fewer requests than expected");

            foreach ($requests as $request) {
                $url = $request['request']['url'];

                expect($url['raw'] ?? '')->toStartWith('{{', "{$request['name']} has no variable-rooted URL");
                expect($url['host'] ?? [])->not->toBe([], "{$request['name']} has no host");

                foreach ($url['host'] as $segment) {
                    expect(str_contains((string) $segment, '/'))
                        ->toBeFalse("{$request['name']} crams a path into its host segment");
                }
            }
        }
    }

    /**
     * The committed artifacts are what clients, Postman and codegen consume, so a code change that
     * is not followed by an export publishes a document the API no longer matches.
     */
    public function test_committed_artifacts_match_a_fresh_export(): void
    {
        $fix = 'Run: php artisan bagisto-api-platform:export-schema && php schema/tools/build-collection.php';

        $generated = [
            'openapi-shop.json',
            'openapi-admin.json',
            'graphql-operations-shop.json',
            'graphql-operations-admin.json',
            'shop.graphql',
            'admin.graphql',
        ];

        foreach ($generated as $file) {
            $committed = dirname(__DIR__, 2)."/schema/generated/{$file}";
            $fresh = self::$exportPath.'/'.$file;

            expect(is_file($committed))->toBeTrue("schema/generated/{$file} is missing; {$fix}");

            $this->assertFreshExportMatches(
                $fresh,
                $committed,
                "schema/generated/{$file} is stale; {$fix}",
            );
        }

        foreach (['Bagisto-Shop-API', 'Bagisto-Admin-API'] as $collection) {
            $file = "collections/{$collection}.postman_collection.json";

            $this->assertFreshExportMatches(
                self::$exportPath.'/collections/'.$collection.'.postman_collection.json',
                dirname(__DIR__, 2).'/'.$file,
                "{$file} is stale; {$fix}",
            );
        }
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (self::$exportPath !== '') {
            return;
        }

        self::$exportPath = sys_get_temp_dir().'/bagisto-schema-export-'.getmypid();

        File::deleteDirectory(self::$exportPath);

        Artisan::call('bagisto-api-platform:export-schema', ['--path' => self::$exportPath]);

        File::ensureDirectoryExists(self::$exportPath.'/collections');

        exec(sprintf(
            'cd %s && php %s %s 2>&1',
            escapeshellarg(self::$exportPath),
            escapeshellarg(dirname(__DIR__, 2).'/schema/tools/build-collection.php'),
            escapeshellarg(self::$exportPath.'/collections'),
        ));
    }

    /**
     * Compare by decoded structure for JSON, so a key-order-only difference is not a failure.
     */
    private function assertFreshExportMatches(string $fresh, string $committed, string $message): void
    {
        $freshContents = (string) file_get_contents($fresh);

        if (str_ends_with($committed, '.json')) {
            expect(json_decode($freshContents, true))
                ->toEqual(json_decode((string) file_get_contents($committed), true), $message);

            return;
        }

        expect($freshContents)->toBe((string) file_get_contents($committed), $message);
    }

    /**
     * Collect every request of a collection item and its nested items into the given list.
     *
     * @param  array<string, mixed>  $item
     * @param  list<array<string, mixed>>  $requests
     */
    private function collectAllRequests(array $item, array &$requests): void
    {
        if (isset($item['request'])) {
            $requests[] = $item;

            return;
        }

        foreach ($item['item'] ?? [] as $child) {
            $this->collectAllRequests($child, $requests);
        }
    }

    /**
     * The exported OpenAPI spec of the given surface.
     *
     * @return array<string, mixed>
     */
    private function spec(string $surface): array
    {
        return json_decode((string) file_get_contents(self::$exportPath."/openapi-{$surface}.json"), true);
    }

    /**
     * Transitive closure of the schema names referenced from the paths.
     *
     * @param  array<string, mixed>  $spec
     * @return list<string>
     */
    private function reachable(array $spec): array
    {
        $schemas = $spec['components']['schemas'] ?? [];

        $pending = $this->refs($spec['paths'] ?? []);
        $seen = [];

        while ($pending) {
            $name = array_pop($pending);

            if (isset($seen[$name])) {
                continue;
            }

            $seen[$name] = true;

            if (isset($schemas[$name])) {
                $pending = array_merge($pending, $this->refs($schemas[$name]));
            }
        }

        return array_keys($seen);
    }

    /**
     * Collect every schema reference found anywhere in the given spec node.
     *
     * @return list<string>
     */
    private function refs(mixed $node, array &$found = []): array
    {
        if (! is_array($node)) {
            return array_keys($found);
        }

        foreach ($node as $key => $value) {
            if (
                $key === '$ref'
                && is_string($value)
                && str_starts_with($value, '#/components/schemas/')
            ) {
                $found[substr($value, strlen('#/components/schemas/'))] = true;

                continue;
            }

            $this->refs($value, $found);
        }

        return array_keys($found);
    }

    /**
     * The field names declared on the given root type of a GraphQL SDL.
     *
     * @return list<string>
     */
    private function rootFields(string $sdl, string $type): array
    {
        if (! preg_match('/^type '.$type.' \{(.*?)^\}/ms', $sdl, $block)) {
            return [];
        }

        preg_match_all('/^  (\w+)[(:]/m', $block[1], $fields);

        return $fields[1];
    }

    /**
     * The GraphQL requests of the given Postman collection, with their names and queries.
     *
     * @return list<array{name: string, query: string}>
     */
    private function graphQlRequests(string $collection): array
    {
        $body = json_decode((string) file_get_contents(self::$exportPath."/collections/{$collection}.postman_collection.json"), true);

        $requests = [];

        foreach ($body['item'] as $folder) {
            if ($folder['name'] === 'GraphQL') {
                $this->collectRequests($folder, $requests);
            }
        }

        return $requests;
    }

    /**
     * Collect the requests of a collection item and its nested items into the given list.
     *
     * @param  array<string, mixed>  $item
     * @param  list<array{name: string, query: string}>  $requests
     */
    private function collectRequests(array $item, array &$requests): void
    {
        if (isset($item['request'])) {
            $requests[] = [
                'name' => $item['name'],
                'query' => $item['request']['body']['graphql']['query'],
            ];

            return;
        }

        foreach ($item['item'] ?? [] as $child) {
            $this->collectRequests($child, $requests);
        }
    }
}
