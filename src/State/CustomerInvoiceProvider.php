<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerInvoice;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Customer\Models\Customer;

class CustomerInvoiceProvider implements ProviderInterface
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
     * Provide customer invoices for collection or single-item operations.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = CurrentCustomer::user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if (
            ! $operation instanceof GetCollection
            && ! ($operation instanceof QueryCollection)
        ) {
            return $this->provideItem($customer, $uriVariables);
        }

        return $this->provideCollection($customer, $context);
    }

    /**
     * Return a single invoice owned by the customer (via order relationship).
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
     * Return a paginated collection of invoices owned by the customer.
     */
    private function provideCollection(object $customer, array $context): Paginator
    {
        $args = $context['args'] ?? [];
        $filters = $context['filters'] ?? [];
        $request = request();

        $query = CustomerInvoice::whereHas('order', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id)
                ->where('customer_type', Customer::class);
        })->with(['items', 'addresses', 'order']);

        $orderId = $args['orderId']
            ?? $filters['orderId']
            ?? $request->query('order_id')
            ?? $request->query('orderId');
        if (
            $orderId !== null
            && $orderId !== ''
        ) {
            $query->where('order_id', (int) $orderId);
        }

        $state = $args['state']
            ?? $filters['state']
            ?? $request->query('state');
        if (
            $state !== null
            && $state !== ''
        ) {
            $query->where('state', (string) $state);
        }

        $requested = $this->requestedCursorLimit($args, 10);
        $limit = $requested > 0 ? $this->clampPageSize($requested) : 0;

        $query->orderBy('id', 'desc');

        return new Paginator($this->paginateCursorWindow($query, $args, $limit));
    }
}
