<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\SearchRedirect;
use Webkul\Marketing\Repositories\SearchTermRepository;
use Webkul\Product\Enums\SearchContextEnum;
use Webkul\Product\Repositories\SearchRepository;

class SearchRedirectProvider implements ProviderInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected SearchTermRepository $searchTermRepository,
        protected SearchRepository $searchRepository,
    ) {}

    /**
     * Provide the redirect for the term as a list of one entry.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [$this->find(request()->query('query'), request()->query('suggest'))];
    }

    /**
     * The redirect and suggestion the storefront search page looks up before searching.
     */
    public function find(mixed $query, mixed $suggest = null): SearchRedirect
    {
        if (
            ! is_string($query)
            || trim($query) === ''
        ) {
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
        $result->suggestion = $this->suggestionDisabled($suggest) ? null : $this->suggestion($query);

        return $result;
    }

    /**
     * The "did you mean" term of the storefront search engine, or null when it has none or fails.
     */
    protected function suggestion(string $query): ?string
    {
        try {
            return $this->searchRepository
                ->setSearchContext(SearchContextEnum::STOREFRONT)
                ->getSuggestions($query);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Whether the client opted out of the suggestion, as core's `suggest=0`.
     */
    protected function suggestionDisabled(mixed $suggest): bool
    {
        return in_array($suggest, [false, 0, '0', 'false'], true);
    }
}
