<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Models\Wishlist;

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

        $perPage = $first ?? $last ?? 30;
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

        $query = Wishlist::where('customer_id', $customer->id)
            ->where('channel_id', core()->getCurrentChannel()->id)
            ->with(['product', 'customer', 'channel'])
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
