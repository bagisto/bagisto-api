<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminExport;

/**
 * Streams the GDPR requests datagrid export, mirroring the core grid's columns.
 */
class AdminCustomerGdprExportProvider implements ProviderInterface
{
    use StreamsAdminExport;

    protected function exportPermission(): string
    {
        return 'customers.gdpr_requests';
    }

    protected function exportNoPermissionLangKey(): string
    {
        return 'bagistoapi::app.admin.customer.gdpr.no-permission';
    }

    protected function exportFilename(): string
    {
        return 'gdpr-requests';
    }

    protected function exportHeaders(): array
    {
        return [
            trans('admin::app.customers.gdpr.index.datagrid.id'),
            trans('admin::app.customers.gdpr.index.datagrid.customer-name'),
            trans('admin::app.customers.gdpr.index.datagrid.status'),
            trans('admin::app.customers.gdpr.index.datagrid.type'),
            trans('admin::app.customers.gdpr.index.datagrid.message'),
            trans('admin::app.customers.gdpr.index.datagrid.created-at'),
        ];
    }

    protected function exportRow(object $row): array
    {
        return [
            $row->id,
            trim((string) ($row->customer_name ?? '')),
            $row->status,
            $row->type,
            $row->message,
            $row->created_at,
        ];
    }

    protected function exportQuery(array $args)
    {
        $prefix = DB::getTablePrefix();

        $query = DB::table('gdpr_data_request as gdpr')
            ->leftJoin('customers', 'gdpr.customer_id', '=', 'customers.id')
            ->select(
                'gdpr.id',
                DB::raw("CONCAT(COALESCE({$prefix}customers.first_name, ''), ' ', COALESCE({$prefix}customers.last_name, '')) as customer_name"),
                'gdpr.status',
                'gdpr.type',
                'gdpr.message',
                'gdpr.created_at',
            )
            ->orderByDesc('gdpr.id');

        if (! empty($args['status'])) {
            $query->where('gdpr.status', (string) $args['status']);
        }

        if (! empty($args['type'])) {
            $query->where('gdpr.type', (string) $args['type']);
        }

        if (isset($args['customer_id']) && $args['customer_id'] !== '') {
            $query->where('gdpr.customer_id', (int) $args['customer_id']);
        }

        if (! empty($args['email'])) {
            $query->where('gdpr.email', 'like', '%'.$args['email'].'%');
        }

        if (! empty($args['customer_name'])) {
            $query->where(DB::raw("CONCAT({$prefix}customers.first_name, ' ', {$prefix}customers.last_name)"), 'like', '%'.$args['customer_name'].'%');
        }

        if (! empty($args['created_at_from'])) {
            $query->where('gdpr.created_at', '>=', $args['created_at_from']);
        }

        if (! empty($args['created_at_to'])) {
            $query->where('gdpr.created_at', '<=', $args['created_at_to']);
        }

        return $query;
    }
}
