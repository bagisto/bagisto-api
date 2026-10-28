<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Models\Page;
use Webkul\Marketing\Repositories\URLRewriteRepository;

/**
 * Finds a CMS page by URL key the way the storefront PageController does: assigned to the
 * current channel, and following a CMS URL rewrite when the key has changed.
 */
trait FindsPageByUrlKey
{
    protected function findPageByUrlKey(string $urlKey): ?Page
    {
        $page = $this->pageWithUrlKey($urlKey);

        if ($page) {
            return $page;
        }

        $rewrite = app(URLRewriteRepository::class)->findOneWhere([
            'entity_type' => 'cms_page',
            'request_path' => $urlKey,
            'locale' => app()->getLocale(),
        ]);

        return $rewrite ? $this->pageWithUrlKey(trim((string) $rewrite->target_path, '/')) : null;
    }

    private function pageWithUrlKey(string $urlKey): ?Page
    {
        return Page::query()
            ->with('translations')
            ->whereHas('translations', fn ($query) => $query->where('url_key', $urlKey))
            ->first();
    }
}
