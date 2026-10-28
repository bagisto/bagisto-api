<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\Marketing\Jobs\UpdateCreateSearchTerm;
use Webkul\Marketing\Repositories\SearchSynonymRepository;

/**
 * Storefront search behaviour: synonyms widen the name match, and a plain search is recorded as a search term.
 */
trait HandlesSearchTerms
{
    /**
     * @return array<int, string>
     */
    protected function searchSynonyms(string $query): array
    {
        return array_values(array_filter(array_map(
            'trim',
            app(SearchSynonymRepository::class)->getSynonymsByQuery($query)
        )));
    }

    protected function recordSearchTerm(string $query, int $results): void
    {
        UpdateCreateSearchTerm::dispatch([
            'term' => $query,
            'results' => $results,
            'channel_id' => core()->getCurrentChannel()->id,
            'locale' => app()->getLocale(),
        ]);
    }
}
