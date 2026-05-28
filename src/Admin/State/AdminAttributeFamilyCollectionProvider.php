<?php

namespace Webkul\BagistoApi\Admin\State;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminAttributeFamily;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for the admin Catalog → Attribute Families datagrid endpoint.
 *
 * REST: GET /api/admin/catalog/families
 *
 * Mirrors Webkul\Admin\DataGrids\Catalog\AttributeFamilyDataGrid 1:1 — same 3 columns:
 * id, code, name. No timestamps on the attribute_families table.
 *
 * Filters: id (int or comma list), code (partial), name (partial).
 * Sort allow-list: id (default desc), code, name.
 */
class AdminAttributeFamilyCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'code', 'name'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('attribute_families')
            ->select(
                'id',
                'code',
                'name',
            );
    }

    protected function applyFilters($query, array $args): void
    {
        // id — single int or comma-separated list
        if (! empty($args['id'])) {
            $ids = is_array($args['id'])
                ? $args['id']
                : array_filter(array_map('trim', explode(',', (string) $args['id'])));
            $ids = array_values(array_filter(array_map('intval', $ids)));
            if ($ids) {
                $query->whereIn('id', $ids);
            }
        }

        // code — partial match
        if (! empty($args['code'])) {
            $query->where('code', 'like', '%'.$args['code'].'%');
        }

        // name — partial match
        if (! empty($args['name'])) {
            $query->where('name', 'like', '%'.$args['name'].'%');
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $query->orderBy($column, $direction);
    }

    protected function mapRow(object $row): AdminAttributeFamily
    {
        $dto = new AdminAttributeFamily;

        $dto->id = (int) $row->id;
        $dto->code = $row->code;
        $dto->name = $row->name;

        // attributeGroups is detail-only; null in listing rows
        $dto->attributeGroups = null;

        return $dto;
    }
}
