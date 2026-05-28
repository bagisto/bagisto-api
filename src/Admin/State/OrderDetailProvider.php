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
    protected function toItem($item, string $currency, bool $withChildren = true): OrderDetailItem
    {
        $dto = new OrderDetailItem;

        $dto->id = $item->id;
        $dto->sku = $item->sku;
        $dto->type = $item->type;
        $dto->name = $item->name;
        $dto->productId = $item->product_id;
        $dto->weight = $item->weight !== null ? (float) $item->weight : null;
        $dto->qtyOrdered = (int) $item->qty_ordered;
        $dto->qtyShipped = (int) $item->qty_shipped;
        $dto->qtyInvoiced = (int) $item->qty_invoiced;
        $dto->qtyCanceled = (int) $item->qty_canceled;
        $dto->qtyRefunded = (int) $item->qty_refunded;
        $dto->price = (float) $item->price;
        $dto->formattedPrice = core()->formatPrice($item->price, $currency);
        $dto->basePrice = (float) $item->base_price;
        $dto->total = (float) $item->total;
        $dto->formattedTotal = core()->formatPrice($item->total, $currency);
        $dto->baseTotal = (float) $item->base_total;
        $dto->taxAmount = (float) $item->tax_amount;
        $dto->formattedTaxAmount = core()->formatPrice($item->tax_amount, $currency);
        $dto->taxPercent = $item->tax_percent !== null ? (float) $item->tax_percent : null;
        $dto->discountAmount = (float) $item->discount_amount;
        $dto->formattedDiscountAmount = core()->formatPrice($item->discount_amount, $currency);
        $dto->discountPercent = $item->discount_percent !== null ? (float) $item->discount_percent : null;
        $dto->additional = is_array($item->additional) ? $item->additional : null;
        $dto->createdAt = (string) $item->created_at;

        if ($withChildren) {
            // Configurable: a single chosen variant. Bundle/grouped: child rows.
            if ($item->child) {
                $dto->child = $this->toItem($item->child, $currency, false);
            }

            $dto->children = $item->children
                ? $item->children->map(fn ($child) => $this->toItem($child, $currency, false))->all()
                : [];

            $dto->downloadableLinks = $item->downloadable_link_purchased
                ? $item->downloadable_link_purchased->map(fn ($link) => $link->toArray())->all()
                : [];
        }

        return $dto;
    }

    /**
     * Map an invoice.
     */
    protected function toInvoice($invoice, string $currency): OrderDetailInvoice
    {
        $dto = new OrderDetailInvoice;

        $dto->id = $invoice->id;
        $dto->incrementId = $invoice->increment_id;
        $dto->state = $invoice->state;
        $dto->emailSent = (bool) $invoice->email_sent;
        $dto->totalQty = (int) $invoice->total_qty;
        $dto->subTotal = (float) $invoice->sub_total;
        $dto->formattedSubTotal = core()->formatPrice($invoice->sub_total, $currency);
        $dto->grandTotal = (float) $invoice->grand_total;
        $dto->formattedGrandTotal = core()->formatPrice($invoice->grand_total, $currency);
        $dto->taxAmount = (float) $invoice->tax_amount;
        $dto->discountAmount = (float) $invoice->discount_amount;
        $dto->shippingAmount = (float) $invoice->shipping_amount;
        $dto->transactionId = $invoice->transaction_id;
        $dto->createdAt = (string) $invoice->created_at;

        return $dto;
    }

    /**
     * Map a shipment.
     */
    protected function toShipment($shipment): OrderDetailShipment
    {
        $dto = new OrderDetailShipment;

        $dto->id = $shipment->id;
        $dto->status = $shipment->status !== null ? (string) $shipment->status : null;
        $dto->totalQty = (int) $shipment->total_qty;
        $dto->totalWeight = $shipment->total_weight !== null ? (float) $shipment->total_weight : null;
        $dto->carrierCode = $shipment->carrier_code;
        $dto->carrierTitle = $shipment->carrier_title;
        $dto->trackNumber = $shipment->track_number;
        $dto->emailSent = (bool) $shipment->email_sent;
        $dto->inventorySourceName = $shipment->inventory_source_name;
        $dto->createdAt = (string) $shipment->created_at;

        return $dto;
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
