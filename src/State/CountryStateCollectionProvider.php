<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webkul\BagistoApi\Models\CountryState;

/**
 * Collection provider for CountryState
 *
 * Provides cursor-based pagination for country states
 * - Subresource: /countries/{country_id}/states (country_id provided via URI)
 * - Direct query: countryStates(countryId: 244) (countryId REQUIRED in args for GraphQL and REST)
 */
class CountryStateCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Handle both 'country_id' (snake_case from URI) and 'countryId' (camelCase from args)
        $countryId = $uriVariables['country_id'] ?? $uriVariables['countryId'] ?? null;

        $args = $context['args'] ?? [];

        // Also check for countryId in GraphQL args (for direct countryStates query)
        if (! $countryId && isset($args['countryId'])) {
            $countryId = (int) $args['countryId'];
        }

        // Enforce: countryId is REQUIRED when querying country states directly
        if (! $countryId) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.country-state.country-id-required')
            );
        }

        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $defaultPerPage = 10;

        // Determine page size
        if ($first !== null) {
            $perPage = $first;
        } elseif ($last !== null) {
            $perPage = $last;
        } else {
            $perPage = $defaultPerPage;
        }

        $query = CountryState::where('country_id', $countryId)
            ->orderBy('id', 'asc');

        // Handle cursor-based pagination
        if ($after) {
            $afterId = (int) base64_decode($after);
            $query->where('id', '>', $afterId);
        } elseif ($before) {
            $beforeId = (int) base64_decode($before);
            $query->where('id', '<', $beforeId);
            // For 'before', we need to reverse order, paginate, then reverse results
            $query->orderBy('id', 'desc');
            $laravelPaginator = $query->paginate($perPage);

            // Reverse the items to maintain proper cursor order
            $items = $laravelPaginator->items();
            $items = array_reverse($items);

            // Load relations
            foreach ($items as $item) {
                $item->load('translations');
            }

            // Create a new paginator with reversed items
            return new Paginator(
                $laravelPaginator,
                (int) $laravelPaginator->currentPage(),
                $perPage,
                $laravelPaginator->lastPage(),
                $laravelPaginator->total(),
            );
        }

        $laravelPaginator = $query->paginate($perPage);

        // Load relations for all items
        foreach ($laravelPaginator->items() as $item) {
            $item->load('translations');
        }

        return new Paginator(
            $laravelPaginator,
            (int) $laravelPaginator->currentPage(),
            $perPage,
            $laravelPaginator->lastPage(),
            $laravelPaginator->total(),
        );
    }
}
