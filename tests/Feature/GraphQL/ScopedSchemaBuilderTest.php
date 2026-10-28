<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use ApiPlatform\GraphQl\Type\FieldsBuilderEnumInterface;
use ApiPlatform\GraphQl\Type\TypesContainerInterface;
use ApiPlatform\GraphQl\Type\TypesFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Webkul\BagistoApi\GraphQl\ScopedSchemaBuilder;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class ScopedSchemaBuilderTest extends GraphQLTestCase
{
    /**
     * Test storefront scoped schema exposes shop ops and hides admin ops.
     */
    public function test_storefront_scoped_schema_exposes_shop_ops_and_hides_admin_ops(): void
    {
        $fields = $this->buildScopedQueryFieldNames(false);

        expect($fields)->toContain('products');
        expect($fields)->not->toContain('adminOrders');
    }

    /**
     * Test admin scoped schema exposes admin ops and hides shop ops.
     */
    public function test_admin_scoped_schema_exposes_admin_ops_and_hides_shop_ops(): void
    {
        $fields = $this->buildScopedQueryFieldNames(true);

        expect($fields)->toContain('adminOrders');
        expect($fields)->not->toContain('products');
    }

    /**
     * Build scoped query field names.
     */
    private function buildScopedQueryFieldNames(bool $adminScope): array
    {
        $builder = new ScopedSchemaBuilder(
            app(ResourceNameCollectionFactoryInterface::class),
            app(ResourceMetadataCollectionFactoryInterface::class),
            app(TypesFactoryInterface::class),
            app(TypesContainerInterface::class),
            app(FieldsBuilderEnumInterface::class),
            $adminScope,
        );

        return array_keys($builder->getSchema()->getQueryType()->getFields());
    }
}
