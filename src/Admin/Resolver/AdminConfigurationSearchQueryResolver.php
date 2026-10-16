<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationSearch;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSearchProvider;

class AdminConfigurationSearchQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminConfigurationSearchProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminConfigurationSearch
    {
        return $this->provider->build($context['args']['query'] ?? null);
    }
}
