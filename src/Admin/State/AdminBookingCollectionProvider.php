<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminBooking;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminBooking;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminBookingCollectionProvider extends AbstractAdminCollectionProvider
{
    use BuildsAdminBooking;
    use ChecksAdminPermission;

    protected const PERMISSION = 'sales.bookings.view';

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
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
            ->leftJoin('order_items', 'bookings.order_item_id', '=', 'order_items.id')
            ->leftJoin('products', 'bookings.product_id', '=', 'products.id')
            ->leftJoin('booking_products', 'bookings.product_id', '=', 'booking_products.product_id')
            ->select($this->adminBookingSelect());
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
            'id' => 'bookings.id',
            'order_id' => 'orders.increment_id',
            'qty' => 'bookings.qty',
            'from' => 'bookings.from',
            'to' => 'bookings.to',
            'created_at' => 'orders.created_at',
        ];

        $query->orderBy($map[$col] ?? 'bookings.id', $dir);
    }

    protected function mapRow(object $row): AdminBooking
    {
        return $this->buildAdminBooking($row);
    }
}
