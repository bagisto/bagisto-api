<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\ResolvesAdminDateRange;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminCsvExport;

class AdminOrderExportProvider implements ProviderInterface
{
    use ResolvesAdminDateRange;
    use StreamsAdminCsvExport;

    protected function exportPermission(): string
    {
        return 'sales.orders.view';
    }

    protected function exportFilename(): string
    {
        return 'orders.csv';
    }

    protected function exportHeaders(): array
    {
        return ['ID', 'Status', 'Grand Total', 'Payment Method', 'Channel', 'Customer', 'Email', 'Order Date'];
    }

    protected function exportRow(object $row): array
    {
        return [
            $row->increment_id ?: $row->id,
            $row->status,
            $this->safeFormatBasePrice($row->base_grand_total),
            $row->payment_method_title ?: $row->payment_method,
            $row->channel_name,
            trim((string) $row->customer_name),
            $row->customer_email,
            $row->created_at,
        ];
    }

    protected function exportQuery(array $args)
    {
        $prefix = DB::getTablePrefix();

        $query = DB::table('orders')
            ->leftJoin('order_payment', 'order_payment.order_id', '=', 'orders.id')
            ->select(
                'orders.id',
                'orders.increment_id',
                'orders.status',
                'orders.base_grand_total',
                'orders.channel_name',
                'orders.customer_email',
                'orders.created_at',
                'order_payment.method as payment_method',
                'order_payment.method_title as payment_method_title',
            )
            ->addSelect(DB::raw('CONCAT('.$prefix.'orders.customer_first_name, " ", '.$prefix.'orders.customer_last_name) as customer_name'))
            ->orderByDesc('orders.id');

        if (! empty($args['order_id'])) {
            $query->where('orders.increment_id', 'like', '%'.$args['order_id'].'%');
        }
        if (! empty($args['status'])) {
            $query->where('orders.status', $args['status']);
        }
        if (isset($args['grand_total']) && $args['grand_total'] !== '') {
            $query->where('orders.base_grand_total', $args['grand_total']);
        }
        if (isset($args['grand_total_from']) && $args['grand_total_from'] !== '') {
            $query->where('orders.base_grand_total', '>=', (float) $args['grand_total_from']);
        }
        if (isset($args['grand_total_to']) && $args['grand_total_to'] !== '') {
            $query->where('orders.base_grand_total', '<=', (float) $args['grand_total_to']);
        }
        if (isset($args['channel']) && $args['channel'] !== '') {
            $query->where('orders.channel_id', $args['channel']);
        }
        if (! empty($args['customer'])) {
            $query->whereRaw('CONCAT('.$prefix.'orders.customer_first_name, " ", '.$prefix.'orders.customer_last_name) like ?', ['%'.$args['customer'].'%']);
        }
        if (! empty($args['email'])) {
            $query->where('orders.customer_email', 'like', '%'.$args['email'].'%');
        }

        [$from, $to] = $this->resolveAdminDateRange($args);
        if ($from) {
            $query->where('orders.created_at', '>=', $from->startOfDay());
        }
        if ($to) {
            $query->where('orders.created_at', '<=', $to->endOfDay());
        }

        return $query;
    }
}
