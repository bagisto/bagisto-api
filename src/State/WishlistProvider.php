<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\Models\Wishlist;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;
use Webkul\BagistoApi\State\Concerns\ResolvesListSort;
use Webkul\BagistoApi\Support\CurrentCustomer;

class WishlistProvider implements ProviderInterface
{
    use ClampsPageSize;
    use ResolvesCursorWindow;
    use ResolvesListSort;

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = CurrentCustomer::user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $args = $context['args'] ?? [];
        $requested = $this->requestedCursorLimit($args, 30);
        $limit = $requested > 0 ? $this->clampPageSize($requested) : 0;

        [$sortColumn, $sortDirection] = $this->resolveListSort($args, ['id', 'created_at'], 'asc');

        $query = Wishlist::where('customer_id', $customer->id)
            ->where('channel_id', core()->getCurrentChannel()->id)
            ->with(['product' => fn ($query) => Product::withReviewSummary($query), 'customer', 'channel'])
            ->orderBy($sortColumn, $sortDirection);

        if ($sortColumn !== 'id') {
            $query->orderBy('id', $sortDirection);
        }

        return new Paginator($this->paginateCursorWindow($query, $args, $limit));
    }
}
