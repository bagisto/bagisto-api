<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\OrderDetailRestDto;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\OrderDetail;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Order;

/**
 * Provides the full admin Order detail for REST GET /api/admin/orders/{id} and
 * the GraphQL adminOrderDetail query.
 *
 * Branches on transport (the AdminReview recipe):
 *   - GraphQL → returns the OrderDetail Eloquent model with its relations
 *     eager-loaded, so nested data resolves as connections / typed objects.
 *   - REST    → maps the core Order to OrderDetailRestDto (the historical flat
 *     shape — customer/addresses as objects, items/etc. as flat arrays).
 */
class OrderDetailProvider implements ProviderInterface
{
    /** Relations eager-loaded for the REST flat mapping. */
    protected const REST_RELATIONS = [
        'customer.group',
        'channel',
        'addresses',
        'payment',
        'items.product',
        'items.child',
        'items.children',
        'items.downloadable_link_purchased',
        'invoices',
        'shipments',
        'refunds',
        'comments',
    ];

    /** Relations eager-loaded for the GraphQL connection / typed-object resolution. */
    public const GRAPHQL_RELATIONS = [
        'items.children',
        'invoices',
        'shipments',
        'refunds',
        'comments',
        'customer.group',
        'addresses',
    ];

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): OrderDetail|OrderDetailRestDto
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $id = $uriVariables['id'] ?? $context['args']['id'] ?? null;

        if ($id === null) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.not-found'));
        }

        $id = (int) basename((string) $id);

        if (! empty($context['graphql_operation_name'])) {
            return $this->loadForGraphQL($id);
        }

        $order = Order::with(self::REST_RELATIONS)->find($id);

        if (! $order) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.not-found'));
        }

        return $this->toRestDto($order);
    }

    /**
     * Load the Eloquent OrderDetail with its connection relations (GraphQL).
     */
    public function loadForGraphQL(int $id): OrderDetail
    {
        $order = OrderDetail::with(self::GRAPHQL_RELATIONS)->find($id);

        if (! $order) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.not-found'));
        }

        return $order;
    }

    /**
     * Map a core Order to the flat REST DTO. Public so the Cancel processor can
     * reuse the same REST response shape.
     */
    public function toRestDto(Order $order): OrderDetailRestDto
    {
        $currency = $order->order_currency_code;

        $dto = new OrderDetailRestDto;

        $dto->id = $order->id;
        $dto->incrementId = $order->increment_id;
        $dto->status = $order->status;
        $dto->statusLabel = $order->status_label;
        $dto->channelName = $order->channel_name;
        $dto->isGuest = (bool) $order->is_guest;
        $dto->isGift = (bool) $order->is_gift;
        $dto->customerEmail = $order->customer_email;
        $dto->customerFirstName = $order->customer_first_name;
        $dto->customerLastName = $order->customer_last_name;
        $dto->shippingMethod = $order->shipping_method;
        $dto->shippingTitle = $order->shipping_title;
        $dto->shippingDescription = $order->shipping_description;
        $dto->paymentMethod = $order->payment?->method;
        $dto->paymentTitle = $this->paymentTitle($order);
        $dto->couponCode = $order->coupon_code;
        $dto->totalItemCount = $order->total_item_count;
        $dto->totalQtyOrdered = (int) $order->total_qty_ordered;
        $dto->baseCurrencyCode = $order->base_currency_code;
        $dto->channelCurrencyCode = $order->channel_currency_code;
        $dto->orderCurrencyCode = $currency;

        $dto->grandTotal = (float) $order->grand_total;
        $dto->baseGrandTotal = (float) $order->base_grand_total;
        $dto->formattedGrandTotal = core()->formatPrice($order->grand_total, $currency);
        $dto->grandTotalInvoiced = (float) $order->grand_total_invoiced;
        $dto->formattedGrandTotalInvoiced = core()->formatPrice($order->grand_total_invoiced, $currency);
        $dto->grandTotalRefunded = (float) $order->grand_total_refunded;
        $dto->formattedGrandTotalRefunded = core()->formatPrice($order->grand_total_refunded, $currency);
        $dto->subTotal = (float) $order->sub_total;
        $dto->baseSubTotal = (float) $order->base_sub_total;
        $dto->formattedSubTotal = core()->formatPrice($order->sub_total, $currency);
        $dto->taxAmount = (float) $order->tax_amount;
        $dto->formattedTaxAmount = core()->formatPrice($order->tax_amount, $currency);
        $dto->discountAmount = (float) $order->discount_amount;
        $dto->formattedDiscountAmount = core()->formatPrice($order->discount_amount, $currency);
        $dto->shippingAmount = (float) $order->shipping_amount;
        $dto->formattedShippingAmount = core()->formatPrice($order->shipping_amount, $currency);
        $dto->totalDue = (float) $order->total_due;
        $dto->baseTotalDue = (float) $order->base_total_due;
        $dto->formattedTotalDue = core()->formatPrice($order->total_due, $currency);

        $dto->createdAt = (string) $order->created_at;
        $dto->updatedAt = (string) $order->updated_at;

        $dto->customer = $this->toCustomer($order);
        $dto->addresses = array_values(array_filter([
            $this->toAddress($order->billing_address),
            $this->toAddress($order->shipping_address),
        ]));
        $dto->items = $order->items->map(fn ($item) => $this->toItem($item, $currency))->all();
        $dto->invoices = $order->invoices->map(fn ($invoice) => $this->toInvoice($invoice, $currency))->all();
        $dto->shipments = $order->shipments->map(fn ($shipment) => $this->toShipment($shipment))->all();
        $dto->refunds = $order->refunds->map(fn ($refund) => $this->toRefund($refund, $currency))->all();
        $dto->comments = $order->comments
            ->sortByDesc('id')
            ->map(fn ($comment) => $this->toComment($comment))
            ->values()
            ->all();

        return $dto;
    }

    protected function toCustomer(Order $order): ?array
    {
        $customer = $order->customer;

        if (! $customer) {
            return null;
        }

        $group = null;

        if ($g = $customer->group) {
            $group = [
                'id'   => $g->id,
                'code' => $g->code,
                'name' => $g->name,
            ];
        }

        return [
            'id'          => $customer->id,
            'email'       => $customer->email,
            'name'        => trim(($customer->first_name ?? '').' '.($customer->last_name ?? '')) ?: null,
            'firstName'   => $customer->first_name,
            'lastName'    => $customer->last_name,
            'gender'      => $customer->gender,
            'dateOfBirth' => $customer->date_of_birth ? (string) $customer->date_of_birth : null,
            'phone'       => $customer->phone,
            'status'      => $customer->status !== null ? (int) $customer->status : null,
            'group'       => $group,
        ];
    }

    protected function toAddress($address): ?array
    {
        if (! $address) {
            return null;
        }

        return [
            'id'          => $address->id,
            'addressType' => $address->address_type,
            'firstName'   => $address->first_name,
            'lastName'    => $address->last_name,
            'companyName' => $address->company_name,
            'vatId'       => $address->vat_id,
            'address'     => $address->address,
            'city'        => $address->city,
            'state'       => $address->state,
            'country'     => $address->country,
            'postcode'    => $address->postcode,
            'email'       => $address->email,
            'phone'       => $address->phone,
        ];
    }

    protected function toItem($item, string $currency, bool $withChildren = true): array
    {
        $row = [
            'id'                      => $item->id,
            'sku'                     => $item->sku,
            'type'                    => $item->type,
            'name'                    => $item->name,
            'productId'               => $item->product_id,
            'weight'                  => $item->weight !== null ? (float) $item->weight : null,
            'qtyOrdered'              => (int) $item->qty_ordered,
            'qtyShipped'              => (int) $item->qty_shipped,
            'qtyInvoiced'             => (int) $item->qty_invoiced,
            'qtyCanceled'             => (int) $item->qty_canceled,
            'qtyRefunded'             => (int) $item->qty_refunded,
            'price'                   => (float) $item->price,
            'formattedPrice'          => core()->formatPrice($item->price, $currency),
            'basePrice'               => (float) $item->base_price,
            'total'                   => (float) $item->total,
            'formattedTotal'          => core()->formatPrice($item->total, $currency),
            'baseTotal'               => (float) $item->base_total,
            'taxAmount'               => (float) $item->tax_amount,
            'formattedTaxAmount'      => core()->formatPrice($item->tax_amount, $currency),
            'taxPercent'              => $item->tax_percent !== null ? (float) $item->tax_percent : null,
            'discountAmount'          => (float) $item->discount_amount,
            'formattedDiscountAmount' => core()->formatPrice($item->discount_amount, $currency),
            'discountPercent'         => $item->discount_percent !== null ? (float) $item->discount_percent : null,
            'additional'              => is_array($item->additional) ? $item->additional : null,
            'createdAt'               => (string) $item->created_at,
            'child'                   => null,
            'children'                => [],
            'downloadableLinks'       => [],
        ];

        if ($withChildren) {
            if ($item->child) {
                $row['child'] = $this->toItem($item->child, $currency, false);
            }

            $row['children'] = $item->children
                ? $item->children->map(fn ($child) => $this->toItem($child, $currency, false))->all()
                : [];

            $row['downloadableLinks'] = $item->downloadable_link_purchased
                ? $item->downloadable_link_purchased->map(fn ($link) => $link->toArray())->all()
                : [];
        }

        return $row;
    }

    protected function toInvoice($invoice, string $currency): array
    {
        return [
            'id'                  => $invoice->id,
            'incrementId'         => $invoice->increment_id,
            'state'               => $invoice->state,
            'emailSent'           => (bool) $invoice->email_sent,
            'totalQty'            => (int) $invoice->total_qty,
            'subTotal'            => (float) $invoice->sub_total,
            'formattedSubTotal'   => core()->formatPrice($invoice->sub_total, $currency),
            'grandTotal'          => (float) $invoice->grand_total,
            'formattedGrandTotal' => core()->formatPrice($invoice->grand_total, $currency),
            'taxAmount'           => (float) $invoice->tax_amount,
            'discountAmount'      => (float) $invoice->discount_amount,
            'shippingAmount'      => (float) $invoice->shipping_amount,
            'transactionId'       => $invoice->transaction_id,
            'createdAt'           => (string) $invoice->created_at,
        ];
    }

    protected function toShipment($shipment): array
    {
        return [
            'id'                  => $shipment->id,
            'status'              => $shipment->status !== null ? (string) $shipment->status : null,
            'totalQty'            => (int) $shipment->total_qty,
            'totalWeight'         => $shipment->total_weight !== null ? (float) $shipment->total_weight : null,
            'carrierCode'         => $shipment->carrier_code,
            'carrierTitle'        => $shipment->carrier_title,
            'trackNumber'         => $shipment->track_number,
            'emailSent'           => (bool) $shipment->email_sent,
            'inventorySourceName' => $shipment->inventory_source_name,
            'createdAt'           => (string) $shipment->created_at,
        ];
    }

    protected function toRefund($refund, string $currency): array
    {
        return [
            'id'                  => $refund->id,
            'state'               => $refund->state,
            'totalQty'            => (int) $refund->total_qty,
            'grandTotal'          => (float) $refund->grand_total,
            'formattedGrandTotal' => core()->formatPrice($refund->grand_total, $currency),
            'baseGrandTotal'      => (float) $refund->base_grand_total,
            'createdAt'           => (string) $refund->created_at,
        ];
    }

    protected function toComment($comment): array
    {
        return [
            'id'               => $comment->id,
            'comment'          => $comment->comment,
            'customerNotified' => (bool) $comment->customer_notified,
            'createdAt'        => (string) $comment->created_at,
        ];
    }

    protected function paymentTitle(Order $order): ?string
    {
        $method = $order->payment?->method;

        if (! $method) {
            return null;
        }

        return core()->getConfigData('sales.payment_methods.'.$method.'.title') ?: $method;
    }
}
