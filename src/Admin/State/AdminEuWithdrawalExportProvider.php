<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminExport;

/**
 * Streams the EU withdrawals datagrid export, mirroring the core grid's columns.
 */
class AdminEuWithdrawalExportProvider implements ProviderInterface
{
    use StreamsAdminExport;

    protected function exportPermission(): string
    {
        return 'sales.eu_withdrawals';
    }

    protected function exportNoPermissionLangKey(): string
    {
        return 'bagistoapi::app.admin.eu-withdrawal.no-permission';
    }

    protected function exportFilename(): string
    {
        return 'eu-withdrawals';
    }

    protected function exportHeaders(): array
    {
        return [
            trans('admin::app.eu_withdrawal.datagrid.received_at'),
            trans('admin::app.eu_withdrawal.datagrid.order'),
            trans('admin::app.eu_withdrawal.datagrid.customer_email'),
            trans('admin::app.eu_withdrawal.datagrid.status'),
            trans('admin::app.eu_withdrawal.datagrid.channel'),
            trans('admin::app.eu_withdrawal.datagrid.confirmation_sent_at'),
            trans('admin::app.eu_withdrawal.datagrid.uuid'),
        ];
    }

    protected function exportRow(object $row): array
    {
        return [
            $row->received_at,
            $row->order_increment_id,
            $row->customer_email,
            $row->status,
            $row->channel_code,
            $row->confirmation_sent_at,
            $row->uuid,
        ];
    }

    protected function exportQuery(array $args)
    {
        $query = DB::table('eu_withdrawals as w')
            ->leftJoin('orders as o', 'w.order_id', '=', 'o.id')
            ->leftJoin('channels as c', 'w.channel_id', '=', 'c.id')
            ->select(
                'w.id',
                'w.uuid',
                'w.customer_email',
                'w.status',
                'w.received_at',
                'w.confirmation_sent_at',
                'o.increment_id as order_increment_id',
                'c.code as channel_code',
            )
            ->orderByDesc('w.id');

        if (! empty($args['order_increment_id'])) {
            $query->where('o.increment_id', 'like', '%'.$args['order_increment_id'].'%');
        }

        if (! empty($args['customer_email'])) {
            $query->where('w.customer_email', 'like', '%'.$args['customer_email'].'%');
        }

        if (! empty($args['status'])) {
            $query->where('w.status', $args['status']);
        }

        if (! empty($args['channel_code'])) {
            $query->where('c.code', $args['channel_code']);
        }

        if (! empty($args['received_at_from'])) {
            $query->whereDate('w.received_at', '>=', $args['received_at_from']);
        }

        if (! empty($args['received_at_to'])) {
            $query->whereDate('w.received_at', '<=', $args['received_at_to']);
        }

        if (! empty($args['confirmation_sent_at_from'])) {
            $query->whereDate('w.confirmation_sent_at', '>=', $args['confirmation_sent_at_from']);
        }

        if (! empty($args['confirmation_sent_at_to'])) {
            $query->whereDate('w.confirmation_sent_at', '<=', $args['confirmation_sent_at_to']);
        }

        return $query;
    }
}
