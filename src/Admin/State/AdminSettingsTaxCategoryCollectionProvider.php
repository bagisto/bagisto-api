<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTaxCategory;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/settings/tax-categories + adminSettingsTaxCategories.
 *
 * Mirrors Webkul\Admin\DataGrids\Settings\TaxCategoryDataGrid — filters on code
 * and name (LIKE); sort on id, code, name. Listing rows omit `taxRates`
 * (detail-only — keeps the listing query cheap).
 */
class AdminSettingsTaxCategoryCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'code', 'name'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('tax_categories')->select(
            'tax_categories.id',
            'tax_categories.code',
            'tax_categories.name',
            'tax_categories.description',
            'tax_categories.created_at',
            'tax_categories.updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['code'])) {
            $query->where('tax_categories.code', 'like', '%'.$args['code'].'%');
        }

        if (! empty($args['name'])) {
            $query->where('tax_categories.name', 'like', '%'.$args['name'].'%');
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'   => 'tax_categories.id',
            'code' => 'tax_categories.code',
            'name' => 'tax_categories.name',
        ];

        $query->orderBy($columnMap[$column] ?? 'tax_categories.id', $direction);
    }

    protected function mapRow(object $row): AdminSettingsTaxCategory
    {
        $dto = new AdminSettingsTaxCategory;

        $dto->id = (int) $row->id;
        $dto->code = $row->code;
        $dto->name = $row->name;
        $dto->description = $row->description;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
