<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Models\Theme;
use Webkul\BagistoApi\State\ThemeProvider;

class ThemeQueryResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): Theme
    {
        return ThemeProvider::build();
    }
}
