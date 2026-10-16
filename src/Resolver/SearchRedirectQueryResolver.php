<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Models\SearchRedirect;
use Webkul\BagistoApi\State\SearchRedirectProvider;

class SearchRedirectQueryResolver implements QueryItemResolverInterface
{
    public function __construct(private readonly SearchRedirectProvider $provider) {}

    public function __invoke(?object $item, array $context): SearchRedirect
    {
        return $this->provider->find($context['args']['query'] ?? null, $context['args']['suggest'] ?? null);
    }
}
