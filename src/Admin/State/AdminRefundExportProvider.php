<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminExport;
use Webkul\Sales\Models\OrderAddress;

class AdminRefundExportProvider implements ProviderInterface
{
    use StreamsAdminExport;

    protected function exportPermission(): string
    {
        return 'sales.refunds.view';
    }

    protected function exportFilename(): string
    {
        return 'refunds';
    }

    protected function exportHeaders(): array
    {
        return ['ID', 'Order ID', 'Refunded Amount', 'Billed To', 'Refund Date'];
    }

    protected function exportRow(object $row): array
    {
        return [
            $row->id,
            $row->order_increment_id,
            $this->safeFormatBasePrice($row->base_grand_total),
            trim((string) ($row->billed_to ?? '')),
            $row->created_at,
        ];
    }

    protected function exportQuery(array $args)
    {
        $prefix = DB::getTablePrefix();

        $query = DB::table('refunds')
            ->leftJoin('orders', 'refunds.order_id', '=', 'orders.id')
            ->leftJoin('addresses as order_address_billing', function ($join) {
                $join->on('order_address_billing.order_id', '=', 'orders.id')
                    ->where('order_address_billing.address_type', OrderAddress::ADDRESS_TYPE_BILLING);
            })
            ->select(
                'refunds.id',
                'refunds.base_grand_total',
                'refunds.created_at',
                'orders.increment_id as order_increment_id',
            )
            ->addSelect(DB::raw('CONCAT('.$prefix.'order_address_billing.first_name, " ", '.$prefix.'order_address_billing.last_name) as billed_to'))
            ->orderByDesc('refunds.id');

        if (! empty($args['id'])) {
            $ids = is_array($args['id']) ? $args['id'] : array_filter(array_map('trim', explode(',', (string) $args['id'])));
            if (! empty($ids)) {
                $query->whereIn('refunds.id', $ids);
            }
        }
        if (! empty($args['order_id'])) {
            $query->where('orders.increment_id', 'like', '%'.$args['order_id'].'%');
        }
        if (! empty($args['state'])) {
            $query->where('refunds.state', $args['state']);
        }
        if (isset($args['base_grand_total_from']) && $args['base_grand_total_from'] !== '') {
            $query->where('refunds.base_grand_total', '>=', (float) $args['base_grand_total_from']);
        }
        if (isset($args['base_grand_total_to']) && $args['base_grand_total_to'] !== '') {
            $query->where('refunds.base_grand_total', '<=', (float) $args['base_grand_total_to']);
        }
        if (! empty($args['billed_to'])) {
            $query->whereRaw('CONCAT('.$prefix.'order_address_billing.first_name, " ", '.$prefix.'order_address_billing.last_name) like ?', ['%'.$args['billed_to'].'%']);
        }
        $from = $args['created_at_from'] ?? $args['date_from'] ?? null;
        $to = $args['created_at_to'] ?? $args['date_to'] ?? null;
        if ($from) {
            $query->where('refunds.created_at', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            $query->where('refunds.created_at', '<=', Carbon::parse($to)->endOfDay());
        }

        return $query;
    }
}
