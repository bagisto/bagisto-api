<?php

namespace Webkul\BagistoApi\Admin\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class IntegrationDataGrid extends DataGrid
{
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        return DB::table('admin_personal_access_tokens as t')
            ->leftJoin('admins as a', 'a.id', '=', 't.admin_id')
            ->whereIn('t.status', ['draft', 'active'])
            ->select(
                't.id as id',
                't.name as name',
                't.description as description',
                't.token_preview as token_preview',
                't.permission_type as permission_type',
                't.status as status',
                't.expires_at as expires_at',
                't.last_used_at as last_used_at',
                't.created_at as created_at',
                't.updated_at as updated_at',
                'a.name as admin_name',
                'a.email as admin_email'
            )
            ->selectRaw("CASE
                WHEN {$tablePrefix}t.token_preview IS NULL THEN '—'
                ELSE CONCAT({$tablePrefix}t.id, '|', {$tablePrefix}t.token_preview, '...xxxx')
            END as masked_token");
    }

    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('bagistoapi::integration.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('bagistoapi::integration.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'admin_name',
            'label'      => trans('bagistoapi::integration.datagrid.admin'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'masked_token',
            'label'      => trans('bagistoapi::integration.datagrid.token'),
            'type'       => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => false,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('bagistoapi::integration.datagrid.status'),
            'type'               => 'string',
            'searchable'         => false,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                ['label' => trans('bagistoapi::integration.status.draft'), 'value' => 'draft'],
                ['label' => trans('bagistoapi::integration.status.active'), 'value' => 'active'],
            ],
            'sortable'           => true,
        ]);

        $this->addColumn([
            'index'              => 'permission_type',
            'label'              => trans('bagistoapi::integration.datagrid.permission-type'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                ['label' => trans('bagistoapi::integration.permission_type.all'), 'value' => 'all'],
                ['label' => trans('bagistoapi::integration.permission_type.custom'), 'value' => 'custom'],
                ['label' => trans('bagistoapi::integration.permission_type.same_as_web'), 'value' => 'same_as_web'],
            ],
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'           => 'expires_at',
            'label'           => trans('bagistoapi::integration.datagrid.expires-at'),
            'type'            => 'datetime',
            'searchable'      => false,
            'filterable'      => true,
            'filterable_type' => 'datetime_range',
            'sortable'        => true,
        ]);

        $this->addColumn([
            'index'           => 'last_used_at',
            'label'           => trans('bagistoapi::integration.datagrid.last-used-at'),
            'type'            => 'datetime',
            'searchable'      => false,
            'filterable'      => true,
            'filterable_type' => 'datetime_range',
            'sortable'        => true,
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('bagistoapi::integration.datagrid.created-at'),
            'type'            => 'datetime',
            'searchable'      => false,
            'filterable'      => true,
            'filterable_type' => 'datetime_range',
            'sortable'        => true,
        ]);
    }

    public function prepareActions()
    {
        if (bouncer()->hasPermission('integration.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('bagistoapi::integration.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.integration.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('integration.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('bagistoapi::integration.datagrid.revoke'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.integration.destroy', $row->id);
                },
            ]);
        }
    }
}
