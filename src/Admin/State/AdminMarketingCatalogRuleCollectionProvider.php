<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCatalogRuleRestDto;
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
    protected bool $listingIsGraphQL = false;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $this->listingIsGraphQL = ! empty($context['graphql_operation_name']);

        return parent::provide($operation, $uriVariables, $context);
    }

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
        if (! empty($args['id'])) {
            $ids = is_array($args['id'])
                ? $args['id']
                : array_filter(array_map('trim', explode(',', (string) $args['id'])));
            $ids = array_values(array_filter(array_map('intval', $ids)));
            if ($ids) {
                $query->whereIn('catalog_rules.id', $ids);
            }
        }

        if (! empty($args['name'])) {
            $query->where('catalog_rules.name', 'like', '%'.$args['name'].'%');
        }

        if (isset($args['status']) && $args['status'] !== '') {
            $query->where('catalog_rules.status', (int) $args['status']);
        }

        if (isset($args['sort_order']) && $args['sort_order'] !== '' && $args['sort_order'] !== null) {
            $query->where('catalog_rules.sort_order', (int) $args['sort_order']);
        }

        if (! empty($args['starts_from_from'])) {
            $query->where('catalog_rules.starts_from', '>=', $args['starts_from_from']);
        }
        if (! empty($args['starts_from_to'])) {
            $query->where('catalog_rules.starts_from', '<=', $args['starts_from_to']);
        }
        if (! empty($args['ends_till_from'])) {
            $query->where('catalog_rules.ends_till', '>=', $args['ends_till_from']);
        }
        if (! empty($args['ends_till_to'])) {
            $query->where('catalog_rules.ends_till', '<=', $args['ends_till_to']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id' => 'catalog_rules.id',
            'name' => 'catalog_rules.name',
            'sort_order' => 'catalog_rules.sort_order',
        ];

        $query->orderBy($columnMap[$column] ?? 'catalog_rules.id', $direction);
    }

    protected function mapRow(object $row): object
    {
        if ($this->listingIsGraphQL) {
            return $this->mapRowToEloquent($row);
        }

        $dto = new AdminMarketingCatalogRuleRestDto;

        $dto->id = (int) $row->id;
        $dto->name = $row->name;
        $dto->description = $row->description;
        $dto->startsFrom = $row->starts_from ? Carbon::parse($row->starts_from)->toIso8601String() : null;
        $dto->endsTill = $row->ends_till ? Carbon::parse($row->ends_till)->toIso8601String() : null;
        $dto->status = $row->status !== null ? (int) $row->status : null;
        $dto->sortOrder = $row->sort_order !== null ? (int) $row->sort_order : null;
        $dto->conditionType = $row->condition_type !== null ? (int) $row->condition_type : null;
        $dto->endOtherRules = $row->end_other_rules !== null ? (int) $row->end_other_rules : null;
        $dto->actionType = $row->action_type;
        $dto->discountAmount = $row->discount_amount !== null ? (float) $row->discount_amount : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        // channels / customerGroups / conditions are detail-only — null on list rows.

        return $dto;
    }

    /**
     * GraphQL listing row → Eloquent AdminMarketingCatalogRule. The channels /
     * customerGroups connections are set empty (detail-only — no per-row N+1).
     */
    protected function mapRowToEloquent(object $row): AdminMarketingCatalogRule
    {
        $model = (new AdminMarketingCatalogRule)->forceFill([
            'id' => (int) $row->id,
            'name' => $row->name,
            'description' => $row->description,
            'starts_from' => $row->starts_from,
            'ends_till' => $row->ends_till,
            'status' => $row->status,
            'sort_order' => $row->sort_order,
            'condition_type' => $row->condition_type,
            'end_other_rules' => $row->end_other_rules,
            'action_type' => $row->action_type,
            'discount_amount' => $row->discount_amount,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);

        $model->setRelation('channels', collect());
        $model->setRelation('customer_groups', collect());

        return $model;
    }
}
