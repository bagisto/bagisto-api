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
use Webkul\BagistoApi\Models\CustomerInvoice;
use Webkul\Customer\Models\Customer;

/**
 * CustomerInvoiceProvider — Retrieves invoices belonging to the authenticated customer
 *
 * Scopes all queries through the order relationship to ensure customer isolation.
 * Supports cursor-based pagination, orderId and state filtering.
 */
class CustomerInvoiceProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Provide customer invoices for collection or single-item operations
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        /** Single item — GET /api/shop/customer-invoices/{id} */
        if (! $operation instanceof GetCollection && ! ($operation instanceof \ApiPlatform\Metadata\GraphQl\QueryCollection)) {
            return $this->provideItem($customer, $uriVariables);
        }

        return $this->provideCollection($customer, $context);
    }

    /**
     * Return a single invoice owned by the customer (via order relationship)
     */
    private function provideItem(object $customer, array $uriVariables): CustomerInvoice
    {
        $id = $uriVariables['id'] ?? null;

        if (! $id) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.customer-invoice.id-required'));
        }

        $invoice = CustomerInvoice::whereHas('order', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id)
                ->where('customer_type', Customer::class);
        })->with(['items', 'addresses', 'order'])->find($id);

        if (! $invoice) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.customer-invoice.not-found', ['id' => $id])
            );
        }

        return $invoice;
    }

    /**
     * Return a paginated collection of invoices owned by the customer
     */
    private function provideCollection(object $customer, array $context): Paginator
    {
        $args = $context['args'] ?? [];
        $filters = $context['filters'] ?? [];

        $query = CustomerInvoice::whereHas('order', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
                ->where('customer_type', Customer::class);
        })->with(['items', 'addresses', 'order']);

        /** Apply optional order ID filter */
        $orderId = $args['orderId'] ?? $filters['orderId'] ?? null;
        if ($orderId !== null) {
            $query->where('order_id', (int) $orderId);
        }

        /** Apply optional state filter */
        $state = $args['state'] ?? $filters['state'] ?? null;
        if ($state !== null) {
            $query->where('state', (string) $state);
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
