<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;

/**
 * WishlistProvider - Handles retrieval of wishlist items for authenticated customers
 *
 * Filters wishlist items by current customer and channel with pagination support
 */
class WishlistProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Retrieve wishlist items for the authenticated customer
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

        if ($first !== null) {
            $perPage = $first;
        } elseif ($last !== null) {
            $perPage = $last;
        } else {
            $perPage = $defaultPerPage;
        }

        $query = Wishlist::where('customer_id', $customer->id)
            ->where('channel_id', core()->getCurrentChannel()->id)
            ->with(['product', 'customer', 'channel'])
            ->orderBy('id', 'asc');

        if ($after) {
            $afterId = (int) base64_decode($after);
            $query->where('id', '>', $afterId);
        } elseif ($before) {
            $beforeId = (int) base64_decode($before);
            $query->where('id', '<', $beforeId);
            
            $query->orderBy('id', 'desc');
            $laravelPaginator = $query->paginate($perPage);

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
