<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Dto\CustomerOrder\CustomerOrderDetailDto;
use Webkul\BagistoApi\Dto\CustomerOrder\CustomerOrderListDto;
use Webkul\BagistoApi\Dto\CustomerOrder\OrderAddressDto;
use Webkul\BagistoApi\Dto\CustomerOrder\OrderInvoiceDto;
use Webkul\BagistoApi\Dto\CustomerOrder\OrderItemDto;
use Webkul\BagistoApi\Dto\CustomerOrder\OrderPaymentDto;
use Webkul\BagistoApi\Dto\CustomerOrder\OrderRefundDto;
use Webkul\BagistoApi\Dto\CustomerOrder\OrderShipmentDto;
use Webkul\BagistoApi\Dto\CustomerOrder\OrderShipmentItemDto;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerOrder;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Repositories\OrderRepository;

class CustomerOrderProvider implements ProviderInterface
{
    use ClampsPageSize;
    use ResolvesCursorWindow;

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly Pagination $pagination,
        private readonly OrderRepository $orderRepository,
    ) {}

    /**
     * Provide customer orders for collection or single-item operations.
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
            return $this->provideItem($customer, $uriVariables, $operation, $context);
        }

        return $this->provideCollection($customer, $context, $operation);
    }

    /**
     * To list dto.
     */
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
     * Return a single order owned by the customer.
     */
    private function provideItem(object $customer, array $uriVariables, Operation $operation, array $context = []): CustomerOrder|CustomerOrderDetailDto
    {
        $id = $uriVariables['id'] ?? null;

        if (! $id) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.customer-order.id-required'));
        }

        $orderQuery = CustomerOrder::with([
            'items', 'addresses', 'payment',
            'shipments.items', 'invoices.items', 'refunds.items',
        ])
            ->where('customer_id', $customer->id)
            ->where('customer_type', Customer::class);

        $order = $orderQuery->find($id);

        if (! $order) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.customer-order.not-found', ['id' => $id])
            );
        }

        $isGraphQL = ! empty($context['graphql_operation_name']);

        if (
            $operation instanceof Get
            && ! $isGraphQL
        ) {
            return $this->toDetailDto($order);
        }

        return $order;
    }

    /**
     * To detail dto.
     */
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

        $coreOrder = $this->orderRepository->with('items.product')->find($order->id);

        $dto->total_due = round((float) $coreOrder->total_due, 2) + 0.0;
        $dto->base_total_due = round((float) $coreOrder->base_total_due, 2) + 0.0;
        $dto->can_cancel = $coreOrder->canCancel();
        $dto->can_reorder = $coreOrder->canReorder();
        $dto->have_stockable_items = $coreOrder->haveStockableItems();

        $dto->items = $order->items
            ->map(fn ($item) => $this->toItemDto($item))
            ->all();

        $dto->addresses = $order->addresses
            ->map(fn ($address) => $this->toAddressDto($address))
            ->all();

        if ($order->payment) {
            $dto->payment = $this->toPaymentDto($order->payment);
        }

        $dto->shipments = $order->shipments
            ->map(fn ($shipment) => $this->toShipmentDto($shipment))
            ->all();

        $dto->invoices = $order->invoices
            ->map(fn ($invoice) => $this->toInvoiceDto($invoice))
            ->all();

        $dto->refunds = $order->refunds
            ->map(fn ($refund) => $this->toRefundDto($refund))
            ->all();

        return $dto;
    }

    /**
     * To item dto.
     */
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

    /**
     * To address dto.
     */
    private function toAddressDto($address): OrderAddressDto
    {
        $dto = new OrderAddressDto;
        $dto->id = (int) $address->id;
        $dto->address_type = $address->address_type;
        $dto->first_name = $address->first_name;
        $dto->last_name = $address->last_name;
        $dto->gender = $address->gender;
        $dto->company_name = $address->company_name;
        $dto->address = $address->address;
        $dto->city = $address->city;
        $dto->state = $address->state;
        $dto->country = $address->country;
        $dto->postcode = $address->postcode;
        $dto->email = $address->email;
        $dto->phone = $address->phone;
        $dto->vat_id = $address->vat_id;

        return $dto;
    }

    /**
     * To payment dto.
     */
    private function toPaymentDto($payment): OrderPaymentDto
    {
        $dto = new OrderPaymentDto;
        $dto->id = (int) $payment->id;
        $dto->method = $payment->method;
        $dto->method_title = $payment->method_title;

        return $dto;
    }

    /**
     * To shipment dto.
     */
    private function toShipmentDto($shipment): OrderShipmentDto
    {
        $dto = new OrderShipmentDto;
        $dto->id = (int) $shipment->id;
        $dto->shipping_number = '#'.$shipment->id;
        $dto->carrier_title = $shipment->carrier_title ?? null;
        $dto->carrier_code = $shipment->carrier_code ?? null;
        $dto->track_number = $shipment->track_number ?? null;
        $dto->total_qty = $shipment->total_qty !== null ? (int) $shipment->total_qty : null;
        $dto->total_weight = $shipment->total_weight !== null ? (float) $shipment->total_weight : null;
        $dto->email_sent = $shipment->email_sent === null ? null : (bool) $shipment->email_sent;
        $dto->created_at = $shipment->created_at?->toIso8601String();
        $dto->updated_at = $shipment->updated_at?->toIso8601String();

        if ($shipment->relationLoaded('items')) {
            $dto->items = $shipment->items->map(function ($shipmentItem) {
                $itemDto = new OrderShipmentItemDto;
                $itemDto->id = (int) $shipmentItem->id;
                $itemDto->order_item_id = $shipmentItem->order_item_id !== null ? (int) $shipmentItem->order_item_id : null;
                $itemDto->qty = $shipmentItem->qty !== null ? (int) $shipmentItem->qty : null;
                $itemDto->weight = $shipmentItem->weight !== null ? (float) $shipmentItem->weight : null;

                return $itemDto;
            })->all();
        }

        return $dto;
    }

    /**
     * Map an invoice of the order to its DTO.
     */
    private function toInvoiceDto(object $invoice): OrderInvoiceDto
    {
        $dto = new OrderInvoiceDto;
        $dto->id = (int) $invoice->id;
        $dto->increment_id = $invoice->increment_id;
        $dto->created_at = $invoice->created_at?->toIso8601String();

        foreach ([
            'sub_total', 'base_sub_total', 'grand_total', 'base_grand_total',
            'tax_amount', 'base_tax_amount', 'shipping_amount', 'base_shipping_amount',
            'discount_amount', 'base_discount_amount',
            'sub_total_incl_tax', 'base_sub_total_incl_tax',
            'shipping_amount_incl_tax', 'base_shipping_amount_incl_tax',
        ] as $field) {
            $dto->{$field} = $invoice->{$field} !== null ? (float) $invoice->{$field} : null;
        }

        $dto->items = $invoice->items
            ->map(fn ($item) => $this->toDocumentItemArray($item))
            ->all();

        return $dto;
    }

    /**
     * Map a refund of the order to its DTO.
     */
    private function toRefundDto(object $refund): OrderRefundDto
    {
        $dto = new OrderRefundDto;
        $dto->id = (int) $refund->id;
        $dto->increment_id = $refund->increment_id;
        $dto->created_at = $refund->created_at?->toIso8601String();

        foreach ([
            'sub_total', 'base_sub_total', 'grand_total', 'base_grand_total',
            'tax_amount', 'base_tax_amount', 'shipping_amount', 'base_shipping_amount',
            'discount_amount', 'base_discount_amount',
            'adjustment_fee', 'base_adjustment_fee',
            'adjustment_refund', 'base_adjustment_refund',
        ] as $field) {
            $dto->{$field} = $refund->{$field} !== null ? (float) $refund->{$field} : null;
        }

        $dto->items = $refund->items
            ->map(fn ($item) => $this->toDocumentItemArray($item))
            ->all();

        return $dto;
    }

    /**
     * Map an invoice or refund item to the array the order detail returns.
     */
    private function toDocumentItemArray(object $item): array
    {
        return [
            'id' => (int) $item->id,
            'order_item_id' => $item->order_item_id !== null ? (int) $item->order_item_id : null,
            'sku' => $item->sku,
            'name' => $item->name,
            'qty' => $item->qty !== null ? (int) $item->qty : null,
            'price' => $item->price !== null ? (float) $item->price : null,
            'base_price' => $item->base_price !== null ? (float) $item->base_price : null,
            'total' => $item->total !== null ? (float) $item->total : null,
            'base_total' => $item->base_total !== null ? (float) $item->base_total : null,
            'tax_amount' => $item->tax_amount !== null ? (float) $item->tax_amount : null,
            'base_tax_amount' => $item->base_tax_amount !== null ? (float) $item->base_tax_amount : null,
        ];
    }

    /**
     * Enable debug dumps only when explicitly requested via header:
     * X-DEBUG-CUSTOMER-ORDER: 1.
     */
    private function debugDump(string $checkpoint, array $payload = []): void
    {
        if (! $this->shouldDebugDump()) {
            return;
        }
    }

    /**
     * Should debug dump.
     */
    private function shouldDebugDump(): bool
    {
        return request()->header('X-DEBUG-CUSTOMER-ORDER') === '1';
    }

    /**
     * Should debug dd.
     */
    private function shouldDebugDd(): bool
    {
        return request()->header('X-DEBUG-CUSTOMER-ORDER-DD') === '1';
    }

    /**
     * Targeted hard-stop for debugging.
     * Usage: X-DEBUG-CUSTOMER-ORDER-DD-AT: start|auth|id|result.
     */
    private function debugDdAt(string $checkpoint, array $payload = []): void
    {
        if (request()->header('X-DEBUG-CUSTOMER-ORDER-DD-AT') !== $checkpoint) {
            return;
        }
    }

    /**
     * Return a paginated collection of orders owned by the customer.
     */
    private function provideCollection(object $customer, array $context, Operation $operation): Paginator
    {
        $isGraphQL = ! empty($context['graphql_operation_name']);

        $args = $isGraphQL ? ($context['args'] ?? []) : $this->restPagingArgs();
        $filters = $context['filters'] ?? [];

        $query = CustomerOrder::with(['items', 'addresses', 'payment', 'shipments.items', 'shipments.shippingAddress'])
            ->where('customer_id', $customer->id)
            ->where('customer_type', Customer::class);

        $status = $args['status'] ?? $filters['status'] ?? null;
        if ($status !== null) {
            $query->where('status', (string) $status);
        }

        $requested = $this->requestedCursorLimit($args, 10);
        $limit = $requested > 0 ? $this->clampPageSize($requested) : 0;

        $query->orderBy('id', 'desc');

        $page = $this->paginateCursorWindow($query, $args, $limit);

        if (
            $operation instanceof GetCollection
            && ! $isGraphQL
        ) {
            $page->through(fn (CustomerOrder $order) => $this->toListDto($order));
        }

        return new Paginator($page);
    }

    /**
     * The cursor window a REST `page` and `per_page` select, with the `status` filter.
     */
    private function restPagingArgs(): array
    {
        $perPage = max(1, (int) request()->query('per_page', 10));
        $page = max(1, (int) request()->query('page', 1));

        $args = ['first' => $perPage];

        if ($page > 1) {
            $args['after'] = base64_encode((string) (($page - 1) * $this->clampPageSize($perPage) - 1));
        }

        if (request()->filled('status')) {
            $args['status'] = (string) request()->query('status');
        }

        return $args;
    }
}
