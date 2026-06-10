<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Webkul\BagistoApi\Admin\Models\AdminRefund;
use Webkul\Sales\Models\Refund;

trait BuildsAdminRefund
{
    use MapsOrderActionItems;
    use MapsOrderAddress;

    protected function buildAdminRefund(Refund $refund): AdminRefund
    {
        $order = $refund->order;
        $currency = $refund->order_currency_code ?? $order?->order_currency_code ?? '';

        $dto = new AdminRefund;
        $dto->id = (int) $refund->id;
        $dto->orderId = (int) $refund->order_id;
        $dto->orderIncrementId = $order?->increment_id;
        $dto->state = $refund->state;
        $dto->emailSent = $refund->email_sent !== null ? (bool) $refund->email_sent : null;
        $dto->totalQty = $refund->total_qty !== null ? (int) $refund->total_qty : null;

        $dto->orderCurrencyCode = $currency ?: null;
        $dto->baseCurrencyCode = $refund->base_currency_code;
        $dto->channelCurrencyCode = $refund->channel_currency_code;

        $dto->subTotal = (float) $refund->sub_total;
        $dto->formattedSubTotal = $this->refundMoney($refund->sub_total, $currency);
        $dto->baseSubTotal = (float) $refund->base_sub_total;
        $dto->formattedBaseSubTotal = core()->formatBasePrice((float) $refund->base_sub_total);
        $dto->subTotalInclTax = (float) $refund->sub_total_incl_tax;
        $dto->formattedSubTotalInclTax = $this->refundMoney($refund->sub_total_incl_tax, $currency);
        $dto->baseSubTotalInclTax = (float) $refund->base_sub_total_incl_tax;
        $dto->formattedBaseSubTotalInclTax = core()->formatBasePrice((float) $refund->base_sub_total_incl_tax);

        $dto->grandTotal = (float) $refund->grand_total;
        $dto->formattedGrandTotal = $this->refundMoney($refund->grand_total, $currency);
        $dto->baseGrandTotal = (float) $refund->base_grand_total;
        $dto->formattedBaseGrandTotal = core()->formatBasePrice((float) $refund->base_grand_total);

        $dto->taxAmount = (float) $refund->tax_amount;
        $dto->formattedTaxAmount = $this->refundMoney($refund->tax_amount, $currency);
        $dto->baseTaxAmount = (float) $refund->base_tax_amount;
        $dto->formattedBaseTaxAmount = core()->formatBasePrice((float) $refund->base_tax_amount);

        $dto->discountAmount = (float) $refund->discount_amount;
        $dto->formattedDiscountAmount = $this->refundMoney($refund->discount_amount, $currency);
        $dto->baseDiscountAmount = (float) $refund->base_discount_amount;
        $dto->formattedBaseDiscountAmount = core()->formatBasePrice((float) $refund->base_discount_amount);

        $dto->shippingAmount = (float) $refund->shipping_amount;
        $dto->formattedShippingAmount = $this->refundMoney($refund->shipping_amount, $currency);
        $dto->baseShippingAmount = (float) $refund->base_shipping_amount;
        $dto->formattedBaseShippingAmount = core()->formatBasePrice((float) $refund->base_shipping_amount);
        $dto->shippingAmountInclTax = (float) $refund->shipping_amount_incl_tax;
        $dto->formattedShippingAmountInclTax = $this->refundMoney($refund->shipping_amount_incl_tax, $currency);
        $dto->baseShippingAmountInclTax = (float) $refund->base_shipping_amount_incl_tax;
        $dto->formattedBaseShippingAmountInclTax = core()->formatBasePrice((float) $refund->base_shipping_amount_incl_tax);
        $dto->shippingTaxAmount = (float) $refund->shipping_tax_amount;
        $dto->formattedShippingTaxAmount = $this->refundMoney($refund->shipping_tax_amount, $currency);
        $dto->baseShippingTaxAmount = (float) $refund->base_shipping_tax_amount;
        $dto->formattedBaseShippingTaxAmount = core()->formatBasePrice((float) $refund->base_shipping_tax_amount);

        $dto->adjustmentRefund = (float) $refund->adjustment_refund;
        $dto->formattedAdjustmentRefund = $this->refundMoney($refund->adjustment_refund, $currency);
        $dto->baseAdjustmentRefund = (float) $refund->base_adjustment_refund;
        $dto->formattedBaseAdjustmentRefund = core()->formatBasePrice((float) $refund->base_adjustment_refund);
        $dto->adjustmentFee = (float) $refund->adjustment_fee;
        $dto->formattedAdjustmentFee = $this->refundMoney($refund->adjustment_fee, $currency);
        $dto->baseAdjustmentFee = (float) $refund->base_adjustment_fee;
        $dto->formattedBaseAdjustmentFee = core()->formatBasePrice((float) $refund->base_adjustment_fee);

        $dto->createdAt = $refund->created_at ? (string) $refund->created_at : null;
        $dto->updatedAt = $refund->updated_at ? (string) $refund->updated_at : null;

        $billing = $order?->billing_address;
        $name = trim((string) ($billing?->first_name ?? '').' '.($billing?->last_name ?? ''));
        $dto->billedTo = $name !== '' ? $name : null;

        $dto->orderStatus = $order?->status;
        $dto->orderStatusLabel = $order?->status_label;
        $dto->orderDate = $order?->created_at ? (string) $order->created_at : null;
        $dto->channelName = $order?->channel_name;
        $dto->customerName = $order?->customer_full_name;
        $dto->customerEmail = $order?->customer_email;

        $paymentMethod = $order?->payment?->method;
        $dto->paymentMethod = $paymentMethod;
        $dto->paymentTitle = $paymentMethod
            ? core()->getConfigData('sales.payment_methods.'.$paymentMethod.'.title')
            : null;
        $dto->shippingMethod = $order?->shipping_method;
        $dto->shippingTitle = $order?->shipping_title;

        $dto->billingAddress = $this->mapAddress($billing);
        $dto->shippingAddress = $this->mapAddress($order?->shipping_address);

        $dto->items = $refund->items
            ? $refund->items->map(fn ($row) => $this->mapItem($row, $currency))->all()
            : [];

        return $dto;
    }

    private function refundMoney($amount, string $currency): string
    {
        try {
            return core()->formatPrice((float) $amount, $currency ?: null);
        } catch (\Throwable $e) {
            try {
                return core()->formatBasePrice((float) $amount);
            } catch (\Throwable $e) {
                return (string) $amount;
            }
        }
    }
}
