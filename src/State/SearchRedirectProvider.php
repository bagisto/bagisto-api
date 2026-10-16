<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\SearchRedirect;
use Webkul\BagistoApi\State\Concerns\ResolvesSearchEngine;
use Webkul\Marketing\Repositories\SearchTermRepository;

/**
 * Mirrors the redirect and suggestion lookups at the start of the storefront SearchController::index.
 */
class SearchRedirectProvider implements ProviderInterface
{
    use ResolvesSearchEngine;

    public function __construct(protected SearchTermRepository $searchTermRepository) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [$this->find(request()->query('query'), request()->query('suggest'))];
    }

    public function find(mixed $query, mixed $suggest = null): SearchRedirect
    {
        if (! is_string($query) || trim($query) === '') {
            throw new InvalidInputException(__('bagistoapi::app.graphql.search.query-required'), 422);
        }

        $searchTerm = $this->searchTermRepository->findOneWhere([
            'term' => $query,
            'channel_id' => core()->getCurrentChannel()->id,
            'locale' => app()->getLocale(),
        ]);

        $result = new SearchRedirect;
        $result->query = $query;
        $result->redirect_url = $searchTerm?->redirect_url ?: null;
        $result->suggestion = $this->searchSuggestion($query, $suggest);

        return $result;
    }
}
