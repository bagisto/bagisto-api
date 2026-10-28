<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerOrderShipment;
use Webkul\BagistoApi\Models\CustomerOrderShipmentItem;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Customer\Models\Customer;

class CustomerOrderShipmentItemProvider implements ProviderInterface
{
    use ClampsPageSize;

    /**
     * Create a new provider instance.
     */
    public function __construct(private readonly Pagination $pagination) {}

    /**
     * Provide the authenticated customer's shipment items, scoped through shipment and order.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = CurrentCustomer::user();
        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if (
            ! $operation instanceof GetCollection
            && ! $operation instanceof QueryCollection
        ) {
            return $this->provideItem($customer, $uriVariables);
        }

        return $this->provideCollection($customer, $context, $uriVariables);
    }

    /**
     * Find one of the customer's shipment items by id.
     */
    private function provideItem(object $customer, array $uriVariables): CustomerOrderShipmentItem
    {
        $id = $uriVariables['id'] ?? null;

        $item = CustomerOrderShipmentItem::query()
            ->whereHas('shipment.order', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id)
                    ->where('customer_type', Customer::class);
            })
            ->find($id);

        if (! $item) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.customer-order-shipment.item-not-found', ['id' => $id])
            );
        }

        return $item;
    }

    /**
     * Paginate the customer's shipment items with a clamped page size.
     */
    private function provideCollection(object $customer, array $context, array $uriVariables = []): Paginator
    {
        $args = $context['args'] ?? [];
        $perPage = $this->clampPageSize((int) ($args['first'] ?? 30));
        $offset = 0;

        if ($after = $args['after'] ?? null) {
            $decoded = base64_decode($after, true);
            $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        }

        $query = CustomerOrderShipmentItem::query()
            ->whereHas('shipment.order', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id)
                    ->where('customer_type', Customer::class);
            })
            ->orderBy('id', 'desc');

        if ($parentShipmentId = $this->parentShipmentId($context, $uriVariables)) {
            $query->where('shipment_id', $parentShipmentId);
        }

        $total = (clone $query)->count();
        $items = $query->offset($offset)->limit($perPage)->get();
        $page = $total > 0 ? (int) floor($offset / $perPage) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator($items, $total, $perPage, $page, ['path' => request()->url()])
        );
    }

    /**
     * The parent shipment id when the collection is nested under a shipment.
     */
    private function parentShipmentId(array $context, array $uriVariables): ?int
    {
        $isNested = ($context['linkClass'] ?? null) === CustomerOrderShipment::class
            && ($context['linkProperty'] ?? null) === 'items';

        if (
            ! $isNested
            || ! isset($uriVariables['id'])
        ) {
            return null;
        }

        return (int) $uriVariables['id'];
    }
}
