<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
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

        $offset = 0;

        if ($after) {
            $decoded = base64_decode($after, true);
            $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        }

        if ($before) {
            $decoded = base64_decode($before, true);
            $cursor = ctype_digit((string) $decoded) ? (int) $decoded : 0;
            $offset = max(0, $cursor - $perPage);
        }

        $query = CountryState::where('country_id', $countryId)
            ->with('translations')
            ->orderBy('id', 'asc');

        $total = (clone $query)->count();

        if ($offset > $total) {
            $offset = max(0, $total - $perPage);
        }

        $items = $query
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $currentPage = $total > 0 ? (int) floor($offset / $perPage) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                ['path' => request()->url()]
            )
        );
    }
}
