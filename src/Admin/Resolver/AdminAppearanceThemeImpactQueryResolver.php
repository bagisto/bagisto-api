<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use ApiPlatform\Metadata\Get;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceThemeImpact;
use Webkul\BagistoApi\Admin\State\AdminAppearanceThemeImpactProvider;

class AdminAppearanceThemeImpactQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminAppearanceThemeImpactProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminAppearanceThemeImpact
    {
        return $this->provider->provide(new Get, [], $context);
    }
}
