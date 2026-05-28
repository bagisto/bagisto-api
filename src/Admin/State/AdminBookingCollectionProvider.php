<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminBookingListDto;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

/**
 * GET /api/admin/bookings + adminBookings cursor query.
 *
 * Mirrors Webkul\Admin\DataGrids\Sales\BookingDataGrid — one row per
 * booking line from the `bookings` table, with the linked order
 * increment_id and slot window (from/to as unix timestamps).
 */
class AdminBookingCollectionProvider extends AbstractAdminCollectionProvider
{
    use ChecksAdminPermission;

    protected const PERMISSION = 'sales.bookings.view';

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): \ApiPlatform\Laravel\Eloquent\Paginator
    {
        $this->authorizedAdmin(self::PERMISSION);

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return ['id', 'order_id', 'qty', 'from', 'to', 'created_at'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('bookings')
            ->leftJoin('orders', 'bookings.order_id', '=', 'orders.id')
            ->leftJoin('products', 'bookings.product_id', '=', 'products.id')
            ->select(
                'bookings.id as id',
                'bookings.order_id as order_id',
                'orders.increment_id as order_increment_id',
                'bookings.order_item_id as order_item_id',
                'bookings.product_id as product_id',
                'products.sku as product_sku',
                'bookings.qty as qty',
                'bookings.from as from_ts',
                'bookings.to as to_ts',
                'orders.created_at as created_at',
            );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['id'])) {
            $ids = is_array($args['id']) ? $args['id'] : array_filter(array_map('trim', explode(',', (string) $args['id'])));
            if (! empty($ids)) {
                $query->whereIn('bookings.id', $ids);
            }
        }

        if (! empty($args['order_id'])) {
            $query->where('orders.increment_id', 'like', '%'.$args['order_id'].'%');
        }

        if (isset($args['qty']) && $args['qty'] !== '') {
            $query->where('bookings.qty', $args['qty']);
        }

        if (! empty($args['product_id'])) {
            $query->where('bookings.product_id', $args['product_id']);
        }

        // from/to are integer unix timestamps — accept ISO strings and convert.
        if (! empty($args['from_from'])) {
            $query->where('bookings.from', '>=', strtotime((string) $args['from_from']));
        }
        if (! empty($args['from_to'])) {
            $query->where('bookings.from', '<=', strtotime((string) $args['from_to']));
        }
        if (! empty($args['to_from'])) {
            $query->where('bookings.to', '>=', strtotime((string) $args['to_from']));
        }
        if (! empty($args['to_to'])) {
            $query->where('bookings.to', '<=', strtotime((string) $args['to_to']));
        }

        $from = $args['created_at_from'] ?? $args['date_from'] ?? null;
        $to = $args['created_at_to'] ?? $args['date_to'] ?? null;
        if ($from) {
            $query->where('orders.created_at', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            $query->where('orders.created_at', '<=', Carbon::parse($to)->endOfDay());
        }
    }

    protected function applySort($query, array $args): void
    {
        [$col, $dir] = $this->resolveSort($args);

        $map = [
            'id'         => 'bookings.id',
            'order_id'   => 'orders.increment_id',
            'qty'        => 'bookings.qty',
            'from'       => 'bookings.from',
            'to'         => 'bookings.to',
            'created_at' => 'orders.created_at',
        ];

        $query->orderBy($map[$col] ?? 'bookings.id', $dir);
    }

    protected function mapRow(object $row): AdminBookingListDto
    {
        $dto = new AdminBookingListDto;
        $dto->id = (int) $row->id;
        $dto->orderId = $row->order_id !== null ? (int) $row->order_id : null;
        $dto->orderIncrementId = $row->order_increment_id;
        $dto->orderItemId = $row->order_item_id !== null ? (int) $row->order_item_id : null;
        $dto->productId = $row->product_id !== null ? (int) $row->product_id : null;
        $dto->productSku = $row->product_sku;
        $dto->productName = null; // populated by item provider; listing leaves null to avoid the product_flat join.
        $dto->qty = $row->qty !== null ? (int) $row->qty : null;
        $dto->from = $row->from_ts !== null ? (int) $row->from_ts : null;
        $dto->to = $row->to_ts !== null ? (int) $row->to_ts : null;
        $dto->fromFormatted = $row->from_ts ? Carbon::createFromTimestamp((int) $row->from_ts)->format('d M, Y H:iA') : null;
        $dto->toFormatted = $row->to_ts ? Carbon::createFromTimestamp((int) $row->to_ts)->format('d M, Y H:iA') : null;
        $dto->createdAt = $row->created_at ? (string) $row->created_at : null;

        return $dto;
    }
}
