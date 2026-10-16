<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Models\UrlResolver;
use Webkul\BagistoApi\State\UrlResolverProvider;

class UrlResolverQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected UrlResolverProvider $provider) {}

    public function __invoke(?object $item, array $context): UrlResolver
    {
        return $this->provider->resolve($context['args']['path'] ?? null);
    }
}
