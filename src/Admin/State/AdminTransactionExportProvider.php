<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminCsvExport;

class AdminTransactionExportProvider implements ProviderInterface
{
    use StreamsAdminCsvExport;

    protected function exportPermission(): string
    {
        return 'sales.transactions.view';
    }

    protected function exportFilename(): string
    {
        return 'transactions.csv';
    }

    protected function exportHeaders(): array
    {
        return ['ID', 'Transaction ID', 'Invoice ID', 'Order ID', 'Status', 'Date'];
    }

    protected function exportRow(object $row): array
    {
        return [
            $row->id,
            $row->transaction_id,
            $row->invoice_id,
            $row->order_increment_id,
            $row->status,
            $row->created_at,
        ];
    }

    protected function exportQuery(array $args)
    {
        $query = DB::table('order_transactions')
            ->leftJoin('orders', 'order_transactions.order_id', '=', 'orders.id')
            ->select(
                'order_transactions.id',
                'order_transactions.transaction_id',
                'order_transactions.invoice_id',
                'order_transactions.status',
                'order_transactions.created_at',
                'orders.increment_id as order_increment_id',
            )
            ->orderByDesc('order_transactions.id');

        if (! empty($args['id'])) {
            $ids = is_array($args['id']) ? $args['id'] : array_filter(array_map('trim', explode(',', (string) $args['id'])));
            if (! empty($ids)) {
                $query->whereIn('order_transactions.id', $ids);
            }
        }

        if (! empty($args['transaction_id'])) {
            $query->where('order_transactions.transaction_id', 'like', '%'.$args['transaction_id'].'%');
        }

        if (! empty($args['invoice_id'])) {
            $query->where('order_transactions.invoice_id', $args['invoice_id']);
        }

        if (! empty($args['order_id'])) {
            $query->where('orders.increment_id', 'like', '%'.$args['order_id'].'%');
        }

        if (! empty($args['status'])) {
            $query->where('order_transactions.status', $args['status']);
        }

        $from = $args['created_at_from'] ?? $args['date_from'] ?? null;
        $to = $args['created_at_to'] ?? $args['date_to'] ?? null;
        if ($from) {
            $query->where('order_transactions.created_at', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            $query->where('order_transactions.created_at', '<=', Carbon::parse($to)->endOfDay());
        }

        return $query;
    }
}
