<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\FieldResolverInterface;
use ApiPlatform\GraphQl\Resolver\ResourceFieldResolver;
use GraphQL\Type\Definition\ResolveInfo;
use Webkul\BagistoApi\Models\CustomerOrder;

class CustomerOrderItemsResolver implements FieldResolverInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly ResourceFieldResolver $resourceFieldResolver,
    ) {}

    /**
     * Handle the invoke magic method.
     */
    public function __invoke($source, array $args, mixed $context, ResolveInfo $info): mixed
    {
        $result = ($this->resourceFieldResolver)($source, $args, $context, $info);

        if (
            $source instanceof CustomerOrder
            && $source->items
        ) {
            return $source->items;
        }

        return $result;
    }
}
