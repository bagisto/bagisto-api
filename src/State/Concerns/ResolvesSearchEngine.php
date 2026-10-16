<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\Product\Repositories\ElasticSearchRepository;
use Webkul\Product\Repositories\ProductRepository;

/**
 * Storefront search engine selection, mirroring Shop\Http\Controllers\API\ProductController::index.
 */
trait ResolvesSearchEngine
{
    /**
     * Upper bound of Elasticsearch hits read for one search (the default index max_result_window).
     */
    protected int $elasticMaxHits = 10000;

    /**
     * The engine the storefront searches with: `database`, or the elastic storefront mode.
     */
    protected function storefrontSearchEngine(): string
    {
        if (core()->getConfigData('catalog.products.search.engine') !== 'elastic') {
            return 'database';
        }

        return core()->getConfigData('catalog.products.search.storefront_mode') ?: 'database';
    }

    /**
     * The "did you mean" term for the query; null on the database engine or when Elasticsearch is unreachable.
     */
    protected function searchSuggestion(?string $query, mixed $suggest = null): ?string
    {
        if (
            ! is_string($query)
            || trim($query) === ''
            || $this->suggestionDisabled($suggest)
        ) {
            return null;
        }

        $searchEngine = $this->storefrontSearchEngine();

        if ($searchEngine !== 'elastic') {
            return null;
        }

        try {
            return app(ProductRepository::class)->setSearchEngine($searchEngine)->getSuggestions($query);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Product ids Elasticsearch matches for the query, or null when the elastic engine is off or unreachable.
     *
     * @return array<int, int>|null
     */
    protected function elasticProductIds(string $query): ?array
    {
        if ($this->storefrontSearchEngine() !== 'elastic') {
            return null;
        }

        try {
            $indices = app(ElasticSearchRepository::class)->search([
                'query' => $query,
                'channel_id' => core()->getCurrentChannel()->id,
                'status' => 1,
                'visible_individually' => 1,
            ], [
                'from' => 0,
                'limit' => $this->elasticMaxHits,
                'sort' => '_score',
                'order' => 'desc',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        return array_map('intval', $indices['ids'] ?? []);
    }

    /**
     * Whether the client opted out of the suggestion, as core's `suggest=0`.
     */
    protected function suggestionDisabled(mixed $suggest): bool
    {
        return $suggest === false || $suggest === 0 || $suggest === '0' || $suggest === 'false';
    }
}
