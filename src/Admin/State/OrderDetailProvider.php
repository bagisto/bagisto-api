<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\OrderDetailAddress;
use Webkul\BagistoApi\Admin\Dto\OrderDetailCustomer;
use Webkul\BagistoApi\Admin\Dto\OrderDetailCustomerGroup;
use Webkul\BagistoApi\Admin\Dto\OrderDetailInvoice;
use Webkul\BagistoApi\Admin\Dto\OrderDetailItem;
use Webkul\BagistoApi\Admin\Dto\OrderDetailShipment;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\OrderDetail;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Order;

/**
 * Provides the full admin Order detail — REST GET /api/admin/orders/{id} and
 * the GraphQL adminOrderDetail query.
 *
 * Eager-loads every relation the order-view screen needs and embeds them
 * inline (measured ~20ms fully loaded — see CLAUDE.md). Items carry their
 * product type plus type-specific data so the client can render per type.
 */
class OrderDetailProvider implements ProviderInterface
{
    protected const RELATIONS = [
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
    ];

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): OrderDetail
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $id = $uriVariables['id']
            ?? $context['args']['id']
            ?? null;

        if ($id === null) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.not-found'));
        }

        // GraphQL may pass an IRI; keep only the numeric id.
        $id = (int) basename((string) $id);

        $order = Order::with(self::RELATIONS)->find($id);

        if (! $order) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.not-found'));
        }

        return $this->toDetail($order);
    }

    /**
     * Map an Order model to the full detail DTO. Public so other admin
     * processors (Cancel, Invoice / Shipment / Refund create) can reuse the
     * same response shape without duplicating the mapper.
     */
    public function toDetail(Order $order): OrderDetail
    {
        $currency = $order->order_currency_code;

        $detail = new OrderDetail;

        $detail->id = $order->id;
        $detail->incrementId = $order->increment_id;
        $detail->status = $order->status;
        $detail->statusLabel = $order->status_label;
        $detail->channelName = $order->channel_name;
        $detail->isGuest = (bool) $order->is_guest;
        $detail->isGift = (bool) $order->is_gift;
        $detail->customerEmail = $order->customer_email;
        $detail->customerFirstName = $order->customer_first_name;
        $detail->customerLastName = $order->customer_last_name;
        $detail->shippingMethod = $order->shipping_method;
        $detail->shippingTitle = $order->shipping_title;
        $detail->shippingDescription = $order->shipping_description;
        $detail->paymentTitle = $this->paymentTitle($order);
        $detail->couponCode = $order->coupon_code;
        $detail->totalItemCount = $order->total_item_count;
        $detail->totalQtyOrdered = (int) $order->total_qty_ordered;
        $detail->baseCurrencyCode = $order->base_currency_code;
        $detail->channelCurrencyCode = $order->channel_currency_code;
        $detail->orderCurrencyCode = $currency;

        $detail->grandTotal = (float) $order->grand_total;
        $detail->baseGrandTotal = (float) $order->base_grand_total;
        $detail->formattedGrandTotal = core()->formatPrice($order->grand_total, $currency);
        $detail->grandTotalInvoiced = (float) $order->grand_total_invoiced;
        $detail->formattedGrandTotalInvoiced = core()->formatPrice($order->grand_total_invoiced, $currency);
        $detail->grandTotalRefunded = (float) $order->grand_total_refunded;
        $detail->formattedGrandTotalRefunded = core()->formatPrice($order->grand_total_refunded, $currency);
        $detail->subTotal = (float) $order->sub_total;
        $detail->baseSubTotal = (float) $order->base_sub_total;
        $detail->formattedSubTotal = core()->formatPrice($order->sub_total, $currency);
        $detail->taxAmount = (float) $order->tax_amount;
        $detail->formattedTaxAmount = core()->formatPrice($order->tax_amount, $currency);
        $detail->discountAmount = (float) $order->discount_amount;
        $detail->formattedDiscountAmount = core()->formatPrice($order->discount_amount, $currency);
        $detail->shippingAmount = (float) $order->shipping_amount;
        $detail->formattedShippingAmount = core()->formatPrice($order->shipping_amount, $currency);

        $detail->createdAt = (string) $order->created_at;
        $detail->updatedAt = (string) $order->updated_at;

        $detail->customer = $this->toCustomer($order);
        $detail->billingAddress = $this->toAddress($order->billing_address);
        $detail->shippingAddress = $this->toAddress($order->shipping_address);
        $detail->items = $order->items->map(fn ($item) => $this->toItem($item, $currency))->all();
        $detail->invoices = $order->invoices->map(fn ($invoice) => $this->toInvoice($invoice, $currency))->all();
        $detail->shipments = $order->shipments->map(fn ($shipment) => $this->toShipment($shipment))->all();

        return $detail;
    }

    /**
     * Map the order's customer (null for guest orders).
     */
    protected function toCustomer(Order $order): ?OrderDetailCustomer
    {
        $customer = $order->customer;

        if (! $customer) {
            return null;
        }

        $dto = new OrderDetailCustomer;
        $dto->id = $customer->id;
        $dto->email = $customer->email;
        $dto->firstName = $customer->first_name;
        $dto->lastName = $customer->last_name;
        $dto->name = trim(($customer->first_name ?? '').' '.($customer->last_name ?? '')) ?: null;
        $dto->gender = $customer->gender;
        $dto->dateOfBirth = $customer->date_of_birth ? (string) $customer->date_of_birth : null;
        $dto->phone = $customer->phone;
        $dto->status = $customer->status !== null ? (int) $customer->status : null;

        if ($group = $customer->group) {
            $groupDto = new OrderDetailCustomerGroup;
            $groupDto->id = $group->id;
            $groupDto->code = $group->code;
            $groupDto->name = $group->name;
            $dto->group = $groupDto;
        }

        return $dto;
    }

    /**
     * Map an order address (billing or shipping).
     */
    protected function toAddress($address): ?OrderDetailAddress
    {
        if (! $address) {
            return null;
        }

        $dto = new OrderDetailAddress;
        $dto->id = $address->id;
        $dto->addressType = $address->address_type;
        $dto->firstName = $address->first_name;
        $dto->lastName = $address->last_name;
        $dto->companyName = $address->company_name;
        $dto->address = $address->address;
        $dto->city = $address->city;
        $dto->state = $address->state;
        $dto->country = $address->country;
        $dto->postcode = $address->postcode;
        $dto->email = $address->email;
        $dto->phone = $address->phone;

        return $dto;
    }

    /**
     * Map an order line-item, including product-type-specific data.
     */
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
            // Configurable: a single chosen variant. Bundle/grouped: child rows.
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

    /**
     * Map an invoice.
     */
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

    /**
     * Map a shipment.
     */
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

    /**
     * Resolve the payment-method display title from core config.
     */
    protected function paymentTitle(Order $order): ?string
    {
        $method = $order->payment?->method;

        if (! $method) {
            return null;
        }

        return core()->getConfigData('sales.payment_methods.'.$method.'.title') ?: $method;
    }
}
