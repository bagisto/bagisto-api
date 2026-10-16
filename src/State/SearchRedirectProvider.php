<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\SearchRedirect;
use Webkul\Marketing\Repositories\SearchTermRepository;

/**
 * Mirrors the redirect lookup at the start of the storefront SearchController::index.
 */
class SearchRedirectProvider implements ProviderInterface
{
    public function __construct(protected SearchTermRepository $searchTermRepository) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [$this->find(request()->query('query'))];
    }

    public function find(mixed $query): SearchRedirect
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

        return $result;
    }
}
