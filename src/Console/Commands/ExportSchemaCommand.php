<?php

declare(strict_types=1);

namespace Webkul\BagistoApi\Console\Commands;

use ApiPlatform\GraphQl\Type\FieldsBuilderEnumInterface;
use ApiPlatform\GraphQl\Type\TypesContainerInterface;
use ApiPlatform\GraphQl\Type\TypesFactoryInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Serializer\Serializer;
use Webkul\BagistoApi\GraphQl\QueryScopedSchemaBuilder;
use Webkul\BagistoApi\GraphQl\ScopedSchemaBuilder;

class ExportSchemaCommand extends Command
{
    protected $signature = 'bagisto-api-platform:export-schema
        {--path= : Output directory (default: the package schema/generated folder)}
        {--transport=all : Which schemas to export — all, rest, or graphql}';

    protected $description = 'Export the shop and admin API schemas — OpenAPI JSON (REST) and SDL (GraphQL) — split per surface, for Postman, tooling, and codegen.';

    /**
     * Singletons rebuilt between the two surfaces so neither carries the other's
     * resources. `TypesContainerInterface` is deliberately NOT in here — it is shared by
     * the type converter and the fields builder, and replacing only the container leaves
     * the converter looking up scalar types in the old one. `Iterable` is then never
     * found, and every array-typed field is dropped from the printed schema without an
     * error.
     */
    private const DEPS = [
        ResourceNameCollectionFactoryInterface::class,
        ResourceMetadataCollectionFactoryInterface::class,
        TypesFactoryInterface::class,
        FieldsBuilderEnumInterface::class,
    ];

    private const SURFACES = [
        'shop' => '/api/shop',
        'admin' => '/api/admin',
    ];

    private const TITLES = [
        'shop' => 'Bagisto Shop API',
        'admin' => 'Bagisto Admin API',
    ];

    private const DEFAULT_HOST = 'http://localhost:8000';

    private const STOREFRONT_KEY_PLACEHOLDER = '{{storefrontKey}}';

    private const STOREFRONT_KEY_PREFIX = 'pk_storefront_';

    public function handle(): int
    {
        $path = rtrim($this->option('path') ?: dirname(__DIR__, 3).'/schema/generated', '/');

        $transport = $this->option('transport');

        File::ensureDirectoryExists($path);

        try {
            if (in_array($transport, ['all', 'rest'], true)) {
                $this->exportRest($path);
            }

            if (in_array($transport, ['all', 'graphql'], true)) {
                $this->exportGraphQl($path);
            }
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function exportRest(string $path): void
    {
        $factory = $this->laravel->make(OpenApiFactoryInterface::class);

        $normalizer = $this->laravel->make(Serializer::class);

        foreach (self::SURFACES as $surface => $prefix) {
            $spec = $normalizer->normalize(
                $factory(['endpoint' => $surface, 'spec_version' => '3']),
                'json',
                ['spec_version' => '3'],
            );

            $spec = json_decode(json_encode($spec, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);

            $spec = $this->harden($spec, $surface, $prefix);

            $this->guard($spec, $surface);

            $file = "{$path}/openapi-{$surface}.json";

            File::put($file, json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            $this->info(sprintf(
                'Wrote %s (%d paths, %d schemas, %d tags)',
                $file,
                count($spec['paths'] ?? []),
                count($spec['components']['schemas'] ?? []),
                count($spec['tags'] ?? []),
            ));
        }
    }

    /**
     * Apply the export-only adjustments the live docs pages do not need.
     *
     * @param  array<string, mixed>  $spec
     * @return array<string, mixed>
     */
    private function harden(array $spec, string $surface, string $prefix): array
    {
        $spec['info']['title'] = self::TITLES[$surface];

        $spec['servers'] = [[
            'url' => '{url}'.$prefix,
            'description' => self::TITLES[$surface],
            'variables' => [
                'url' => [
                    'default' => self::DEFAULT_HOST,
                    'description' => 'Base URL of your Bagisto installation.',
                ],
            ],
        ]];

        return $this->scrubStorefrontKey($spec, (string) config('storefront.playground_key'));
    }

    private function scrubStorefrontKey(mixed $node, string $configuredKey): mixed
    {
        if (is_array($node)) {
            foreach ($node as $key => $value) {
                $node[$key] = $this->scrubStorefrontKey($value, $configuredKey);
            }

            return $node;
        }

        if (! is_string($node)) {
            return $node;
        }

        if (str_starts_with($node, self::STOREFRONT_KEY_PREFIX)) {
            return self::STOREFRONT_KEY_PLACEHOLDER;
        }

        if ($configuredKey !== '' && str_contains($node, $configuredKey)) {
            return self::STOREFRONT_KEY_PLACEHOLDER;
        }

        return $node;
    }

    /**
     * Refuse to write a spec that leaks another surface, carries a credential, or references
     * a schema it does not define.
     *
     * @param  array<string, mixed>  $spec
     */
    private function guard(array $spec, string $surface): void
    {
        $schemas = $spec['components']['schemas'] ?? [];

        $reachable = $this->reachableSchemas($spec['paths'] ?? [], $schemas);

        if ($missing = array_diff($reachable, array_keys($schemas))) {
            throw new RuntimeException(sprintf(
                'openapi-%s: %d unresolved schema reference(s): %s',
                $surface,
                count($missing),
                implode(', ', array_slice($missing, 0, 5)),
            ));
        }

        if ($orphans = array_diff(array_keys($schemas), $reachable)) {
            throw new RuntimeException(sprintf(
                'openapi-%s: %d schema(s) not reachable from any path — the surface filter leaked: %s',
                $surface,
                count($orphans),
                implode(', ', array_slice($orphans, 0, 5)),
            ));
        }

        if ($surface === 'shop') {
            $foreign = array_filter(
                array_merge(array_keys($schemas), array_column($spec['tags'] ?? [], 'name')),
                fn ($name) => str_starts_with((string) $name, 'Admin'),
            );

            if ($foreign) {
                throw new RuntimeException(sprintf(
                    'openapi-shop: %d admin definition(s) leaked into the storefront spec: %s',
                    count($foreign),
                    implode(', ', array_slice(array_values($foreign), 0, 5)),
                ));
            }
        }

        if (str_contains(json_encode($spec) ?: '', self::STOREFRONT_KEY_PREFIX)) {
            throw new RuntimeException("openapi-{$surface}: a storefront key survived scrubbing — refusing to write a credential to disk.");
        }
    }

    /**
     * Transitive closure of schema names referenced from the paths.
     *
     * @param  array<string, mixed>  $paths
     * @param  array<string, mixed>  $schemas
     * @return list<string>
     */
    private function reachableSchemas(array $paths, array $schemas): array
    {
        $pending = $this->collectRefs($paths);
        $seen = [];

        while ($pending) {
            $name = array_pop($pending);

            if (isset($seen[$name])) {
                continue;
            }

            $seen[$name] = true;

            if (isset($schemas[$name])) {
                $pending = array_merge($pending, $this->collectRefs($schemas[$name]));
            }
        }

        return array_keys($seen);
    }

    /**
     * @return list<string>
     */
    private function collectRefs(mixed $node, array &$found = []): array
    {
        if (! is_array($node)) {
            return array_keys($found);
        }

        foreach ($node as $key => $value) {
            if ($key === '$ref' && is_string($value) && str_starts_with($value, '#/components/schemas/')) {
                $found[substr($value, strlen('#/components/schemas/'))] = true;

                continue;
            }

            $this->collectRefs($value, $found);
        }

        return array_keys($found);
    }

    private function exportGraphQl(string $path): void
    {
        foreach (['shop' => false, 'admin' => true] as $surface => $adminScope) {
            $file = "{$path}/{$surface}.graphql";

            File::put($file, SchemaPrinter::doPrint($this->buildGraphQlSchema($adminScope)));

            $this->info("Wrote {$file}");

            $operations = $this->graphQlOperationMap($adminScope);

            $mapFile = "{$path}/graphql-operations-{$surface}.json";

            File::put($mapFile, json_encode($operations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            $this->info(sprintf('Wrote %s (%d operations)', $mapFile, count($operations)));
        }
    }

    /**
     * Map every root GraphQL field to the REST tag of the resource behind it.
     *
     * @return array<string, array{kind: string, tag: string, resource: string}>
     */
    private function graphQlOperationMap(bool $adminScope): array
    {
        $names = $this->laravel->make(ResourceNameCollectionFactoryInterface::class);
        $metadataFactory = $this->laravel->make(ResourceMetadataCollectionFactoryInterface::class);
        $fieldsBuilder = $this->laravel->make(FieldsBuilderEnumInterface::class);

        $map = [];

        foreach ($names->create() as $resourceClass) {
            if (str_starts_with($resourceClass, 'ApiPlatform\\')) {
                continue;
            }

            if (str_contains($resourceClass, '\\Admin\\') !== $adminScope) {
                continue;
            }

            try {
                $collection = $metadataFactory->create($resourceClass);
            } catch (\Throwable) {
                continue;
            }

            $tag = $this->resourceTag($collection);

            foreach ($collection as $resourceMetadata) {
                foreach ($resourceMetadata->getGraphQlOperations() ?? [] as $operation) {
                    $kind = match (true) {
                        $operation instanceof Mutation => 'mutation',
                        $operation instanceof Query => 'query',
                        default => null,
                    };

                    if ($kind === null) {
                        continue;
                    }

                    try {
                        $fields = $this->operationFields($fieldsBuilder, $resourceClass, $operation);
                    } catch (\Throwable) {
                        continue;
                    }

                    foreach (array_keys($fields) as $field) {
                        if ($field === 'node') {
                            continue;
                        }

                        $map[(string) $field] = array_filter([
                            'kind' => $kind,
                            'tag' => $tag,
                            'resource' => $resourceClass,
                            'example' => $kind === 'mutation' ? $this->resourceRequestExample($collection) : null,
                        ], fn ($value) => $value !== null);
                    }
                }
            }
        }

        ksort($map);

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    private function operationFields(FieldsBuilderEnumInterface $fieldsBuilder, string $resourceClass, object $operation): array
    {
        if ($operation instanceof Mutation) {
            return $fieldsBuilder->getMutationFields($resourceClass, $operation);
        }

        $configuration = $operation->getArgs() !== null ? ['args' => $operation->getArgs()] : [];

        return $operation instanceof CollectionOperationInterface
            ? $fieldsBuilder->getCollectionQueryFields($resourceClass, $operation, $configuration)
            : $fieldsBuilder->getItemQueryFields($resourceClass, $operation, $configuration);
    }

    /**
     * The request-body example the resource's REST write operation documents.
     *
     * GraphQL input types are almost entirely nullable, so a skeleton built from the schema
     * alone is empty. The REST example is hand-written per endpoint and therefore carries values
     * that actually mean something.
     *
     * @return array<string, mixed>|null
     */
    private function resourceRequestExample(iterable $collection): ?array
    {
        foreach ($collection as $resourceMetadata) {
            foreach ($resourceMetadata->getOperations() ?? [] as $operation) {
                if (! method_exists($operation, 'getOpenapi')) {
                    continue;
                }

                $openapi = $operation->getOpenapi();

                if (! is_object($openapi) || ! method_exists($openapi, 'getRequestBody')) {
                    continue;
                }

                $content = $openapi->getRequestBody()?->getContent();

                foreach ((array) ($content instanceof \ArrayObject ? $content->getArrayCopy() : $content) as $media) {
                    $example = $media['example'] ?? (is_array($media['examples'] ?? null) ? (reset($media['examples'])['value'] ?? null) : null);

                    if (is_array($example) && $example !== []) {
                        return $example;
                    }
                }
            }
        }

        return null;
    }

    /**
     * First OpenAPI tag declared by any of the resource's REST operations.
     */
    private function resourceTag(iterable $collection): string
    {
        $shortName = '';

        foreach ($collection as $resourceMetadata) {
            $shortName = $shortName ?: (string) $resourceMetadata->getShortName();

            foreach ($resourceMetadata->getOperations() ?? [] as $operation) {
                $openapi = method_exists($operation, 'getOpenapi') ? $operation->getOpenapi() : null;

                if (is_object($openapi) && method_exists($openapi, 'getTags') && $tags = $openapi->getTags()) {
                    return (string) reset($tags);
                }
            }
        }

        return $shortName ?: 'Uncategorised';
    }

    private function buildGraphQlSchema(bool $adminScope): Schema
    {
        foreach (self::DEPS as $id) {
            Container::getInstance()->forgetInstance($id);
        }

        $builder = $adminScope
            ? new ScopedSchemaBuilder(
                $this->laravel->make(ResourceNameCollectionFactoryInterface::class),
                $this->laravel->make(ResourceMetadataCollectionFactoryInterface::class),
                $this->laravel->make(TypesFactoryInterface::class),
                $this->laravel->make(TypesContainerInterface::class),
                $this->laravel->make(FieldsBuilderEnumInterface::class),
                true,
            )
            : new QueryScopedSchemaBuilder(
                $this->laravel->make(ResourceNameCollectionFactoryInterface::class),
                $this->laravel->make(ResourceMetadataCollectionFactoryInterface::class),
                $this->laravel->make(TypesFactoryInterface::class),
                $this->laravel->make(TypesContainerInterface::class),
                $this->laravel->make(FieldsBuilderEnumInterface::class),
            );

        return $builder->getSchema();
    }
}
