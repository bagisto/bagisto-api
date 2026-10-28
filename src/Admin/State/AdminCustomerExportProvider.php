<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminExport;

/**
 * Streams the customers datagrid export, mirroring the core grid's exportable columns.
 */
class AdminCustomerExportProvider implements ProviderInterface
{
    use StreamsAdminExport;

    protected function exportPermission(): string
    {
        return 'customers.customers';
    }

    protected function exportNoPermissionLangKey(): string
    {
        return 'bagistoapi::app.admin.customer.no-permission';
    }

    protected function exportFilename(): string
    {
        return 'customers';
    }

    protected function exportHeaders(): array
    {
        return [
            trans('admin::app.customers.customers.index.datagrid.channel'),
            trans('admin::app.customers.customers.index.datagrid.id'),
            trans('admin::app.customers.customers.index.datagrid.name'),
            trans('admin::app.customers.customers.index.datagrid.email'),
            trans('admin::app.customers.customers.index.datagrid.phone'),
            trans('admin::app.customers.customers.index.datagrid.status'),
            trans('admin::app.customers.customers.index.datagrid.gender'),
            trans('admin::app.customers.customers.index.datagrid.group'),
            trans('admin::app.customers.customers.index.datagrid.suspended'),
            trans('admin::app.customers.customers.index.datagrid.order-count'),
            trans('admin::app.customers.customers.index.datagrid.address-count'),
        ];
    }

    protected function exportRow(object $row): array
    {
        return [
            $row->channel_id,
            $row->customer_id,
            $row->full_name,
            $row->email,
            $row->phone,
            $row->status,
            $row->gender,
            $row->group,
            $row->is_suspended,
            $row->order_count,
            $row->address_count,
        ];
    }

    protected function exportQuery(array $args)
    {
        $prefix = DB::getTablePrefix();

        $query = DB::table('customers')
            ->leftJoin('addresses', function ($join) {
                $join->on('customers.id', '=', 'addresses.customer_id')
                    ->where('addresses.address_type', '=', 'customer');
            })
            ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
            ->addSelect(
                'customers.id as customer_id',
                'customers.email',
                'customers.phone',
                'customers.gender',
                'customers.status',
                'customers.is_suspended',
                'customer_groups.name as group',
                'customers.channel_id',
            )
            ->addSelect(DB::raw('COUNT(DISTINCT '.$prefix.'addresses.id) as address_count'))
            ->addSelect(DB::raw('COUNT(DISTINCT '.$prefix.'orders.id) as order_count'))
            ->addSelect(DB::raw('CONCAT('.$prefix.'customers.first_name, " ", '.$prefix.'customers.last_name) as full_name'))
            ->groupBy('customers.id')
            ->orderByDesc('customers.id');

        if (isset($args['query']) && $args['query'] !== '') {
            $search = urldecode((string) $args['query']);

            $query->where(function ($q) use ($search, $prefix) {
                $q->where('customers.email', 'like', '%'.$search.'%')
                    ->orWhere(DB::raw('CONCAT('.$prefix."customers.first_name, ' ', ".$prefix.'customers.last_name)'), 'like', '%'.$search.'%');
            });
        }

        if (! empty($args['name'])) {
            $name = $args['name'];

            $query->where(function ($q) use ($name, $prefix) {
                $q->where('customers.first_name', 'like', '%'.$name.'%')
                    ->orWhere('customers.last_name', 'like', '%'.$name.'%')
                    ->orWhere(DB::raw('CONCAT('.$prefix."customers.first_name, ' ', ".$prefix.'customers.last_name)'), 'like', '%'.$name.'%');
            });
        }

        if (! empty($args['email'])) {
            $query->where('customers.email', 'like', '%'.$args['email'].'%');
        }

        if (! empty($args['phone'])) {
            $query->where('customers.phone', 'like', '%'.$args['phone'].'%');
        }

        if (isset($args['customer_group_id']) && $args['customer_group_id'] !== '') {
            $query->where('customers.customer_group_id', (int) $args['customer_group_id']);
        }

        if (isset($args['status']) && $args['status'] !== '') {
            $query->where('customers.status', (int) $args['status']);
        }

        if (isset($args['channel_id']) && $args['channel_id'] !== '') {
            $query->where('customers.channel_id', (int) $args['channel_id']);
        }

        if (! empty($args['date_of_birth_from'])) {
            $query->where('customers.date_of_birth', '>=', $args['date_of_birth_from']);
        }

        if (! empty($args['date_of_birth_to'])) {
            $query->where('customers.date_of_birth', '<=', $args['date_of_birth_to']);
        }

        if (! empty($args['created_at_from'])) {
            $query->where('customers.created_at', '>=', $args['created_at_from']);
        }

        if (! empty($args['created_at_to'])) {
            $query->where('customers.created_at', '<=', $args['created_at_to']);
        }

        return $query;
    }
}
