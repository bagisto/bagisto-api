<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Models\StoreConfig;
use Webkul\BagistoApi\State\StoreConfigProvider;

class StoreConfigQueryResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): StoreConfig
    {
        return StoreConfigProvider::build();
    }
}
