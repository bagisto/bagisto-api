<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminExport;
use Webkul\Sales\Models\OrderAddress;

class AdminShipmentExportProvider implements ProviderInterface
{
    use StreamsAdminExport;

    protected function exportPermission(): string
    {
        return 'sales.shipments.view';
    }

    protected function exportFilename(): string
    {
        return 'shipments';
    }

    protected function exportHeaders(): array
    {
        return ['ID', 'Order ID', 'Total Qty', 'Inventory Source', 'Shipped To', 'Shipment Date'];
    }

    protected function exportRow(object $row): array
    {
        return [
            $row->id,
            $row->order_increment_id,
            $row->total_qty,
            $row->inventory_source_name,
            trim((string) ($row->shipped_to ?? '')),
            $row->created_at,
        ];
    }

    protected function exportQuery(array $args)
    {
        $prefix = DB::getTablePrefix();

        $query = DB::table('shipments')
            ->leftJoin('addresses as order_address_shipping', function ($join) {
                $join->on('order_address_shipping.order_id', '=', 'shipments.order_id')
                    ->where('order_address_shipping.address_type', OrderAddress::ADDRESS_TYPE_SHIPPING);
            })
            ->leftJoin('orders', 'shipments.order_id', '=', 'orders.id')
            ->leftJoin('inventory_sources', 'shipments.inventory_source_id', '=', 'inventory_sources.id')
            ->select(
                'shipments.id as id',
                'shipments.total_qty as total_qty',
                'shipments.created_at as created_at',
                'orders.increment_id as order_increment_id',
                'orders.created_at as order_date',
            )
            ->addSelect(DB::raw('CONCAT('.$prefix.'order_address_shipping.first_name, " ", '.$prefix.'order_address_shipping.last_name) as shipped_to'))
            ->selectRaw('IF('.$prefix.'shipments.inventory_source_id IS NOT NULL, '.$prefix.'inventory_sources.name, '.$prefix.'shipments.inventory_source_name) as inventory_source_name')
            ->orderByDesc('shipments.id');

        if (! empty($args['id'])) {
            $ids = is_array($args['id']) ? $args['id'] : array_filter(array_map('trim', explode(',', (string) $args['id'])));
            if (! empty($ids)) {
                $query->whereIn('shipments.id', $ids);
            }
        }
        if (! empty($args['order_id'])) {
            $query->where('orders.increment_id', 'like', '%'.$args['order_id'].'%');
        }
        if (isset($args['total_qty']) && $args['total_qty'] !== '') {
            $query->where('shipments.total_qty', $args['total_qty']);
        }
        if (! empty($args['inventory_source_name'])) {
            $query->whereRaw('IF('.$prefix.'shipments.inventory_source_id IS NOT NULL, '.$prefix.'inventory_sources.name, '.$prefix.'shipments.inventory_source_name) like ?', ['%'.$args['inventory_source_name'].'%']);
        }
        if (! empty($args['shipped_to'])) {
            $query->whereRaw('CONCAT('.$prefix.'order_address_shipping.first_name, " ", '.$prefix.'order_address_shipping.last_name) like ?', ['%'.$args['shipped_to'].'%']);
        }
        $from = $args['created_at_from'] ?? $args['date_from'] ?? null;
        $to = $args['created_at_to'] ?? $args['date_to'] ?? null;
        if ($from) {
            $query->where('shipments.created_at', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            $query->where('shipments.created_at', '<=', Carbon::parse($to)->endOfDay());
        }
        if (! empty($args['order_date_from'])) {
            $query->where('orders.created_at', '>=', Carbon::parse($args['order_date_from'])->startOfDay());
        }
        if (! empty($args['order_date_to'])) {
            $query->where('orders.created_at', '<=', Carbon::parse($args['order_date_to'])->endOfDay());
        }

        return $query;
    }
}
