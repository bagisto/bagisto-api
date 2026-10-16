<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use Webkul\BagistoApi\State\Concerns\FindsPageByUrlKey;

/**
 * Resolves pageByUrlKey: the CMS page with that URL key on the current channel.
 */
class PageByUrlKeyResolver implements QueryCollectionResolverInterface
{
    use FindsPageByUrlKey;

    public function __invoke(?iterable $collection, array $context): iterable
    {
        $urlKey = $context['args']['urlKey'] ?? null;

        $page = is_string($urlKey) && $urlKey !== '' ? $this->findPageByUrlKey($urlKey) : null;

        return $page ? [$page] : [];
    }
}
