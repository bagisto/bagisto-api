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
use Webkul\BagistoApi\Models\CustomerOrder;
use Webkul\Customer\Models\Customer;

/**
 * CustomerOrderProvider — Retrieves orders belonging to the authenticated customer
 *
 * Supports cursor-based pagination and status filtering.
 * All queries are scoped to the current customer for multi-tenant isolation.
 */
class CustomerOrderProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Provide customer orders for collection or single-item operations
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        /** Single item — GET /api/shop/customer-orders/{id} */
        if (! $operation instanceof GetCollection && ! ($operation instanceof \ApiPlatform\Metadata\GraphQl\QueryCollection)) {
            return $this->provideItem($customer, $uriVariables);
        }

        return $this->provideCollection($customer, $context);
    }

    /**
     * Return a single order owned by the customer
     */
    private function provideItem(object $customer, array $uriVariables): CustomerOrder
    {
        $id = $uriVariables['id'] ?? null;

        if (! $id) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.customer-order.id-required'));
        }

        $orderQuery = CustomerOrder::with(['items', 'addresses', 'payment', 'shipments.items', 'shipments.shippingAddress'])
            ->where('customer_id', $customer->id)
            ->where('customer_type', Customer::class);

        $order = $orderQuery->find($id);

        if (! $order) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.customer-order.not-found', ['id' => $id])
            );
        }

        return $order;
    }

    /**
     * Enable debug dumps only when explicitly requested via header:
     * X-DEBUG-CUSTOMER-ORDER: 1
     * Optional hard-stop at success checkpoint:
     * X-DEBUG-CUSTOMER-ORDER-DD: 1
     */
    private function debugDump(string $checkpoint, array $payload = []): void
    {
        if (! $this->shouldDebugDump()) {
            return;
        }
    }

    private function shouldDebugDump(): bool
    {
        return request()->header('X-DEBUG-CUSTOMER-ORDER') === '1';
    }

    private function shouldDebugDd(): bool
    {
        return request()->header('X-DEBUG-CUSTOMER-ORDER-DD') === '1';
    }

    /**
     * Targeted hard-stop for debugging.
     * Usage: X-DEBUG-CUSTOMER-ORDER-DD-AT: start|auth|id|result
     */
    private function debugDdAt(string $checkpoint, array $payload = []): void
    {
        if (request()->header('X-DEBUG-CUSTOMER-ORDER-DD-AT') !== $checkpoint) {
            return;
        }
    }

    /**
     * Return a paginated collection of orders owned by the customer
     */
    private function provideCollection(object $customer, array $context): Paginator
    {
        $args = $context['args'] ?? [];
        $filters = $context['filters'] ?? [];

        $query = CustomerOrder::with(['items', 'addresses', 'payment', 'shipments.items', 'shipments.shippingAddress'])
            ->where('customer_id', $customer->id)
            ->where('customer_type', Customer::class);

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
