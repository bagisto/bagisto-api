<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use ApiPlatform\Metadata\Get;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceTheme;
use Webkul\BagistoApi\Admin\State\AdminAppearanceThemeItemProvider;

/**
 * A GraphQL item query carrying its own args resolves through a resolver, not the
 * provider alone.
 */
class AdminAppearanceThemeQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminAppearanceThemeItemProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminAppearanceTheme
    {
        return $this->provider->provide(new Get, [], $context);
    }
}
