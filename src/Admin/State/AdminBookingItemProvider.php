<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminBookingDetailDto;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

/**
 * GET /api/admin/bookings/{id} + adminBooking(id:) query.
 *
 * Returns booking row + booking-product sub-type (default/appointment/
 * event/rental/table) + slim order + order-item summary.
 */
class AdminBookingItemProvider extends AbstractAdminItemProvider
{
    use ChecksAdminPermission;

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
            ->select(
                'bookings.id as id',
                'bookings.order_id as order_id',
                'orders.increment_id as order_increment_id',
                'orders.status as order_status',
                'orders.customer_email as order_customer_email',
                'orders.grand_total as order_grand_total',
                'orders.order_currency_code as order_currency_code',
                'bookings.order_item_id as order_item_id',
                'order_items.sku as order_item_sku',
                'order_items.name as order_item_name',
                'order_items.qty_ordered as order_item_qty_ordered',
                'bookings.product_id as product_id',
                'products.sku as product_sku',
                'bookings.qty as qty',
                'bookings.from as from_ts',
                'bookings.to as to_ts',
                'bookings.booking_product_event_ticket_id as event_ticket_id',
                'booking_products.type as booking_type',
                'orders.created_at as created_at',
            )
            ->first();

        return $row ?: null;
    }

    protected function mapToDto(object $entity): AdminBookingDetailDto
    {
        $dto = new AdminBookingDetailDto;
        $dto->id = (int) $entity->id;
        $dto->orderId = $entity->order_id !== null ? (int) $entity->order_id : null;
        $dto->orderIncrementId = $entity->order_increment_id;
        $dto->orderItemId = $entity->order_item_id !== null ? (int) $entity->order_item_id : null;
        $dto->productId = $entity->product_id !== null ? (int) $entity->product_id : null;
        $dto->productSku = $entity->product_sku;
        $dto->productName = $entity->order_item_name;
        $dto->bookingType = $entity->booking_type;
        $dto->qty = $entity->qty !== null ? (int) $entity->qty : null;
        $dto->from = $entity->from_ts !== null ? (int) $entity->from_ts : null;
        $dto->to = $entity->to_ts !== null ? (int) $entity->to_ts : null;
        $dto->fromFormatted = $entity->from_ts ? Carbon::createFromTimestamp((int) $entity->from_ts)->format('d M, Y H:iA') : null;
        $dto->toFormatted = $entity->to_ts ? Carbon::createFromTimestamp((int) $entity->to_ts)->format('d M, Y H:iA') : null;
        $dto->bookingProductEventTicketId = $entity->event_ticket_id !== null ? (int) $entity->event_ticket_id : null;
        $dto->createdAt = $entity->created_at ? (string) $entity->created_at : null;

        if ($entity->order_id) {
            $dto->order = [
                'id'                => (int) $entity->order_id,
                'incrementId'       => $entity->order_increment_id,
                'status'            => $entity->order_status,
                'customerEmail'     => $entity->order_customer_email,
                'grandTotal'        => $entity->order_grand_total !== null ? (float) $entity->order_grand_total : null,
                'orderCurrencyCode' => $entity->order_currency_code,
            ];
        }

        if ($entity->order_item_id) {
            $dto->orderItem = [
                'id'          => (int) $entity->order_item_id,
                'sku'         => $entity->order_item_sku,
                'name'        => $entity->order_item_name,
                'qtyOrdered'  => $entity->order_item_qty_ordered !== null ? (float) $entity->order_item_qty_ordered : null,
            ];
        }

        return $dto;
    }
}
