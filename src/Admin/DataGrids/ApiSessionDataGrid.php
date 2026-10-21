<?php

namespace Webkul\BagistoApi\Admin\DataGrids;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\DataGrid\DataGrid;

class ApiSessionDataGrid extends DataGrid
{
    /**
     * The status of a session that can be used.
     */
    public const STATUS_ACTIVE = 'active';

    /**
     * The status of a session past its absolute lifetime.
     */
    public const STATUS_EXPIRED = 'expired';

    /**
     * The status of a revoked session.
     */
    public const STATUS_REVOKED = 'revoked';

    /**
     * Prepare the query over every admin's sessions.
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        $now = Carbon::now()->format('Y-m-d H:i:s');

        $status = "CASE
            WHEN {$tablePrefix}s.revoked_at IS NOT NULL THEN '".self::STATUS_REVOKED."'
            WHEN {$tablePrefix}s.expires_at <= '{$now}' THEN '".self::STATUS_EXPIRED."'
            ELSE '".self::STATUS_ACTIVE."'
        END";

        $queryBuilder = DB::table('admin_api_sessions as s')
            ->leftJoin('admins as a', 'a.id', '=', 's.admin_id')
            ->select(
                's.id as id',
                's.admin_id as admin_id',
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
                'a.name as admin_name',
            )
            ->selectRaw("{$status} as status");

        $this->addFilter('id', 's.id');
        $this->addFilter('name', 's.name');
        $this->addFilter('client_type', 's.client_type');
        $this->addFilter('ip_address', 's.ip_address');
        $this->addFilter('last_ip_address', 's.last_ip_address');
        $this->addFilter('last_used_at', 's.last_used_at');
        $this->addFilter('last_channel', 's.last_channel');
        $this->addFilter('refresh_expires_at', 's.refresh_expires_at');
        $this->addFilter('expires_at', 's.expires_at');
        $this->addFilter('created_at', 's.created_at');
        $this->addFilter('admin_name', 'a.name');
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
            'index' => 'admin_name',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.admin'),
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
            ], AdminApiSession::CLIENT_TYPES),

            'sortable' => true,
            'closure' => fn ($row) => trans('bagistoapi::app.admin.api-sessions.client-types.'.$row->client_type),
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
            'index' => 'user_agent',
            'label' => trans('bagistoapi::app.admin.api-sessions.fields.user-agent'),
            'type' => 'string',
            'filterable' => false,
            'sortable' => false,
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
                    'value' => AdminApiSession::CHANNEL_REST,
                ], [
                    'label' => trans('bagistoapi::app.admin.api-tokens.channels.graphql'),
                    'value' => AdminApiSession::CHANNEL_GRAPHQL,
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
            ], [self::STATUS_ACTIVE, self::STATUS_EXPIRED, self::STATUS_REVOKED]),

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
        if (AdminAuthHelper::hasPermission(auth()->guard('admin')->user(), 'settings.api_sessions.delete')) {
            $this->addAction([
                'index' => 'revoke',
                'icon' => 'icon-delete',
                'title' => trans('bagistoapi::app.admin.api-sessions.datagrid.revoke'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.settings.api_sessions.delete', $row->id),
            ]);

            $this->addAction([
                'index' => 'revoke-all',
                'icon' => 'icon-exit',
                'title' => trans('bagistoapi::app.admin.api-sessions.datagrid.revoke-all'),
                'method' => 'POST',
                'url' => fn ($row) => route('admin.settings.api_sessions.revoke_all', $row->admin_id),
            ]);
        }
    }
}
