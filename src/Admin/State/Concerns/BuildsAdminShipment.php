<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Webkul\BagistoApi\Admin\Models\AdminShipment;
use Webkul\Sales\Models\Shipment;

trait BuildsAdminShipment
{
    use MapsOrderActionItems;
    use MapsOrderAddress;

    protected function buildAdminShipment(Shipment $shipment): AdminShipment
    {
        $order = $shipment->order;
        $currency = $order?->order_currency_code ?? '';

        $shippingAddress = $order?->shipping_address;
        $shippedTo = $shippingAddress
            ? trim(($shippingAddress->first_name ?? '').' '.($shippingAddress->last_name ?? ''))
            : null;

        $dto = new AdminShipment;
        $dto->id = (int) $shipment->id;
        $dto->orderId = $shipment->order_id !== null ? (int) $shipment->order_id : null;
        $dto->orderIncrementId = $order?->increment_id;
        $dto->shippedTo = $shippedTo !== '' ? $shippedTo : null;
        $dto->orderDate = $order?->created_at ? (string) $order->created_at : null;
        $dto->orderStatus = $order?->status;
        $dto->orderStatusLabel = $order?->status_label;
        $dto->channelName = $order?->channel_name;
        $dto->customerName = $order?->customer_full_name;
        $dto->customerEmail = $order?->customer_email;

        $paymentMethod = $order?->payment?->method;
        $dto->paymentMethod = $paymentMethod;
        $dto->paymentTitle = $paymentMethod ? core()->getConfigData('sales.payment_methods.'.$paymentMethod.'.title') : null;
        $dto->orderCurrencyCode = $order?->order_currency_code;
        $dto->shippingMethod = $order?->shipping_method;
        $dto->shippingTitle = $order?->shipping_title;
        $dto->baseShippingAmount = $order && $order->base_shipping_amount !== null ? (float) $order->base_shipping_amount : null;
        $dto->formattedBaseShippingAmount = $order && $order->base_shipping_amount !== null ? $this->safeFormatBasePrice((float) $order->base_shipping_amount) : null;

        $dto->status = $shipment->status !== null ? (string) $shipment->status : null;
        $dto->totalQty = $shipment->total_qty !== null ? (int) $shipment->total_qty : null;
        $dto->totalWeight = $shipment->total_weight !== null ? (float) $shipment->total_weight : null;
        $dto->carrierCode = $shipment->carrier_code;
        $dto->carrierTitle = $shipment->carrier_title;
        $dto->trackNumber = $shipment->track_number;
        $dto->emailSent = $shipment->email_sent !== null ? (bool) $shipment->email_sent : null;
        $dto->inventorySourceId = $shipment->inventory_source_id !== null ? (int) $shipment->inventory_source_id : null;
        $dto->inventorySourceName = $shipment->inventory_source_name;
        $dto->createdAt = $shipment->created_at ? (string) $shipment->created_at : null;
        $dto->updatedAt = $shipment->updated_at ? (string) $shipment->updated_at : null;

        $dto->billingAddress = $this->mapAddress($order?->billing_address);
        $dto->shippingAddress = $this->mapAddress($shippingAddress);

        $dto->items = $shipment->items
            ? $shipment->items->map(fn ($row) => $this->mapItem($row, $currency))->all()
            : [];

        return $dto;
    }

    protected function safeFormatBasePrice(float $amount): ?string
    {
        try {
            return core()->formatBasePrice($amount);
        } catch (\Throwable) {
            return (string) $amount;
        }
    }
}
