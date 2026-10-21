<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webkul\BagistoApi\Models\CountryState;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;

class CountryStateCollectionProvider implements ProviderInterface
{
    use ResolvesCursorWindow;

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly Pagination $pagination,
    ) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $countryId = $uriVariables['country_id'] ?? $uriVariables['countryId'] ?? null;

        $args = $context['args'] ?? [];

        if (
            ! $countryId
            && isset($args['countryId'])
        ) {
            $countryId = (int) $args['countryId'];
        }

        if (! $countryId) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.country-state.country-id-required')
            );
        }

        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;

        $query = CountryState::where('country_id', $countryId)
            ->with('translations')
            ->orderBy('id', 'asc');

        $total = (clone $query)->count();

        [$offset, $limit] = $this->resolveCursorWindow($args, $total, max(0, $first ?? $last ?? 10));

        $items = $limit > 0
            ? $query->offset($offset)->limit($limit)->get()
            : $query->getModel()->newCollection();

        $perPage = max(1, $limit);
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
