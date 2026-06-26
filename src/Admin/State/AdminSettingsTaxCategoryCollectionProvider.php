<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsTaxCategoryRestDto;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTaxCategory;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/settings/tax-categories + adminSettingsTaxCategories.
 *
 * Mirrors Webkul\Admin\DataGrids\Settings\TaxCategoryDataGrid — filters on code
 * and name (LIKE); sort on id, code, name.
 *
 * Branches: GraphQL → an AdminSettingsTaxCategory Eloquent row per result (the
 * `tax_rates` connection is set empty on listings — detail-only, no N+1); REST →
 * the flat AdminSettingsTaxCategoryRestDto (taxRates omitted on listing rows).
 */
class AdminSettingsTaxCategoryCollectionProvider extends AbstractAdminCollectionProvider
{
    protected bool $listingIsGraphQL = false;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): \ApiPlatform\Laravel\Eloquent\Paginator
    {
        $this->listingIsGraphQL = ! empty($context['graphql_operation_name']);

        return parent::provide($operation, $uriVariables, $context);
    }

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

    protected function mapRow(object $row): object
    {
        if ($this->listingIsGraphQL) {
            return $this->mapRowToEloquent($row);
        }

        $dto = new AdminSettingsTaxCategoryRestDto;

        $dto->id = (int) $row->id;
        $dto->code = $row->code;
        $dto->name = $row->name;
        $dto->description = $row->description;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }

    /**
     * GraphQL listing row → Eloquent AdminSettingsTaxCategory. The `tax_rates`
     * relation is set empty (detail-only on the listing — no per-row query).
     */
    protected function mapRowToEloquent(object $row): AdminSettingsTaxCategory
    {
        $model = (new AdminSettingsTaxCategory)->forceFill([
            'id'          => (int) $row->id,
            'code'        => $row->code,
            'name'        => $row->name,
            'description' => $row->description,
            'created_at'  => $row->created_at,
            'updated_at'  => $row->updated_at,
        ]);

        $model->setRelation('tax_rates', collect());

        return $model;
    }
}
