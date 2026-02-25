<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
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

        $order = CustomerOrder::with(['items', 'addresses', 'payment', 'shipments.items', 'shipments.shippingAddress'])
            ->where('customer_id', $customer->id)
            ->where('customer_type', Customer::class)
            ->find($id);

        if (! $order) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.customer-order.not-found', ['id' => $id])
            );
        }

        return $order;
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

        /** Cursor-based pagination */
        $first  = isset($args['first']) ? (int) $args['first'] : null;
        $last   = isset($args['last']) ? (int) $args['last'] : null;
        $after  = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $perPage = $first ?? $last ?? 10;

        $query->orderBy('id', 'desc');

        if ($after) {
            $afterId = (int) base64_decode($after);
            $query->where('id', '<', $afterId);
        } elseif ($before) {
            $beforeId = (int) base64_decode($before);
            $query->where('id', '>', $beforeId);
            $query->orderBy('id', 'asc');
        }

        $laravelPaginator = $query->paginate($perPage);

        return new Paginator($laravelPaginator);
    }
}
