<?php

namespace Webkul\BagistoApi\Resolver;

use Webkul\BagistoApi\Models\Product;

/**
 * Custom resolver for Product nested relations (upSells, crossSells, relatedProducts)
 * Ensures the relation methods are called instead of querying all products
 */
class ProductRelationResolver
{
    public function __invoke($object, array $args, $context, $info, $type, $fieldName)
    {
        if (! $object instanceof Product) {
            return null;
        }

        $relationMap = [
            'upSells'          => 'up_sells',
            'crossSells'       => 'cross_sells',
            'relatedProducts'  => 'related_products',
        ];

        $relationMethod = $relationMap[$fieldName] ?? null;

        if (! $relationMethod || ! method_exists($object, $relationMethod)) {
            return null;
        }

        return $object->{$relationMethod}()->get();
    }
}
