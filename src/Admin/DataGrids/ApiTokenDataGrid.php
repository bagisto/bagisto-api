<?php

namespace Webkul\BagistoApi\Admin\DataGrids;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenIssuer;
use Webkul\DataGrid\DataGrid;

class ApiTokenDataGrid extends DataGrid
{
    /**
     * Prepare the query: every admin's tokens with manage_others, otherwise the signed-in admin's own.
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        $now = Carbon::now()->format('Y-m-d H:i:s');

        $status = "CASE
            WHEN {$tablePrefix}t.revoked_at IS NOT NULL THEN '".AdminApiToken::STATUS_REVOKED."'
            WHEN {$tablePrefix}t.expires_at IS NOT NULL AND {$tablePrefix}t.expires_at <= '{$now}' THEN '".AdminApiToken::STATUS_EXPIRED."'
            ELSE '".AdminApiToken::STATUS_ACTIVE."'
        END";

        $actor = auth()->guard('admin')->user();

        $queryBuilder = DB::table('admin_api_tokens as t')
            ->leftJoin('admins as a', 'a.id', '=', 't.admin_id')
            ->leftJoin('admins as c', 'c.id', '=', 't.created_by_admin_id')
            ->when(
                ! AdminAuthHelper::hasPermission($actor, AdminApiTokenIssuer::PERMISSION_MANAGE_OTHERS),
                fn ($query) => $query->where('t.admin_id', $actor->id)
            )
            ->select(
                't.id as id',
                't.name as name',
                't.token_preview as token_preview',
                't.domain as domain',
                't.expires_at as expires_at',
                't.last_used_at as last_used_at',
                't.last_ip_address as last_ip_address',
                't.last_channel as last_channel',
                't.created_at as created_at',
                'a.name as admin_name',
                'c.name as created_by_name',
            )
            ->selectRaw("{$status} as status");

        $this->addFilter('id', 't.id');
        $this->addFilter('name', 't.name');
        $this->addFilter('domain', 't.domain');
        $this->addFilter('expires_at', 't.expires_at');
        $this->addFilter('last_used_at', 't.last_used_at');
        $this->addFilter('last_ip_address', 't.last_ip_address');
        $this->addFilter('last_channel', 't.last_channel');
        $this->addFilter('created_at', 't.created_at');
        $this->addFilter('admin_name', 'a.name');
        $this->addFilter('created_by_name', 'c.name');
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
            'label' => trans('bagistoapi::app.admin.api-tokens.fields.id'),
            'type' => 'integer',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('bagistoapi::app.admin.api-tokens.fields.name'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'admin_name',
            'label' => trans('bagistoapi::app.admin.api-tokens.fields.admin'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'token_preview',
            'label' => trans('bagistoapi::app.admin.api-tokens.fields.token'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            'closure' => fn ($row) => $row->token_preview ? '…'.e($row->token_preview) : '—',
        ]);

        $this->addColumn([
            'index' => 'domain',
            'label' => trans('bagistoapi::app.admin.api-tokens.fields.domain'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'expires_at',
            'label' => trans('bagistoapi::app.admin.api-tokens.fields.expires-at'),
            'type' => 'datetime',
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
            'closure' => fn ($row) => $row->expires_at ?? trans('bagistoapi::app.admin.api-tokens.datagrid.never'),
        ]);

        $this->addColumn([
            'index' => 'last_used_at',
            'label' => trans('bagistoapi::app.admin.api-tokens.fields.last-used-at'),
            'type' => 'datetime',
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'last_ip_address',
            'label' => trans('bagistoapi::app.admin.api-tokens.fields.last-ip-address'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'last_channel',
            'label' => trans('bagistoapi::app.admin.api-tokens.fields.last-channel'),
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',

            'filterable_options' => [
                [
                    'label' => trans('bagistoapi::app.admin.api-tokens.channels.rest'),
                    'value' => 'rest',
                ], [
                    'label' => trans('bagistoapi::app.admin.api-tokens.channels.graphql'),
                    'value' => 'graphql',
                ],
            ],

            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('bagistoapi::app.admin.api-tokens.fields.status'),
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',

            'filterable_options' => array_map(fn (string $status) => [
                'label' => trans('bagistoapi::app.admin.api-tokens.status.'.$status),
                'value' => $status,
            ], AdminApiToken::STATUSES),

            'sortable' => true,
            'closure' => fn ($row) => trans('bagistoapi::app.admin.api-tokens.status.'.$row->status),
        ]);

        $this->addColumn([
            'index' => 'created_by_name',
            'label' => trans('bagistoapi::app.admin.api-tokens.fields.created-by'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => fn ($row) => $row->created_by_name ? e($row->created_by_name) : trans('bagistoapi::app.admin.api-tokens.datagrid.console'),
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('bagistoapi::app.admin.api-tokens.fields.created-at'),
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
        $actor = auth()->guard('admin')->user();

        if (AdminAuthHelper::hasPermission($actor, 'settings.api_tokens')) {
            $this->addAction([
                'index' => 'view',
                'icon' => 'icon-view',
                'title' => trans('bagistoapi::app.admin.api-tokens.datagrid.view'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.settings.api_tokens.view', $row->id),
            ]);
        }

        if (AdminAuthHelper::hasPermission($actor, 'settings.api_tokens.delete')) {
            $this->addAction([
                'index' => 'revoke',
                'icon' => 'icon-delete',
                'title' => trans('bagistoapi::app.admin.api-tokens.datagrid.revoke'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.settings.api_tokens.delete', $row->id),
            ]);
        }
    }
}
