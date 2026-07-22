<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminExport;

class AdminBookingExportProvider implements ProviderInterface
{
    use StreamsAdminExport;

    protected function exportPermission(): string
    {
        return 'sales.bookings.view';
    }

    protected function exportFilename(): string
    {
        return 'bookings';
    }

    protected function exportHeaders(): array
    {
        return ['ID', 'Order ID', 'Qty', 'From', 'To', 'Booking Date'];
    }

    protected function exportRow(object $row): array
    {
        return [
            $row->id,
            $row->order_increment_id,
            $row->qty,
            $row->from_ts !== null ? Carbon::createFromTimestamp((int) $row->from_ts)->format('d M, Y H:iA') : '',
            $row->to_ts !== null ? Carbon::createFromTimestamp((int) $row->to_ts)->format('d M, Y H:iA') : '',
            $row->order_created_at,
        ];
    }

    protected function exportQuery(array $args)
    {
        $query = DB::table('bookings')
            ->leftJoin('orders', 'bookings.order_id', '=', 'orders.id')
            ->select(
                'bookings.id',
                'bookings.order_id',
                'bookings.qty',
                'bookings.from as from_ts',
                'bookings.to as to_ts',
                'orders.increment_id as order_increment_id',
                'orders.created_at as order_created_at',
            )
            ->orderByDesc('bookings.id');

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

        return $query;
    }
}
