<?php

namespace Webkul\BagistoApi\Admin\DataGrids;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\DataGrid\DataGrid;

class CustomerApiSessionDataGrid extends DataGrid
{
    /**
     * Prepare the query over every customer's storefront sessions.
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        $now = Carbon::now()->format('Y-m-d H:i:s');

        $status = "CASE
            WHEN {$tablePrefix}s.revoked_at IS NOT NULL THEN '".ApiSessionDataGrid::STATUS_REVOKED."'
            WHEN {$tablePrefix}s.expires_at <= '{$now}' THEN '".ApiSessionDataGrid::STATUS_EXPIRED."'
            WHEN {$tablePrefix}s.refresh_expires_at <= '{$now}' THEN '".ApiSessionDataGrid::STATUS_EXPIRED."'
            ELSE '".ApiSessionDataGrid::STATUS_ACTIVE."'
        END";

        $customerName = db_grammar()->concat($tablePrefix.'c.first_name', "' '", $tablePrefix.'c.last_name');

        $queryBuilder = DB::table('customer_api_sessions as s')
            ->leftJoin('customers as c', 'c.id', '=', 's.customer_id')
            ->leftJoin('channels as ch', 'ch.id', '=', 's.channel_id')
            ->select(
                's.id as id',
                's.customer_id as customer_id',
                's.name as name',
                's.client_type as client_type',
                's.ip_address as ip_address',
                's.user_agent as user_agent',
                's.last_ip_address as last_ip_address',
                's.last_user_agent as last_user_agent',
                's.last_used_at as last_used_at',
                's.last_channel as last_channel',
                's.refresh_expires_at as refresh_expires_at',
                's.expires_at as expires_at',
                's.created_at as created_at',
                'c.email as customer_email',
                'ch.code as channel_code',
            )
            ->selectRaw("{$customerName} as customer_name")
            ->selectRaw("{$status} as status");

        $this->addFilter('id', 's.id');
        $this->addFilter('customer_name', DB::raw($customerName));
        $this->addFilter('customer_email', 'c.email');
        $this->addFilter('name', 's.name');
        $this->addFilter('client_type', 's.client_type');
        $this->addFilter('channel_code', 'ch.code');
        $this->addFilter('ip_address', 's.ip_address');
        $this->addFilter('last_ip_address', 's.last_ip_address');
        $this->addFilter('last_used_at', 's.last_used_at');
        $this->addFilter('last_channel', 's.last_channel');
        $this->addFilter('refresh_expires_at', 's.refresh_expires_at');
        $this->addFilter('expires_at', 's.expires_at');
        $this->addFilter('created_at', 's.created_at');
        $this->addFilter('status', DB::raw($status));

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.id'),
            'type' => 'integer',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'customer_name',
            'label' => trans('bagistoapi::app.admin.customer-api-sessions.fields.customer'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => fn ($row) => '<a class="text-blue-600 hover:underline" href="'.e(route('admin.customers.customers.view', $row->customer_id)).'">'.e($row->customer_name).'</a>',
        ]);

        $this->addColumn([
            'index' => 'customer_email',
            'label' => trans('bagistoapi::app.admin.customer-api-sessions.fields.email'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.device'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'client_type',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.client-type'),
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',

            'filterable_options' => array_map(fn (string $clientType) => [
                'label' => trans('bagistoapi::app.admin.api-sessions.client-types.'.$clientType),
                'value' => $clientType,
            ], CustomerApiSession::CLIENT_TYPES),

            'sortable' => true,
            'closure' => fn ($row) => trans('bagistoapi::app.admin.api-sessions.client-types.'.$row->client_type),
        ]);

        $this->addColumn([
            'index' => 'channel_code',
            'label' => trans('bagistoapi::app.admin.customer-api-sessions.fields.channel'),
            'type' => 'string',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'ip_address',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.ip-address'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'last_ip_address',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.last-ip-address'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'last_user_agent',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.last-user-agent'),
            'type' => 'string',
            'filterable' => false,
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'last_used_at',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.last-used-at'),
            'type' => 'datetime',
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'last_channel',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.last-channel'),
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',

            'filterable_options' => [
                [
                    'label' => trans('bagistoapi::app.admin.api-tokens.channels.rest'),
                    'value' => CustomerApiSession::CHANNEL_REST,
                ], [
                    'label' => trans('bagistoapi::app.admin.api-tokens.channels.graphql'),
                    'value' => CustomerApiSession::CHANNEL_GRAPHQL,
                ],
            ],

            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'refresh_expires_at',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.refresh-expires-at'),
            'type' => 'datetime',
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'expires_at',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.expires-at'),
            'type' => 'datetime',
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.status'),
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',

            'filterable_options' => array_map(fn (string $status) => [
                'label' => trans('bagistoapi::app.admin.api-tokens.status.'.$status),
                'value' => $status,
            ], [ApiSessionDataGrid::STATUS_ACTIVE, ApiSessionDataGrid::STATUS_EXPIRED, ApiSessionDataGrid::STATUS_REVOKED]),

            'sortable' => true,
            'closure' => fn ($row) => trans('bagistoapi::app.admin.api-tokens.status.'.$row->status),
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.created-at'),
            'type' => 'datetime',
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions()
    {
        if (AdminAuthHelper::hasPermission(auth()->guard('admin')->user(), 'customers.api_sessions.delete')) {
            $this->addAction([
                'index' => 'revoke',
                'icon' => 'icon-delete',
                'title' => trans('bagistoapi::app.admin.api-sessions.datagrid.revoke'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.customers.api_sessions.delete', $row->id),
            ]);

            $this->addAction([
                'index' => 'revoke-all',
                'icon' => 'icon-exit',
                'title' => trans('bagistoapi::app.admin.customer-api-sessions.datagrid.revoke-all'),
                'method' => 'POST',
                'url' => fn ($row) => route('admin.customers.api_sessions.revoke_all', $row->customer_id),
            ]);
        }
    }
}
