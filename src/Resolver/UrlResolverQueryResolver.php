<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Models\UrlResolver;
use Webkul\BagistoApi\State\UrlResolverProvider;

class UrlResolverQueryResolver implements QueryItemResolverInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(protected UrlResolverProvider $provider) {}

    /**
     * Resolve the path given as the query argument.
     */
    public function __invoke(?object $item, array $context): UrlResolver
    {
        return $this->provider->resolve($context['args']['path'] ?? null);
    }
}
