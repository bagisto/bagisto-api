<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminShipment;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\MapsOrderAddress;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderAddress;

class AdminShipmentCollectionProvider extends AbstractAdminCollectionProvider
{
    use ChecksAdminPermission;
    use MapsOrderAddress;

    protected const PERMISSION = 'sales.shipments.view';

    private array $billingByOrder = [];

    private array $shippingByOrder = [];

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
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
                'shipments.status as status',
                'shipments.total_qty as total_qty',
                'shipments.total_weight as total_weight',
                'shipments.carrier_code as carrier_code',
                'shipments.carrier_title as carrier_title',
                'shipments.track_number as track_number',
                'shipments.email_sent as email_sent',
                'shipments.inventory_source_id as inventory_source_id',
                'shipments.created_at as created_at',
                'shipments.updated_at as updated_at',
                'orders.increment_id as order_increment_id',
                'orders.status as order_status',
                'orders.channel_name as order_channel_name',
                'orders.created_at as order_date',
                'orders.customer_email as order_customer_email',
                'orders.customer_first_name as order_customer_first_name',
                'orders.customer_last_name as order_customer_last_name',
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
            'id' => 'shipments.id',
            'order_id' => 'orders.increment_id',
            'total_qty' => 'shipments.total_qty',
            'order_date' => 'orders.created_at',
            'created_at' => 'shipments.created_at',
            'inventory_source_name' => 'inventory_source_name',
            'shipped_to' => 'shipped_to',
        ];

        $query->orderBy($map[$col] ?? 'shipments.id', $dir);
    }

    protected function mapRows($rows): array
    {
        $this->billingByOrder = [];
        $this->shippingByOrder = [];

        $orderIds = $rows->pluck('order_id')->filter()->unique()->values()->all();

        if (! empty($orderIds)) {
            $addresses = DB::table('addresses')
                ->whereIn('order_id', $orderIds)
                ->whereIn('address_type', ['order_billing', 'order_shipping'])
                ->get();

            foreach ($addresses as $address) {
                if ($address->address_type === 'order_billing' && ! isset($this->billingByOrder[$address->order_id])) {
                    $this->billingByOrder[$address->order_id] = $address;
                } elseif ($address->address_type === 'order_shipping' && ! isset($this->shippingByOrder[$address->order_id])) {
                    $this->shippingByOrder[$address->order_id] = $address;
                }
            }
        }

        return $rows->map(fn ($row) => $this->mapRow($row))->all();
    }

    protected function mapRow(object $row): AdminShipment
    {
        $name = trim((string) ($row->order_customer_first_name ?? '').' '.($row->order_customer_last_name ?? ''));

        $dto = new AdminShipment;
        $dto->id = (int) $row->id;
        $dto->orderId = $row->order_id !== null ? (int) $row->order_id : null;
        $dto->orderIncrementId = $row->order_increment_id;
        $dto->shippedTo = trim((string) $row->shipped_to) ?: null;
        $dto->orderDate = $row->order_date ? (string) $row->order_date : null;
        $dto->orderStatus = $row->order_status;
        $dto->orderStatusLabel = $this->orderStatusLabel($row->order_status);
        $dto->channelName = $row->order_channel_name;
        $dto->customerName = $name !== '' ? $name : null;
        $dto->customerEmail = $row->order_customer_email;
        $dto->status = $row->status !== null ? (string) $row->status : null;
        $dto->totalQty = $row->total_qty !== null ? (int) $row->total_qty : null;
        $dto->totalWeight = $row->total_weight !== null ? (float) $row->total_weight : null;
        $dto->carrierCode = $row->carrier_code;
        $dto->carrierTitle = $row->carrier_title;
        $dto->trackNumber = $row->track_number;
        $dto->emailSent = $row->email_sent !== null ? (bool) $row->email_sent : null;
        $dto->inventorySourceId = $row->inventory_source_id !== null ? (int) $row->inventory_source_id : null;
        $dto->inventorySourceName = $row->inventory_source_name;
        $dto->createdAt = $row->created_at ? (string) $row->created_at : null;
        $dto->updatedAt = $row->updated_at ? (string) $row->updated_at : null;

        $dto->billingAddress = $this->mapAddress($this->billingByOrder[$row->order_id] ?? null);
        $dto->shippingAddress = $this->mapAddress($this->shippingByOrder[$row->order_id] ?? null);

        $dto->items = [];

        return $dto;
    }

    protected function resolveDateRange(array $args, string $prefix): array
    {
        $from = $args[$prefix.'_from'] ?? $args['date_from'] ?? null;
        $to = $args[$prefix.'_to'] ?? $args['date_to'] ?? null;

        return [
            $from ? Carbon::parse($from) : null,
            $to ? Carbon::parse($to) : null,
        ];
    }

    private function orderStatusLabel(?string $status): ?string
    {
        if (! $status) {
            return null;
        }

        try {
            $order = new Order;
            $order->status = $status;

            return $order->status_label;
        } catch (\Throwable) {
            return $status;
        }
    }
}
