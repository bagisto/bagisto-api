<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminBooking;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminBooking;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\MapsOrderAddress;
use Webkul\Sales\Models\Order;

class AdminBookingItemProvider extends AbstractAdminItemProvider
{
    use BuildsAdminBooking;
    use ChecksAdminPermission;
    use MapsOrderAddress;

    protected const PERMISSION = 'sales.bookings.view';

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $this->authorizedAdmin(self::PERMISSION);

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.sales.booking.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        $row = DB::table('bookings')
            ->leftJoin('orders', 'bookings.order_id', '=', 'orders.id')
            ->leftJoin('order_items', 'bookings.order_item_id', '=', 'order_items.id')
            ->leftJoin('products', 'bookings.product_id', '=', 'products.id')
            ->leftJoin('booking_products', 'bookings.product_id', '=', 'booking_products.product_id')
            ->where('bookings.id', $id)
            ->select($this->adminBookingSelect())
            ->first();

        return $row ?: null;
    }

    protected function mapToDto(object $entity): AdminBooking
    {
        $booking = $this->buildAdminBooking($entity);

        if ($entity->order_id) {
            $this->attachOrderDocuments($booking, (int) $entity->order_id);
        }

        return $booking;
    }

    protected function attachOrderDocuments(AdminBooking $booking, int $orderId): void
    {
        $order = Order::with(['addresses', 'payment', 'invoices', 'shipments', 'refunds'])->find($orderId);

        if (! $order) {
            return;
        }

        $booking->billingAddress = $this->mapAddress($order->billing_address);
        $booking->shippingAddress = $this->mapAddress($order->shipping_address);

        $method = $order->payment?->method;
        $booking->paymentMethod = $method;
        $booking->paymentTitle = $method ? core()->getConfigData('sales.payment_methods.'.$method.'.title') : null;
        $booking->shippingMethod = $order->shipping_method;
        $booking->shippingTitle = $order->shipping_title;

        $booking->invoices = $order->invoices->map(fn ($invoice) => [
            'id' => (int) $invoice->id,
            'incrementId' => $invoice->increment_id !== null ? (string) $invoice->increment_id : (string) $invoice->id,
            'state' => $invoice->state,
            'baseGrandTotal' => $invoice->base_grand_total !== null ? (float) $invoice->base_grand_total : null,
            'formattedBaseGrandTotal' => $invoice->base_grand_total !== null ? $this->safeFormatBasePrice((float) $invoice->base_grand_total) : null,
            'createdAt' => $invoice->created_at ? (string) $invoice->created_at : null,
        ])->all();

        $booking->shipments = $order->shipments->map(fn ($shipment) => [
            'id' => (int) $shipment->id,
            'totalQty' => $shipment->total_qty !== null ? (int) $shipment->total_qty : null,
            'carrierTitle' => $shipment->carrier_title,
            'trackNumber' => $shipment->track_number,
            'createdAt' => $shipment->created_at ? (string) $shipment->created_at : null,
        ])->all();

        $booking->refunds = $order->refunds->map(fn ($refund) => [
            'id' => (int) $refund->id,
            'state' => $refund->state,
            'baseGrandTotal' => $refund->base_grand_total !== null ? (float) $refund->base_grand_total : null,
            'formattedBaseGrandTotal' => $refund->base_grand_total !== null ? $this->safeFormatBasePrice((float) $refund->base_grand_total) : null,
            'createdAt' => $refund->created_at ? (string) $refund->created_at : null,
        ])->all();
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
