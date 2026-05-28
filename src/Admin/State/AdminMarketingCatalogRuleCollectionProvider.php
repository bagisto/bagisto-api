<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCatalogRule;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/marketing/catalog-rules + adminMarketingCatalogRules.
 *
 * Filters: name (LIKE), status (exact 0/1).
 * Sort:    id (default desc), name, sort_order.
 *
 * Listing rows omit `conditions`, `channels`, `customerGroups` (detail-only —
 * keeps the listing query cheap).
 */
class AdminMarketingCatalogRuleCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'name', 'sort_order'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('catalog_rules')->select(
            'catalog_rules.id',
            'catalog_rules.name',
            'catalog_rules.description',
            'catalog_rules.starts_from',
            'catalog_rules.ends_till',
            'catalog_rules.status',
            'catalog_rules.sort_order',
            'catalog_rules.condition_type',
            'catalog_rules.end_other_rules',
            'catalog_rules.action_type',
            'catalog_rules.discount_amount',
            'catalog_rules.created_at',
            'catalog_rules.updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['name'])) {
            $query->where('catalog_rules.name', 'like', '%'.$args['name'].'%');
        }

        if (isset($args['status']) && $args['status'] !== '') {
            $query->where('catalog_rules.status', (int) $args['status']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'         => 'catalog_rules.id',
            'name'       => 'catalog_rules.name',
            'sort_order' => 'catalog_rules.sort_order',
        ];

        $query->orderBy($columnMap[$column] ?? 'catalog_rules.id', $direction);
    }

    protected function mapRow(object $row): AdminMarketingCatalogRule
    {
        $dto = new AdminMarketingCatalogRule;

        $dto->id = (int) $row->id;
        $dto->name = $row->name;
        $dto->description = $row->description;
        $dto->startsFrom = $row->starts_from;
        $dto->endsTill = $row->ends_till;
        $dto->status = $row->status !== null ? (int) $row->status : null;
        $dto->sortOrder = $row->sort_order !== null ? (int) $row->sort_order : null;
        $dto->conditionType = $row->condition_type !== null ? (int) $row->condition_type : null;
        $dto->endOtherRules = $row->end_other_rules !== null ? (int) $row->end_other_rules : null;
        $dto->actionType = $row->action_type;
        $dto->discountAmount = $row->discount_amount !== null ? (float) $row->discount_amount : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
