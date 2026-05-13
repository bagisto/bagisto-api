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
use Webkul\BagistoApi\Models\CustomerOrderShipmentItem;
use Webkul\Customer\Models\Customer;

/**
 * Scopes shipment-items to the authenticated customer via shipment → order.
 *
 * Without this provider the endpoint would expose every customer's shipment items
 * to any caller with a storefront key — a public read on `shipment_items`.
 */
class CustomerOrderShipmentItemProvider implements ProviderInterface
{
    public function __construct(private readonly Pagination $pagination) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = Auth::guard('sanctum')->user();
        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if (! $operation instanceof GetCollection
            && ! ($operation instanceof \ApiPlatform\Metadata\GraphQl\QueryCollection)
        ) {
            return $this->provideItem($customer, $uriVariables);
        }

        return $this->provideCollection($customer, $context);
    }

    private function provideItem(object $customer, array $uriVariables): CustomerOrderShipmentItem
    {
        $id = $uriVariables['id'] ?? null;

        $item = CustomerOrderShipmentItem::query()
            ->whereHas('shipment.order', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id)
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

    private function provideCollection(object $customer, array $context): Paginator
    {
        $args = $context['args'] ?? [];
        $perPage = isset($args['first']) ? (int) $args['first'] : 30;
        $offset = 0;

        if ($after = $args['after'] ?? null) {
            $decoded = base64_decode($after, true);
            $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        }

        $query = CustomerOrderShipmentItem::query()
            ->whereHas('shipment.order', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id)
                    ->where('customer_type', Customer::class);
            })
            ->orderBy('id', 'desc');

        $total = (clone $query)->count();
        $items = $query->offset($offset)->limit($perPage)->get();
        $page = $total > 0 ? (int) floor($offset / $perPage) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator($items, $total, $perPage, $page, ['path' => request()->url()])
        );
    }
}
