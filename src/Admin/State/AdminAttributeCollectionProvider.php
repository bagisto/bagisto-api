<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminAttribute;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for the admin Catalog → Attributes datagrid endpoint.
 *
 * REST: GET /api/admin/catalog/attributes
 *
 * Mirrors Webkul\Admin\DataGrids\Catalog\AttributeDataGrid 1:1 — same columns,
 * same filterable columns, same sort columns.
 *
 * The datagrid queries the `attributes` table directly (no join). We add an
 * optional LEFT JOIN to `attribute_translations` so the `adminName` field can
 * surface the locale-aware translated name when a translation exists for the
 * requested locale; falls back to `attributes.admin_name` otherwise.
 */
class AdminAttributeCollectionProvider extends AbstractAdminCollectionProvider
{
    /**
     * Boolean-ish columns that only accept 0 or 1.
     */
    protected const BOOLEAN_FILTERS = [
        'is_required',
        'is_unique',
        'is_filterable',
        'is_configurable',
        'is_visible_on_front',
        'is_user_defined',
        'value_per_locale',
        'value_per_channel',
    ];

    /** Locale resolved during buildQuery; used by mapRow. */
    private string $locale = '';

    protected function getSortable(): array
    {
        return ['id', 'code', 'admin_name', 'type', 'position'];
    }

    protected function buildQuery(array $args)
    {
        $this->locale = $args['locale'] ?? app()->getLocale();

        return DB::table('attributes')
            ->leftJoin('attribute_translations as at', function ($j) {
                $j->on('attributes.id', '=', 'at.attribute_id')
                    ->where('at.locale', $this->locale);
            })
            ->select(
                'attributes.id',
                'attributes.code',
                'attributes.type',
                'attributes.admin_name',
                'attributes.is_required',
                'attributes.is_unique',
                'attributes.value_per_locale',
                'attributes.value_per_channel',
                'attributes.is_filterable',
                'attributes.is_configurable',
                'attributes.is_visible_on_front',
                'attributes.is_user_defined',
                'attributes.swatch_type',
                'attributes.position',
                'attributes.created_at',
                'attributes.updated_at',
                'at.name as translated_name',
                'at.locale as at_locale',
            )
            ->groupBy('attributes.id');
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['id'])) {
            $ids = is_array($args['id'])
                ? $args['id']
                : array_filter(array_map('trim', explode(',', (string) $args['id'])));
            $ids = array_values(array_filter(array_map('intval', $ids)));
            if ($ids) {
                $query->whereIn('attributes.id', $ids);
            }
        }

        if (! empty($args['code'])) {
            $query->where('attributes.code', 'like', '%'.$args['code'].'%');
        }

        if (! empty($args['type'])) {
            $query->where('attributes.type', $args['type']);
        }

        if (! empty($args['admin_name'])) {
            $search = $args['admin_name'];
            $query->where(function ($q) use ($search) {
                $q->where('attributes.admin_name', 'like', '%'.$search.'%')
                    ->orWhere('at.name', 'like', '%'.$search.'%');
            });
        }

        foreach (self::BOOLEAN_FILTERS as $col) {
            if (isset($args[$col]) && in_array((string) $args[$col], ['0', '1'], true)) {
                $query->where('attributes.'.$col, (int) $args[$col]);
            }
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id' => 'attributes.id',
            'code' => 'attributes.code',
            'admin_name' => 'attributes.admin_name',
            'type' => 'attributes.type',
            'position' => 'attributes.position',
        ];

        $orderColumn = $columnMap[$column] ?? 'attributes.id';

        $query->orderBy($orderColumn, $direction);
    }

    protected function mapRow(object $row): AdminAttribute
    {
        $locale = $this->locale ?: app()->getLocale();

        $dto = new AdminAttribute;

        $dto->id = (int) $row->id;
        $dto->code = $row->code;
        $dto->type = $row->type;
        $dto->adminName = $row->translated_name ?? $row->admin_name;
        $dto->isRequired = (int) $row->is_required;
        $dto->isUnique = (int) $row->is_unique;
        $dto->valuePerLocale = (int) $row->value_per_locale;
        $dto->valuePerChannel = (int) $row->value_per_channel;
        $dto->isFilterable = (int) $row->is_filterable;
        $dto->isConfigurable = (int) $row->is_configurable;
        $dto->isVisibleOnFront = (int) $row->is_visible_on_front;
        $dto->isUserDefined = (int) $row->is_user_defined;
        $dto->swatchType = $row->swatch_type ?? null;
        $dto->position = $row->position !== null ? (int) $row->position : null;
        $dto->locale = $row->at_locale ?? $locale;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
