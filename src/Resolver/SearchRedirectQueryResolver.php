<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Models\SearchRedirect;
use Webkul\BagistoApi\State\SearchRedirectProvider;

class SearchRedirectQueryResolver implements QueryItemResolverInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(protected SearchRedirectProvider $provider) {}

    /**
     * Resolve the redirect for the term given as the query argument.
     */
    public function __invoke(?object $item, array $context): SearchRedirect
    {
        return $this->provider->find($context['args']['query'] ?? null, $context['args']['suggest'] ?? null);
    }
}
