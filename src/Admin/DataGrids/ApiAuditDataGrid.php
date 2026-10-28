<?php

namespace Webkul\BagistoApi\Admin\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\DataGrid\DataGrid;

class ApiAuditDataGrid extends DataGrid
{
    /**
     * The audit events a row can record.
     */
    public const EVENTS = ['created', 'updated', 'deleted'];

    /**
     * Prepare the query over every audit row.
     */
    public function prepareQueryBuilder()
    {
        return DB::table('admin_api_audits')
            ->select(
                'id',
                'event',
                'auditable_type',
                'auditable_id',
                'admin_name',
                'token_kind',
                'token_id',
                'token_name',
                'channel',
                'method',
                'ip_address',
                'created_at',
            );
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('bagistoapi::app.admin.api-audit.datagrid.id'),
            'type' => 'integer',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'event',
            'label' => trans('bagistoapi::app.admin.api-audit.datagrid.operation'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',

            'filterable_options' => array_map(fn (string $event) => [
                'label' => trans('bagistoapi::app.admin.api-audit.events.'.$event),
                'value' => $event,
            ], self::EVENTS),

            'sortable' => true,
            'closure' => fn ($row) => $this->eventLabel((string) $row->event),
        ]);

        $this->addColumn([
            'index' => 'auditable_type',
            'label' => trans('bagistoapi::app.admin.api-audit.datagrid.resource'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => fn ($row) => e($this->resourceLabel($row->auditable_type, $row->auditable_id)),
        ]);

        $this->addColumn([
            'index' => 'admin_name',
            'label' => trans('bagistoapi::app.admin.api-audit.datagrid.admin'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'token_kind',
            'label' => trans('bagistoapi::app.admin.api-audit.datagrid.token-kind'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',

            'filterable_options' => array_map(fn (string $tokenKind) => [
                'label' => trans('bagistoapi::app.admin.api-audit.token-kinds.'.$this->langSegment($tokenKind)),
                'value' => $tokenKind,
            ], AdminApiAudit::TOKEN_KINDS),

            'sortable' => true,
            'closure' => fn ($row) => $this->optionLabel('token-kinds', $row->token_kind),
        ]);

        $this->addColumn([
            'index' => 'token_name',
            'label' => trans('bagistoapi::app.admin.api-audit.datagrid.token'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => fn ($row) => e($this->tokenLabel($row->token_name, $row->token_id)),
        ]);

        $this->addColumn([
            'index' => 'channel',
            'label' => trans('bagistoapi::app.admin.api-audit.datagrid.channel'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',

            'filterable_options' => array_map(fn (string $channel) => [
                'label' => trans('bagistoapi::app.admin.api-audit.channels.'.$channel),
                'value' => $channel,
            ], AdminApiAudit::CHANNELS),

            'sortable' => true,
            'closure' => fn ($row) => $this->optionLabel('channels', $row->channel),
        ]);

        $this->addColumn([
            'index' => 'method',
            'label' => trans('bagistoapi::app.admin.api-audit.datagrid.method'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'ip_address',
            'label' => trans('bagistoapi::app.admin.api-audit.datagrid.ip'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('bagistoapi::app.admin.api-audit.datagrid.date'),
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
            'title' => trans('bagistoapi::app.admin.api-audit.datagrid.view'),
            'method' => 'GET',
            'url' => fn ($row) => route('admin.settings.api_audit.view', $row->id),
        ]);
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions()
    {
        if (AdminAuthHelper::hasPermission(auth()->guard('admin')->user(), 'settings.api_audit.delete')) {
            $this->addMassAction([
                'title' => trans('bagistoapi::app.admin.api-audit.datagrid.delete'),
                'method' => 'POST',
                'url' => route('admin.settings.api_audit.mass_delete'),
            ]);
        }
    }

    /**
     * The coloured label of an audit event.
     */
    protected function eventLabel(string $event): string
    {
        $class = match ($event) {
            'created' => 'label-active',
            'deleted' => 'label-canceled',
            default => 'label-pending',
        };

        $label = in_array($event, self::EVENTS, true)
            ? trans('bagistoapi::app.admin.api-audit.events.'.$event)
            : $event;

        return '<span class="'.$class.'">'.e($label).'</span>';
    }

    /**
     * The short class name and id of the audited record.
     */
    protected function resourceLabel(?string $type, mixed $id): string
    {
        $label = $type ? class_basename($type) : '—';

        return $id ? $label.' #'.$id : $label;
    }

    /**
     * The name and id of the session or token a change was made with.
     */
    protected function tokenLabel(?string $name, mixed $id): string
    {
        if (! $id) {
            return $name ?? '—';
        }

        return ($name ?? '—').' (#'.$id.')';
    }

    /**
     * The translated label of a token kind or channel, or a dash when the row has none.
     */
    protected function optionLabel(string $group, ?string $value): string
    {
        if ($value === null) {
            return '—';
        }

        $key = 'bagistoapi::app.admin.api-audit.'.$group.'.'.$this->langSegment($value);

        return e(trans()->has($key) ? trans($key) : $value);
    }

    /**
     * The kebab-case translation key segment of a stored value.
     */
    protected function langSegment(string $value): string
    {
        return str_replace('_', '-', $value);
    }
}
