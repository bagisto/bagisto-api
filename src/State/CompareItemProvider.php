<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Models\CompareItem;
use Illuminate\Support\Facades\Auth;

/**
 * CompareItemProvider - Handles retrieval of compare items for authenticated customers
 *
 * Filters compare items by current customer with pagination support
 */
class CompareItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Retrieve compare items for the authenticated customer
     *
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return object|array|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $args = $context['args'] ?? [];
        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $defaultPerPage = 30;

        // Determine page size
        if ($first !== null) {
            $perPage = $first;
        } elseif ($last !== null) {
            $perPage = $last;
        } else {
            $perPage = $defaultPerPage;
        }

        $query = CompareItem::where('customer_id', $customer->id)
            ->with(['product', 'customer'])
            ->orderBy('id', 'asc');

        // Handle cursor-based pagination
        if ($after) {
            $afterId = (int) base64_decode($after);
            $query->where('id', '>', $afterId);
        } elseif ($before) {
            $beforeId = (int) base64_decode($before);
            $query->where('id', '<', $beforeId);
            // For 'before', reverse order, paginate, then reverse results
            $query->orderBy('id', 'desc');
            $laravelPaginator = $query->paginate($perPage);

            // Reverse items to maintain proper cursor order
            $items = $laravelPaginator->items();
            $items = array_reverse($items);

            return new Paginator(
                $laravelPaginator,
                (int) $laravelPaginator->currentPage(),
                $perPage,
                $laravelPaginator->lastPage(),
                $laravelPaginator->total(),
            );
        }

        $laravelPaginator = $query->paginate($perPage);

        return new Paginator(
            $laravelPaginator,
            (int) $laravelPaginator->currentPage(),
            $perPage,
            $laravelPaginator->lastPage(),
            $laravelPaginator->total(),
        );
    }
}
