<?php

namespace Webkul\BagistoApi\Admin\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class AuditHistoryDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        return DB::table('admin_api_audits')
            ->select(
                'id',
                'event',
                'auditable_type',
                'auditable_id',
                'created_at'
            );
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('bagistoapi::app.integration.history.datagrid.id'),
            'type' => 'integer',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'event',
            'label' => trans('bagistoapi::app.integration.history.datagrid.operation'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',

            'filterable_options' => [
                [
                    'label' => trans('bagistoapi::app.integration.history.events.created'),
                    'value' => 'created',
                ], [
                    'label' => trans('bagistoapi::app.integration.history.events.updated'),
                    'value' => 'updated',
                ], [
                    'label' => trans('bagistoapi::app.integration.history.events.deleted'),
                    'value' => 'deleted',
                ],
            ],

            'sortable' => true,
            'closure' => fn ($row) => '<span class="label-'.($row->event === 'deleted' ? 'canceled' : ($row->event === 'created' ? 'active' : 'pending')).'">'.ucfirst((string) $row->event).'</span>',
        ]);

        $this->addColumn([
            'index' => 'auditable_type',
            'label' => trans('bagistoapi::app.integration.history.datagrid.resource'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $base = $row->auditable_type ? class_basename($row->auditable_type) : '—';

                return $row->auditable_id ? $base.' #'.$row->auditable_id : $base;
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('bagistoapi::app.integration.history.datagrid.date'),
            'type' => 'datetime',
            'searchable' => false,
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
        $this->addAction([
            'icon' => 'icon-view',
            'title' => trans('bagistoapi::app.integration.history.datagrid.view'),
            'method' => 'GET',
            'url' => fn ($row) => route('admin.integration.history.view', $row->id),
        ]);
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('integration.history.delete')) {
            $this->addMassAction([
                'title' => trans('bagistoapi::app.integration.history.datagrid.delete'),
                'method' => 'POST',
                'url' => route('admin.integration.history.mass_delete'),
            ]);
        }
    }
}
