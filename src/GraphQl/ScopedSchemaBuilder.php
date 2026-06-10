<?php

declare(strict_types=1);

namespace Webkul\BagistoApi\GraphQl;

use ApiPlatform\GraphQl\Type\FieldsBuilderEnumInterface;
use ApiPlatform\GraphQl\Type\SchemaBuilder;
use ApiPlatform\GraphQl\Type\SchemaBuilderInterface;
use ApiPlatform\GraphQl\Type\TypesContainerInterface;
use ApiPlatform\GraphQl\Type\TypesFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;
use GraphQL\Type\Schema;

/**
 * Builds a GraphQL schema scoped to ONE API surface (shop OR admin).
 *
 * The default API Platform SchemaBuilder iterates every registered #[ApiResource]
 * (~261 here) and builds query/mutation field definitions for all of them on every
 * request. Because the storefront (`/api/graphql`) and the admin (`/api/admin/graphql`)
 * endpoints share one builder, each endpoint pays to build the OTHER surface's ~130
 * resources too — roughly doubling the per-request schema-build cost.
 *
 * This builder wraps the real ResourceNameCollectionFactory with a namespace filter
 * (admin resources live under a `\Admin\` namespace segment; framework error resources
 * stay in both schemas) so each endpoint only builds its own surface. It reuses the
 * shared singleton TypesContainer / TypesFactory / FieldsBuilder so lazily-resolved
 * GraphQL types still resolve identically to the unscoped builder.
 */
final class ScopedSchemaBuilder implements SchemaBuilderInterface
{
    private SchemaBuilder $inner;

    public function __construct(
        ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory,
        ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        TypesFactoryInterface $typesFactory,
        TypesContainerInterface $typesContainer,
        FieldsBuilderEnumInterface $fieldsBuilder,
        bool $adminScope,
    ) {
        $this->inner = new SchemaBuilder(
            $this->scopedNameFactory($resourceNameCollectionFactory, $adminScope),
            $resourceMetadataCollectionFactory,
            $typesFactory,
            $typesContainer,
            $fieldsBuilder,
        );
    }

    public function getSchema(): Schema
    {
        return $this->inner->getSchema();
    }

    private function scopedNameFactory(
        ResourceNameCollectionFactoryInterface $inner,
        bool $adminScope,
    ): ResourceNameCollectionFactoryInterface {
        return new class($inner, $adminScope) implements ResourceNameCollectionFactoryInterface
        {
            public function __construct(
                private readonly ResourceNameCollectionFactoryInterface $inner,
                private readonly bool $adminScope,
            ) {}

            public function create(): ResourceNameCollection
            {
                $keep = [];

                foreach ($this->inner->create() as $resourceClass) {
                    // Framework resources (Error / ValidationError) belong in every schema.
                    if (str_starts_with($resourceClass, 'ApiPlatform\\')) {
                        $keep[] = $resourceClass;

                        continue;
                    }

                    $isAdminResource = str_contains($resourceClass, '\\Admin\\');

                    if ($this->adminScope === $isAdminResource) {
                        $keep[] = $resourceClass;
                    }
                }

                return new ResourceNameCollection($keep);
            }
        };
    }
}
