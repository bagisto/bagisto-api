<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Webkul\BagistoApi\Admin\Models\AdminInvoice;
use Webkul\Sales\Models\Invoice;

/**
 * Maps a core Invoice model to the AdminInvoice resource (the GraphQL/REST
 * detail payload). Shared by AdminInvoiceProvider (read) and
 * AdminInvoiceCreateProcessor (create) so both return the identical, complete
 * payload — every invoice column + order/customer/address context + items.
 *
 * AdminInvoice IS the resource the detail Get/Query return (no `output:` DTO),
 * so its GraphQL `id` IRI resolves from its own /api/admin/invoices/{id} route —
 * mirrors the working AdminOrderDetail pattern.
 */
trait BuildsAdminInvoice
{
    use MapsOrderActionItems;
    use MapsOrderAddress;

    protected function buildAdminInvoice(Invoice $invoice): AdminInvoice
    {
        $order = $invoice->order;
        $currency = $invoice->order_currency_code ?? $order?->order_currency_code;

        $dto = new AdminInvoice;
        $dto->id = (int) $invoice->id;
        $dto->incrementId = $invoice->increment_id;
        $dto->orderId = (int) $invoice->order_id;
        $dto->orderIncrementId = $order?->increment_id;
        $dto->state = $invoice->state;
        $dto->emailSent = (bool) $invoice->email_sent;
        $dto->totalQty = (int) $invoice->total_qty;

        // --- Currency codes ---
        $dto->orderCurrencyCode = $currency;
        $dto->baseCurrencyCode = $invoice->base_currency_code;
        $dto->channelCurrencyCode = $invoice->channel_currency_code;

        // --- Sub-total ---
        $dto->subTotal = (float) $invoice->sub_total;
        $dto->formattedSubTotal = core()->formatPrice((float) $invoice->sub_total, $currency);
        $dto->baseSubTotal = (float) $invoice->base_sub_total;
        $dto->formattedBaseSubTotal = core()->formatBasePrice((float) $invoice->base_sub_total);
        $dto->subTotalInclTax = (float) $invoice->sub_total_incl_tax;
        $dto->formattedSubTotalInclTax = core()->formatPrice((float) $invoice->sub_total_incl_tax, $currency);
        $dto->baseSubTotalInclTax = (float) $invoice->base_sub_total_incl_tax;
        $dto->formattedBaseSubTotalInclTax = core()->formatBasePrice((float) $invoice->base_sub_total_incl_tax);

        // --- Grand total ---
        $dto->grandTotal = (float) $invoice->grand_total;
        $dto->formattedGrandTotal = core()->formatPrice((float) $invoice->grand_total, $currency);
        $dto->baseGrandTotal = (float) $invoice->base_grand_total;
        $dto->formattedBaseGrandTotal = core()->formatBasePrice((float) $invoice->base_grand_total);

        // --- Tax ---
        $dto->taxAmount = (float) $invoice->tax_amount;
        $dto->formattedTaxAmount = core()->formatPrice((float) $invoice->tax_amount, $currency);
        $dto->baseTaxAmount = (float) $invoice->base_tax_amount;
        $dto->formattedBaseTaxAmount = core()->formatBasePrice((float) $invoice->base_tax_amount);

        // --- Discount ---
        $dto->discountAmount = (float) $invoice->discount_amount;
        $dto->formattedDiscountAmount = core()->formatPrice((float) $invoice->discount_amount, $currency);
        $dto->baseDiscountAmount = (float) $invoice->base_discount_amount;
        $dto->formattedBaseDiscountAmount = core()->formatBasePrice((float) $invoice->base_discount_amount);

        // --- Shipping ---
        $dto->shippingAmount = (float) $invoice->shipping_amount;
        $dto->formattedShippingAmount = core()->formatPrice((float) $invoice->shipping_amount, $currency);
        $dto->baseShippingAmount = (float) $invoice->base_shipping_amount;
        $dto->formattedBaseShippingAmount = core()->formatBasePrice((float) $invoice->base_shipping_amount);
        $dto->shippingAmountInclTax = (float) $invoice->shipping_amount_incl_tax;
        $dto->formattedShippingAmountInclTax = core()->formatPrice((float) $invoice->shipping_amount_incl_tax, $currency);
        $dto->baseShippingAmountInclTax = (float) $invoice->base_shipping_amount_incl_tax;
        $dto->formattedBaseShippingAmountInclTax = core()->formatBasePrice((float) $invoice->base_shipping_amount_incl_tax);
        $dto->shippingTaxAmount = (float) $invoice->shipping_tax_amount;
        $dto->formattedShippingTaxAmount = core()->formatPrice((float) $invoice->shipping_tax_amount, $currency);
        $dto->baseShippingTaxAmount = (float) $invoice->base_shipping_tax_amount;
        $dto->formattedBaseShippingTaxAmount = core()->formatBasePrice((float) $invoice->base_shipping_tax_amount);

        $dto->transactionId = $invoice->transaction_id;
        $dto->reminders = $invoice->reminders !== null ? (int) $invoice->reminders : null;
        $dto->nextReminderAt = $invoice->next_reminder_at ? (string) $invoice->next_reminder_at : null;
        $dto->createdAt = $invoice->created_at ? (string) $invoice->created_at : null;
        $dto->updatedAt = $invoice->updated_at ? (string) $invoice->updated_at : null;

        // --- Order / customer context (mirrors the admin invoice view's right column) ---
        $dto->orderStatus = $order?->status;
        $dto->orderStatusLabel = $order?->status_label;
        $dto->orderDate = $order?->created_at ? (string) $order->created_at : null;
        $dto->channelName = $order?->channel_name;
        $dto->customerName = $order?->customer_full_name;
        $dto->customerEmail = $order?->customer_email;
        $dto->billingAddress = $this->mapAddress($order?->billing_address);
        $dto->shippingAddress = $this->mapAddress($order?->shipping_address);

        $dto->items = $invoice->items
            ? $invoice->items->map(fn ($row) => $this->mapItem($row, $currency))->all()
            : [];

        return $dto;
    }
}
