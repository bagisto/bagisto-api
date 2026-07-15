<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Illuminate\Support\Carbon;
use Webkul\BagistoApi\Admin\Models\AdminBooking;

trait BuildsAdminBooking
{
    protected function buildAdminBooking(object $row): AdminBooking
    {
        $booking = new AdminBooking;

        $booking->id = (int) $row->id;
        $booking->orderId = $row->order_id !== null ? (int) $row->order_id : null;
        $booking->orderIncrementId = $row->order_increment_id ?? null;
        $booking->orderItemId = $row->order_item_id !== null ? (int) $row->order_item_id : null;
        $booking->productId = $row->product_id !== null ? (int) $row->product_id : null;
        $booking->productSku = $row->product_sku ?? null;
        $booking->productName = $row->order_item_name ?? null;
        $booking->bookingType = $row->booking_type ?? null;
        $booking->qty = $row->qty !== null ? (int) $row->qty : null;
        $booking->from = $row->from_ts !== null ? (int) $row->from_ts : null;
        $booking->to = $row->to_ts !== null ? (int) $row->to_ts : null;
        $booking->fromFormatted = $row->from_ts ? Carbon::createFromTimestamp((int) $row->from_ts)->format('d M, Y H:iA') : null;
        $booking->toFormatted = $row->to_ts ? Carbon::createFromTimestamp((int) $row->to_ts)->format('d M, Y H:iA') : null;
        $booking->bookingProductEventTicketId = $row->event_ticket_id !== null ? (int) $row->event_ticket_id : null;
        $booking->createdAt = $row->order_created_at ? (string) $row->order_created_at : null;

        if ($row->order_id) {
            $name = trim(($row->order_customer_first_name ?? '').' '.($row->order_customer_last_name ?? ''));

            $booking->order = [
                'id' => (int) $row->order_id,
                'incrementId' => $row->order_increment_id ?? null,
                'status' => $row->order_status ?? null,
                'customerName' => $name !== '' ? $name : null,
                'customerEmail' => $row->order_customer_email ?? null,
                'grandTotal' => isset($row->order_grand_total) && $row->order_grand_total !== null ? (float) $row->order_grand_total : null,
                'orderCurrencyCode' => $row->order_currency_code ?? null,
            ];
        }

        if ($row->order_item_id) {
            $booking->orderItem = [
                'id' => (int) $row->order_item_id,
                'sku' => $row->order_item_sku ?? null,
                'name' => $row->order_item_name ?? null,
                'qtyOrdered' => isset($row->order_item_qty_ordered) && $row->order_item_qty_ordered !== null ? (float) $row->order_item_qty_ordered : null,
            ];
        }

        return $booking;
    }

    protected function adminBookingSelect(): array
    {
        return [
            'bookings.id as id',
            'bookings.order_id as order_id',
            'orders.increment_id as order_increment_id',
            'orders.status as order_status',
            'orders.customer_email as order_customer_email',
            'orders.customer_first_name as order_customer_first_name',
            'orders.customer_last_name as order_customer_last_name',
            'orders.grand_total as order_grand_total',
            'orders.order_currency_code as order_currency_code',
            'orders.created_at as order_created_at',
            'bookings.order_item_id as order_item_id',
            'order_items.sku as order_item_sku',
            'order_items.name as order_item_name',
            'order_items.qty_ordered as order_item_qty_ordered',
            'bookings.product_id as product_id',
            'products.sku as product_sku',
            'booking_products.type as booking_type',
            'bookings.qty as qty',
            'bookings.from as from_ts',
            'bookings.to as to_ts',
            'bookings.booking_product_event_ticket_id as event_ticket_id',
        ];
    }
}
