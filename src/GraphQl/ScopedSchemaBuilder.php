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
 * The default API Platform SchemaBuilder iterates every registered #[ApiResource].
 */
final class ScopedSchemaBuilder implements SchemaBuilderInterface
{
    /**
     * The inner.
     */
    private SchemaBuilder $inner;

    /**
     * Create a new instance.
     */
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

    /**
     * Get schema.
     */
    public function getSchema(): Schema
    {
        return $this->inner->getSchema();
    }

    /**
     * Scoped name factory.
     */
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
