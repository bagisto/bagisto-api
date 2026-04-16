<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use Webkul\BagistoApi\Models\Page;
use Webkul\CMS\Repositories\PageRepository;

/**
 * Custom resolver for fetching CMS pages by URL key.
 */
class PageByUrlKeyResolver implements QueryCollectionResolverInterface
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly PageRepository $pageRepository
    ) {}

    /**
     * Return page(s) filtered by URL key
     */
    public function __invoke(?iterable $collection, array $context): iterable
    {
        $urlKey = $context['args']['urlKey'] ?? null;

        if (! $urlKey) {
            return [];
        }

        $page = Page::query()
            ->whereHas('translations', function ($q) use ($urlKey) {
                $q->where('url_key', $urlKey);
            })
            ->first();

        return $page ? [$page] : [];
    }
}
