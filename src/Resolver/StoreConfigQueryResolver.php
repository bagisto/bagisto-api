<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Models\StoreConfig;
use Webkul\BagistoApi\State\StoreConfigProvider;

class StoreConfigQueryResolver implements QueryItemResolverInterface
{
    /**
     * Handle the invoke magic method.
     */
    public function __invoke(?object $item, array $context): StoreConfig
    {
        return StoreConfigProvider::build();
    }
}
