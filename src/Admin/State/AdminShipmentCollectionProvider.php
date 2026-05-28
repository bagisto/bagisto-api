<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminShipmentListDto;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\Sales\Models\OrderAddress;

/**
 * GET /api/admin/shipments + adminShipments cursor query.
 *
 * DataGrid parity with OrderShipmentDataGrid. Filters: id (shipment_id),
 * order_id (partial on increment_id), total_qty, inventory_source_name,
 * shipped_to, order_date (range), created_at (range).
 */
class AdminShipmentCollectionProvider extends AbstractAdminCollectionProvider
{
    use ChecksAdminPermission;

    protected const PERMISSION = 'sales.shipments.view';

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): \ApiPlatform\Laravel\Eloquent\Paginator
    {
        $this->authorizedAdmin(self::PERMISSION);

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return ['id', 'order_id', 'total_qty', 'inventory_source_name', 'shipped_to', 'order_date', 'created_at'];
    }

    protected function buildQuery(array $args)
    {
        $prefix = DB::getTablePrefix();

        return DB::table('shipments')
            ->leftJoin('addresses as order_address_shipping', function ($join) {
                $join->on('order_address_shipping.order_id', '=', 'shipments.order_id')
                    ->where('order_address_shipping.address_type', OrderAddress::ADDRESS_TYPE_SHIPPING);
            })
            ->leftJoin('orders', 'shipments.order_id', '=', 'orders.id')
            ->leftJoin('inventory_sources', 'shipments.inventory_source_id', '=', 'inventory_sources.id')
            ->select(
                'shipments.id as id',
                'shipments.order_id as order_id',
                'orders.increment_id as order_increment_id',
                'shipments.total_qty as total_qty',
                'orders.created_at as order_date',
                'shipments.created_at as created_at',
            )
            ->addSelect(DB::raw('CONCAT('.$prefix.'order_address_shipping.first_name, " ", '.$prefix.'order_address_shipping.last_name) as shipped_to'))
            ->selectRaw('IF('.$prefix.'shipments.inventory_source_id IS NOT NULL, '.$prefix.'inventory_sources.name, '.$prefix.'shipments.inventory_source_name) as inventory_source_name');
    }

    protected function applyFilters($query, array $args): void
    {
        $prefix = DB::getTablePrefix();

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

        [$from, $to] = $this->resolveDateRange($args, 'created_at');
        if ($from) {
            $query->where('shipments.created_at', '>=', $from->startOfDay());
        }
        if ($to) {
            $query->where('shipments.created_at', '<=', $to->endOfDay());
        }

        if (! empty($args['order_date_from'])) {
            $query->where('orders.created_at', '>=', Carbon::parse($args['order_date_from'])->startOfDay());
        }
        if (! empty($args['order_date_to'])) {
            $query->where('orders.created_at', '<=', Carbon::parse($args['order_date_to'])->endOfDay());
        }
    }

    protected function applySort($query, array $args): void
    {
        [$col, $dir] = $this->resolveSort($args);

        $map = [
            'id'                    => 'shipments.id',
            'order_id'              => 'orders.increment_id',
            'total_qty'             => 'shipments.total_qty',
            'order_date'            => 'orders.created_at',
            'created_at'            => 'shipments.created_at',
            'inventory_source_name' => 'inventory_source_name',
            'shipped_to'            => 'shipped_to',
        ];

        $query->orderBy($map[$col] ?? 'shipments.id', $dir);
    }

    protected function mapRow(object $row): AdminShipmentListDto
    {
        $dto = new AdminShipmentListDto;
        $dto->id = (int) $row->id;
        $dto->orderId = $row->order_id !== null ? (int) $row->order_id : null;
        $dto->orderIncrementId = $row->order_increment_id;
        $dto->totalQty = $row->total_qty !== null ? (int) $row->total_qty : null;
        $dto->inventorySourceName = $row->inventory_source_name;
        $dto->shippedTo = trim((string) $row->shipped_to) ?: null;
        $dto->orderDate = $row->order_date ? (string) $row->order_date : null;
        $dto->createdAt = $row->created_at ? (string) $row->created_at : null;

        return $dto;
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    protected function resolveDateRange(array $args, string $prefix): array
    {
        $from = $args[$prefix.'_from'] ?? $args['date_from'] ?? null;
        $to = $args[$prefix.'_to'] ?? $args['date_to'] ?? null;

        return [
            $from ? Carbon::parse($from) : null,
            $to ? Carbon::parse($to) : null,
        ];
    }
}
