<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminExport;

class AdminInvoiceExportProvider implements ProviderInterface
{
    use StreamsAdminExport;

    protected function exportPermission(): string
    {
        return 'sales.invoices.view';
    }

    protected function exportFilename(): string
    {
        return 'invoices';
    }

    protected function exportHeaders(): array
    {
        return ['ID', 'Order ID', 'Status', 'Grand Total', 'Invoice Date'];
    }

    protected function exportRow(object $row): array
    {
        return [
            $row->increment_id ?? $row->id,
            $row->order_increment_id,
            $row->state,
            $this->safeFormatBasePrice($row->base_grand_total),
            $row->created_at,
        ];
    }

    protected function exportQuery(array $args)
    {
        $query = DB::table('invoices')
            ->leftJoin('orders', 'invoices.order_id', '=', 'orders.id')
            ->select(
                'invoices.id',
                'invoices.increment_id',
                'invoices.state',
                'invoices.base_grand_total',
                'invoices.created_at',
                'orders.increment_id as order_increment_id',
            )
            ->orderByDesc('invoices.id');

        if (! empty($args['id'])) {
            $ids = is_array($args['id']) ? $args['id'] : array_filter(array_map('trim', explode(',', (string) $args['id'])));
            if (! empty($ids)) {
                $query->whereIn('invoices.id', $ids);
            }
        }
        if (! empty($args['order_id'])) {
            $query->where('orders.increment_id', 'like', '%'.$args['order_id'].'%');
        }
        if (! empty($args['state'])) {
            $query->where('invoices.state', $args['state']);
        }
        if (isset($args['base_grand_total_from']) && $args['base_grand_total_from'] !== '') {
            $query->where('invoices.base_grand_total', '>=', (float) $args['base_grand_total_from']);
        }
        if (isset($args['base_grand_total_to']) && $args['base_grand_total_to'] !== '') {
            $query->where('invoices.base_grand_total', '<=', (float) $args['base_grand_total_to']);
        }
        $from = $args['created_at_from'] ?? $args['date_from'] ?? null;
        $to = $args['created_at_to'] ?? $args['date_to'] ?? null;
        if ($from) {
            $query->where('invoices.created_at', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            $query->where('invoices.created_at', '<=', Carbon::parse($to)->endOfDay());
        }

        return $query;
    }
}
