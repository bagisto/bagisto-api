<?php

declare(strict_types=1);

namespace Webkul\BagistoApi\GraphQl;

use ApiPlatform\GraphQl\Type\FieldsBuilderEnumInterface;
use ApiPlatform\GraphQl\Type\SchemaBuilder;
use ApiPlatform\GraphQl\Type\SchemaBuilderInterface;
use ApiPlatform\GraphQl\Type\TypesContainerInterface;
use ApiPlatform\GraphQl\Type\TypesFactoryInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use Illuminate\Support\Facades\Cache;

/**
 * Per-query scoped schema builder (storefront).
 * Reads the incoming GraphQL query and builds a schema scoped to ONLY the.
 */
final class QueryScopedSchemaBuilder implements SchemaBuilderInterface
{
    /**
     * The map cache key constant.
     */
    private const MAP_CACHE_KEY = 'bagistoapi.graphql.shop_root_field_map';

    /**
     * The full shop.
     */
    private ScopedSchemaBuilder $fullShop;

    /** @var list<class-string>|null */
    private ?array $frameworkResources = null;

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly TypesFactoryInterface $typesFactory,
        private readonly TypesContainerInterface $typesContainer,
        private readonly FieldsBuilderEnumInterface $fieldsBuilder,
    ) {
        $this->fullShop = new ScopedSchemaBuilder(
            $resourceNameCollectionFactory,
            $resourceMetadataCollectionFactory,
            $typesFactory,
            $typesContainer,
            $fieldsBuilder,
            false,
        );
    }

    /**
     * Get schema.
     */
    public function getSchema(): Schema
    {
        $scope = $this->scopeForRequest();

        if ($scope === null) {
            return $this->fullShop->getSchema();
        }

        return (new SchemaBuilder(
            $this->fixedNameFactory($scope),
            $this->resourceMetadataCollectionFactory,
            $this->typesFactory,
            $this->typesContainer,
            $this->fieldsBuilder,
        ))->getSchema();
    }

    /**
     * Pre-build and cache the root-field -> resource map so the first request
     * after a deploy/cache-clear does not pay the map build. Returns the number.
     */
    public function warmFieldMap(): int
    {
        return count($this->fieldMap());
    }

    /**
     * Resolve the resource scope for the current request's query, or null to
     * use the full storefront schema.
     *
     * @return list<class-string>|null
     */
    private function scopeForRequest(): ?array
    {
        $query = request()->input('query');

        if (
            ! is_string($query)
            || $query === ''
        ) {
            return null;
        }

        try {
            $document = Parser::parse($query, ['noLocation' => true]);
        } catch (\Throwable) {
            return null;
        }

        $rootFields = [];

        foreach ($document->definitions as $definition) {
            if (! $definition instanceof OperationDefinitionNode) {
                return null;
            }

            if ($definition->operation !== 'query') {
                return null;
            }

            foreach ($definition->selectionSet->selections as $selection) {
                if (! $selection instanceof FieldNode) {
                    return null;
                }

                $rootFields[] = $selection->name->value;
            }
        }

        if ($rootFields === []) {
            return null;
        }

        $map = $this->fieldMap();
        $resources = [];

        foreach (array_unique($rootFields) as $field) {
            if (! isset($map[$field])) {
                return null;
            }

            $resources[$map[$field]] = true;
        }

        return array_values(array_merge($this->frameworkResources(), array_keys($resources)));
    }

    /**
     * Field-name -> resource-class map, cached (self-invalidating on resource
     * set changes).
     *
     * @return array<string, class-string>
     */
    private function fieldMap(): array
    {
        return Cache::rememberForever($this->fieldMapCacheKey(), fn () => $this->buildFieldMap());
    }

    /**
     * Field map cache key.
     */
    private function fieldMapCacheKey(): string
    {
        $names = [];

        foreach ($this->resourceNameCollectionFactory->create() as $resourceClass) {
            $names[] = $resourceClass;
        }

        sort($names);

        return self::MAP_CACHE_KEY.':'.md5(implode(',', $names));
    }

    /**
     * @return array<string, class-string>
     */
    private function buildFieldMap(): array
    {
        $map = [];

        foreach ($this->shopResourceNames() as $resourceClass) {
            foreach ($this->queryFieldNames($resourceClass) as $fieldName) {
                $map[$fieldName] = $resourceClass;
            }
        }

        return $map;
    }

    /**
     * Root query field names a resource exposes.
     *
     * @return list<string>
     */
    private function queryFieldNames(string $resourceClass): array
    {
        try {
            $metadataCollection = $this->resourceMetadataCollectionFactory->create($resourceClass);
        } catch (\Throwable) {
            return [];
        }

        $names = [];

        foreach ($metadataCollection as $resourceMetadata) {
            foreach ($resourceMetadata->getGraphQlOperations() ?? [] as $operation) {
                if ($operation instanceof Query) {
                    array_push($names, ...$this->operationFieldNames($resourceClass, $operation));
                }
            }
        }

        return $names;
    }

    /**
     * @return list<string>
     */
    private function operationFieldNames(string $resourceClass, Query $operation): array
    {
        $configuration = $operation->getArgs() !== null ? ['args' => $operation->getArgs()] : [];

        try {
            $fields = $operation instanceof CollectionOperationInterface
                ? $this->fieldsBuilder->getCollectionQueryFields($resourceClass, $operation, $configuration)
                : $this->fieldsBuilder->getItemQueryFields($resourceClass, $operation, $configuration);
        } catch (\Throwable) {
            return [];
        }

        return array_values(array_filter(
            array_map('strval', array_keys($fields)),
            fn (string $fieldName) => $fieldName !== 'node'
        ));
    }

    /**
     * @return list<class-string>
     */
    private function shopResourceNames(): array
    {
        $keep = [];

        foreach ($this->resourceNameCollectionFactory->create() as $resourceClass) {
            if (str_starts_with($resourceClass, 'ApiPlatform\\')) {
                continue;
            }

            if (str_contains($resourceClass, '\\Admin\\')) {
                continue;
            }

            $keep[] = $resourceClass;
        }

        return $keep;
    }

    /**
     * @return list<class-string>
     */
    private function frameworkResources(): array
    {
        if ($this->frameworkResources === null) {
            $this->frameworkResources = [];

            foreach ($this->resourceNameCollectionFactory->create() as $resourceClass) {
                if (str_starts_with($resourceClass, 'ApiPlatform\\')) {
                    $this->frameworkResources[] = $resourceClass;
                }
            }
        }

        return $this->frameworkResources;
    }

    /**
     * @param  list<class-string>  $classes
     */
    private function fixedNameFactory(array $classes): ResourceNameCollectionFactoryInterface
    {
        return new class($classes) implements ResourceNameCollectionFactoryInterface
        {
            public function __construct(private readonly array $classes) {}

            public function create(): ResourceNameCollection
            {
                return new ResourceNameCollection($this->classes);
            }
        };
    }
}
