<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Dto\CustomerOrder\CustomerOrderDetailDto;
use Webkul\BagistoApi\Dto\CustomerOrder\CustomerOrderListDto;
use Webkul\BagistoApi\Dto\CustomerOrder\OrderAddressDto;
use Webkul\BagistoApi\Dto\CustomerOrder\OrderItemDto;
use Webkul\BagistoApi\Dto\CustomerOrder\OrderPaymentDto;
use Webkul\BagistoApi\Dto\CustomerOrder\OrderShipmentDto;
use Webkul\BagistoApi\Dto\CustomerOrder\OrderShipmentItemDto;
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
            return $this->provideItem($customer, $uriVariables, $operation, $context);
        }

        return $this->provideCollection($customer, $context, $operation);
    }

    private function toListDto(CustomerOrder $order): CustomerOrderListDto
    {
        $dto = new CustomerOrderListDto;

        $dto->id = (int) $order->id;
        $dto->increment_id = $order->increment_id;
        $dto->status = $order->status;
        $dto->channel_name = $order->channel_name;
        $dto->customer_email = $order->customer_email;
        $dto->customer_first_name = $order->customer_first_name;
        $dto->customer_last_name = $order->customer_last_name;
        $dto->shipping_method = $order->shipping_method;
        $dto->shipping_title = $order->shipping_title;
        $dto->coupon_code = $order->coupon_code;
        $dto->total_item_count = $order->total_item_count !== null ? (int) $order->total_item_count : null;
        $dto->total_qty_ordered = $order->total_qty_ordered !== null ? (int) $order->total_qty_ordered : null;
        $dto->grand_total = $order->grand_total !== null ? (float) $order->grand_total : null;
        $dto->base_grand_total = $order->base_grand_total !== null ? (float) $order->base_grand_total : null;
        $dto->sub_total = $order->sub_total !== null ? (float) $order->sub_total : null;
        $dto->base_sub_total = $order->base_sub_total !== null ? (float) $order->base_sub_total : null;
        $dto->tax_amount = $order->tax_amount !== null ? (float) $order->tax_amount : null;
        $dto->shipping_amount = $order->shipping_amount !== null ? (float) $order->shipping_amount : null;
        $dto->discount_amount = $order->discount_amount !== null ? (float) $order->discount_amount : null;
        $dto->base_currency_code = $order->base_currency_code;
        $dto->order_currency_code = $order->order_currency_code;
        $dto->created_at = $order->created_at?->toIso8601String();
        $dto->updated_at = $order->updated_at?->toIso8601String();

        return $dto;
    }

    /**
     * Return a single order owned by the customer
     */
    private function provideItem(object $customer, array $uriVariables, Operation $operation, array $context = []): CustomerOrder|CustomerOrderDetailDto
    {
        $id = $uriVariables['id'] ?? null;

        if (! $id) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.customer-order.id-required'));
        }

        $orderQuery = CustomerOrder::with(['items', 'addresses', 'payment', 'shipments.items'])
            ->where('customer_id', $customer->id)
            ->where('customer_type', Customer::class);

        $order = $orderQuery->find($id);

        if (! $order) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.customer-order.not-found', ['id' => $id])
            );
        }

        $isGraphQL = ! empty($context['graphql_operation_name']);

        if ($operation instanceof \ApiPlatform\Metadata\Get && ! $isGraphQL) {
            return $this->toDetailDto($order);
        }

        return $order;
    }

    private function toDetailDto(CustomerOrder $order): CustomerOrderDetailDto
    {
        $dto = new CustomerOrderDetailDto;

        $dto->id = (int) $order->id;
        $dto->increment_id = $order->increment_id;
        $dto->status = $order->status;
        $dto->channel_name = $order->channel_name;
        $dto->is_guest = $order->is_guest === null ? null : (bool) $order->is_guest;
        $dto->customer_email = $order->customer_email;
        $dto->customer_first_name = $order->customer_first_name;
        $dto->customer_last_name = $order->customer_last_name;
        $dto->customer_full_name = trim(($order->customer_first_name ?? '').' '.($order->customer_last_name ?? '')) ?: null;
        $dto->shipping_method = $order->shipping_method;
        $dto->shipping_title = $order->shipping_title;
        $dto->shipping_description = $order->shipping_description;
        $dto->coupon_code = $order->coupon_code;
        $dto->is_gift = $order->is_gift === null ? null : (bool) $order->is_gift;
        $dto->total_item_count = $order->total_item_count !== null ? (int) $order->total_item_count : null;
        $dto->total_qty_ordered = $order->total_qty_ordered !== null ? (int) $order->total_qty_ordered : null;
        $dto->base_currency_code = $order->base_currency_code;
        $dto->channel_currency_code = $order->channel_currency_code;
        $dto->order_currency_code = $order->order_currency_code;

        foreach ([
            'grand_total', 'base_grand_total', 'grand_total_invoiced', 'base_grand_total_invoiced',
            'grand_total_refunded', 'base_grand_total_refunded',
            'sub_total', 'base_sub_total', 'sub_total_invoiced', 'base_sub_total_invoiced',
            'sub_total_refunded', 'base_sub_total_refunded',
            'discount_percent', 'discount_amount', 'base_discount_amount',
            'discount_invoiced', 'base_discount_invoiced',
            'discount_refunded', 'base_discount_refunded',
            'tax_amount', 'base_tax_amount', 'tax_amount_invoiced', 'base_tax_amount_invoiced',
            'tax_amount_refunded', 'base_tax_amount_refunded',
            'shipping_amount', 'base_shipping_amount', 'shipping_invoiced', 'base_shipping_invoiced',
            'shipping_refunded', 'base_shipping_refunded',
            'shipping_discount_amount', 'base_shipping_discount_amount',
            'shipping_tax_amount', 'base_shipping_tax_amount',
            'shipping_tax_refunded', 'base_shipping_tax_refunded',
            'sub_total_incl_tax', 'base_sub_total_incl_tax',
            'shipping_amount_incl_tax', 'base_shipping_amount_incl_tax',
        ] as $field) {
            $dto->{$field} = $order->{$field} !== null ? (float) $order->{$field} : null;
        }

        $dto->customer_id = $order->customer_id !== null ? (int) $order->customer_id : null;
        $dto->channel_id = $order->channel_id !== null ? (int) $order->channel_id : null;
        $dto->cart_id = $order->cart_id !== null ? (int) $order->cart_id : null;
        $dto->applied_cart_rule_ids = $order->applied_cart_rule_ids;
        $dto->created_at = $order->created_at?->toIso8601String();
        $dto->updated_at = $order->updated_at?->toIso8601String();

        $dto->items = $order->items
            ->map(fn ($item) => $this->toItemDto($item))
            ->all();

        $dto->addresses = $order->addresses
            ->map(fn ($a) => $this->toAddressDto($a))
            ->all();

        if ($order->payment) {
            $dto->payment = $this->toPaymentDto($order->payment);
        }

        $dto->shipments = $order->shipments
            ->map(fn ($s) => $this->toShipmentDto($s))
            ->all();

        return $dto;
    }

    private function toItemDto($item): OrderItemDto
    {
        $dto = new OrderItemDto;
        $dto->id = (int) $item->id;
        $dto->sku = $item->sku;
        $dto->type = $item->type;
        $dto->name = $item->name;
        $dto->product_id = $item->product_id !== null ? (int) $item->product_id : null;
        $dto->product_type = $item->product_type;
        $dto->qty_ordered = $item->qty_ordered !== null ? (int) $item->qty_ordered : null;
        $dto->qty_shipped = $item->qty_shipped !== null ? (int) $item->qty_shipped : null;
        $dto->qty_invoiced = $item->qty_invoiced !== null ? (int) $item->qty_invoiced : null;
        $dto->qty_canceled = $item->qty_canceled !== null ? (int) $item->qty_canceled : null;
        $dto->qty_refunded = $item->qty_refunded !== null ? (int) $item->qty_refunded : null;
        $dto->price = $item->price !== null ? (float) $item->price : null;
        $dto->base_price = $item->base_price !== null ? (float) $item->base_price : null;
        $dto->total = $item->total !== null ? (float) $item->total : null;
        $dto->base_total = $item->base_total !== null ? (float) $item->base_total : null;
        $dto->discount_percent = $item->discount_percent !== null ? (float) $item->discount_percent : null;
        $dto->discount_amount = $item->discount_amount !== null ? (float) $item->discount_amount : null;
        $dto->tax_percent = $item->tax_percent !== null ? (float) $item->tax_percent : null;
        $dto->tax_amount = $item->tax_amount !== null ? (float) $item->tax_amount : null;
        $dto->price_incl_tax = $item->price_incl_tax !== null ? (float) $item->price_incl_tax : null;
        $dto->total_incl_tax = $item->total_incl_tax !== null ? (float) $item->total_incl_tax : null;

        return $dto;
    }

    private function toAddressDto($a): OrderAddressDto
    {
        $dto = new OrderAddressDto;
        $dto->id = (int) $a->id;
        $dto->address_type = $a->address_type;
        $dto->first_name = $a->first_name;
        $dto->last_name = $a->last_name;
        $dto->gender = $a->gender;
        $dto->company_name = $a->company_name;
        $dto->address = $a->address;
        $dto->city = $a->city;
        $dto->state = $a->state;
        $dto->country = $a->country;
        $dto->postcode = $a->postcode;
        $dto->email = $a->email;
        $dto->phone = $a->phone;
        $dto->vat_id = $a->vat_id;

        return $dto;
    }

    private function toPaymentDto($p): OrderPaymentDto
    {
        $dto = new OrderPaymentDto;
        $dto->id = (int) $p->id;
        $dto->method = $p->method;
        $dto->method_title = $p->method_title;

        return $dto;
    }

    private function toShipmentDto($s): OrderShipmentDto
    {
        $dto = new OrderShipmentDto;
        $dto->id = (int) $s->id;
        $dto->shipping_number = '#'.$s->id;
        $dto->carrier_title = $s->carrier_title ?? null;
        $dto->carrier_code = $s->carrier_code ?? null;
        $dto->track_number = $s->track_number ?? null;
        $dto->total_qty = $s->total_qty !== null ? (int) $s->total_qty : null;
        $dto->total_weight = $s->total_weight !== null ? (float) $s->total_weight : null;
        $dto->email_sent = $s->email_sent === null ? null : (bool) $s->email_sent;
        $dto->created_at = $s->created_at?->toIso8601String();
        $dto->updated_at = $s->updated_at?->toIso8601String();

        if ($s->relationLoaded('items')) {
            $dto->items = $s->items->map(function ($si) {
                $idto = new OrderShipmentItemDto;
                $idto->id = (int) $si->id;
                $idto->order_item_id = $si->order_item_id !== null ? (int) $si->order_item_id : null;
                $idto->qty = $si->qty !== null ? (int) $si->qty : null;
                $idto->weight = $si->weight !== null ? (float) $si->weight : null;

                return $idto;
            })->all();
        }

        return $dto;
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
    private function provideCollection(object $customer, array $context, Operation $operation): Paginator
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

        $isGraphQL = ! empty($context['graphql_operation_name']);

        if ($operation instanceof GetCollection && ! $isGraphQL) {
            $items = $items->map(fn (CustomerOrder $order) => $this->toListDto($order));
        }

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
