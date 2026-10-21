<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerOrderShipment;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;
use Webkul\Customer\Models\Customer;

class CustomerOrderShipmentProvider implements ProviderInterface
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
     * Provide customer shipments for collection or single-item operations.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = Auth::guard('sanctum')->user();

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
     * Return a single shipment owned by the customer (via order relationship).
     */
    private function provideItem(object $customer, array $uriVariables): CustomerOrderShipment
    {
        $id = $uriVariables['id'] ?? null;

        if (! $id) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.customer-order-shipment.id-required'));
        }

        $shipment = CustomerOrderShipment::whereHas('order', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id)
                ->where('customer_type', Customer::class);
        })->with(['items', 'shippingAddress', 'order'])->find($id);

        if (! $shipment) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.customer-order-shipment.not-found', ['id' => $id])
            );
        }

        return $shipment;
    }

    /**
     * Return a paginated collection of shipments owned by the customer.
     */
    private function provideCollection(object $customer, array $context): Paginator
    {
        $args = $context['args'] ?? [];
        $filters = $context['filters'] ?? [];
        $request = request();

        $query = CustomerOrderShipment::whereHas('order', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id)
                ->where('customer_type', Customer::class);
        })->with(['items', 'shippingAddress', 'order']);

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

        $status = $args['status']
            ?? $filters['status']
            ?? $request->query('status');
        if (
            $status !== null
            && $status !== ''
        ) {
            $query->where('status', (string) $status);
        }

        $requested = $this->requestedCursorLimit($args, 10);
        $limit = $requested > 0 ? $this->clampPageSize($requested) : 0;

        $query->orderBy('id', 'desc');

        return new Paginator($this->paginateCursorWindow($query, $args, $limit));
    }
}
