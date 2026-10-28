<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use ApiPlatform\Metadata\Get;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceTheme;
use Webkul\BagistoApi\Admin\State\AdminAppearanceThemeItemProvider;

class AdminAppearanceThemeQueryResolver implements QueryItemResolverInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(protected AdminAppearanceThemeItemProvider $provider) {}

    /**
     * Handle the invoke magic method.
     */
    public function __invoke(?object $item, array $context): AdminAppearanceTheme
    {
        return $this->provider->provide(new Get, [], $context);
    }
}
