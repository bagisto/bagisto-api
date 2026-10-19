<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use ApiPlatform\Metadata\Get;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceThemeImpact;
use Webkul\BagistoApi\Admin\State\AdminAppearanceThemeImpactProvider;

class AdminAppearanceThemeImpactQueryResolver implements QueryItemResolverInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(protected AdminAppearanceThemeImpactProvider $provider) {}

    /**
     * Handle the invoke magic method.
     */
    public function __invoke(?object $item, array $context): AdminAppearanceThemeImpact
    {
        return $this->provider->provide(new Get, [], $context);
    }
}
