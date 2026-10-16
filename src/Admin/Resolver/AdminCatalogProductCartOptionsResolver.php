<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductCartOptions;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCartOptionsProvider;

class AdminCatalogProductCartOptionsResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminCatalogProductCartOptionsProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminCatalogProductCartOptions
    {
        return $this->provider->build((int) ($context['args']['productId'] ?? 0));
    }
}
