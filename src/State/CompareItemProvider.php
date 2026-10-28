<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Models\CompareItem;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;
use Webkul\BagistoApi\Support\CurrentCustomer;

class CompareItemProvider implements ProviderInterface
{
    use ClampsPageSize;
    use ResolvesCursorWindow;

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

        $query = CompareItem::where('customer_id', $customer->id)
            ->with(['product' => fn ($query) => Product::withReviewSummary($query), 'customer'])
            ->orderBy('id', 'asc');

        return new Paginator($this->paginateCursorWindow($query, $args, $limit));
    }
}
