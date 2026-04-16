<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\FieldResolverInterface;
use ApiPlatform\GraphQl\Resolver\ResourceFieldResolver;
use GraphQL\Type\Definition\ResolveInfo;
use Webkul\BagistoApi\Models\CustomerOrder;

/**
 * Custom field resolver for CustomerOrder.items
 * Ensures qty fields are always included in nested item responses
 */
class CustomerOrderItemsResolver implements FieldResolverInterface
{
    public function __construct(
        private readonly ResourceFieldResolver $resourceFieldResolver,
    ) {}

    public function __invoke($source, array $args, mixed $context, ResolveInfo $info): mixed
    {
        // Get the items through the default resolver
        $result = ($this->resourceFieldResolver)($source, $args, $context, $info);

        // If source is a CustomerOrder instance, ensure qty fields are accessible
        if ($source instanceof CustomerOrder && $source->items) {
            // Ensure all items have qty fields explicitly set in their attributes
            // This is handled by the FixedSerializerContextBuilder and toArray override
            // Just return the items collection as-is
            return $source->items;
        }

        return $result;
    }
}
