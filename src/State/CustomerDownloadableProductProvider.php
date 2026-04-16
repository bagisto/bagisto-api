<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerDownloadableProduct;

/**
 * CustomerDownloadableProductProvider — Retrieves downloadable product purchases for the authenticated customer
 *
 * Supports cursor-based pagination and status filtering.
 * All queries are scoped to the current customer for multi-tenant isolation.
 */
class CustomerDownloadableProductProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Provide customer downloadable products for collection or single-item operations
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        /** Single item — GET /api/shop/customer-downloadable-products/{id} */
        if (! $operation instanceof GetCollection && ! ($operation instanceof \ApiPlatform\Metadata\GraphQl\QueryCollection)) {
            return $this->provideItem($customer, $uriVariables);
        }

        return $this->provideCollection($customer, $context);
    }

    /**
     * Return a single downloadable product purchase owned by the customer
     */
    private function provideItem(object $customer, array $uriVariables): CustomerDownloadableProduct
    {
        $id = $uriVariables['id'] ?? null;

        if (! $id) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.customer-downloadable-product.id-required'));
        }

        $item = CustomerDownloadableProduct::where('customer_id', $customer->id)
            ->with(['order'])
            ->find($id);

        if (! $item) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.customer-downloadable-product.not-found', ['id' => $id])
            );
        }

        return $item;
    }

    /**
     * Return a paginated collection of downloadable product purchases owned by the customer
     */
    private function provideCollection(object $customer, array $context): Paginator
    {
        $args = $context['args'] ?? [];
        $filters = $context['filters'] ?? [];

        $query = CustomerDownloadableProduct::where('customer_id', $customer->id)
            ->with(['order']);

        /** Apply optional status filter */
        $status = $args['status'] ?? $filters['status'] ?? null;
        if ($status !== null) {
            $query->where('status', (string) $status);
        }

        /** Cursor-based pagination (offset-based cursors from API Platform) */
        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $perPage = $first ?? $last ?? 10;
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

        $query->orderBy('id', 'desc');

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
