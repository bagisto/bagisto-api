<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Models\AdminCategory;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for the admin Catalog → Categories datagrid endpoint.
 *
 * REST: GET /api/admin/catalog/categories
 *
 * Mirrors Webkul\Admin\DataGrids\Catalog\CategoryDataGrid 1:1 — same DB join
 * (categories × category_translations on the active locale), same filterable
 * columns, same sort columns.
 */
class AdminCategoryCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'name', 'position', 'status'];
    }

    protected function buildQuery(array $args)
    {
        $locale = $args['locale'] ?? app()->getLocale();

        return DB::table('categories')
            ->leftJoin('category_translations as ct', function ($j) use ($locale) {
                $j->on('categories.id', '=', 'ct.category_id')
                    ->where('ct.locale', $locale);
            })
            ->select(
                'categories.id',
                'categories.position',
                'categories.status',
                'categories.parent_id',
                'categories.display_mode',
                'categories.logo_path',
                'categories.banner_path',
                'categories.created_at',
                'categories.updated_at',
                'ct.name',
                'ct.slug',
                'ct.description',
                'ct.locale as ct_locale',
            )
            ->groupBy('categories.id');
    }

    protected function applyFilters($query, array $args): void
    {
        // category_id — single int or comma-separated list
        if (! empty($args['category_id'])) {
            $ids = is_array($args['category_id'])
                ? $args['category_id']
                : array_filter(array_map('trim', explode(',', (string) $args['category_id'])));
            $ids = array_values(array_filter(array_map('intval', $ids)));
            if ($ids) {
                $query->whereIn('categories.id', $ids);
            }
        }

        // name — partial match
        if (! empty($args['name'])) {
            $query->where('ct.name', 'like', '%'.$args['name'].'%');
        }

        // position — exact
        if (isset($args['position']) && $args['position'] !== '' && $args['position'] !== null) {
            $query->where('categories.position', (int) $args['position']);
        }

        // status — only 0 or 1 are valid; silently drop anything else
        if (isset($args['status']) && in_array((string) $args['status'], ['0', '1'], true)) {
            $query->where('categories.status', (int) $args['status']);
        }

        // parent_id — exact
        if (isset($args['parent_id']) && $args['parent_id'] !== '' && $args['parent_id'] !== null) {
            $query->where('categories.parent_id', (int) $args['parent_id']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'       => 'categories.id',
            'name'     => 'ct.name',
            'position' => 'categories.position',
            'status'   => 'categories.status',
        ];

        $orderColumn = $columnMap[$column] ?? 'categories.id';

        $query->orderBy($orderColumn, $direction);
    }

    protected function mapRow(object $row): AdminCategory
    {
        $locale = app()->getLocale();

        $dto = new AdminCategory;

        $dto->id = (int) $row->id;
        $dto->position = (int) $row->position;
        $dto->status = (int) $row->status;
        $dto->parentId = $row->parent_id !== null ? (int) $row->parent_id : null;
        $dto->displayMode = $row->display_mode;
        $dto->logoUrl = $row->logo_path ? Storage::url($row->logo_path) : null;
        $dto->bannerUrl = $row->banner_path ? Storage::url($row->banner_path) : null;
        $dto->name = $row->name;
        $dto->slug = $row->slug;
        $dto->description = $row->description;
        $dto->locale = $row->ct_locale ?? $locale;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
