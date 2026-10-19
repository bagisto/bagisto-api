<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsTaxCategoryRestDto;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTaxCategory;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

class AdminSettingsTaxCategoryCollectionProvider extends AbstractAdminCollectionProvider
{
    /**
     * The listing is graph ql.
     */
    protected bool $listingIsGraphQL = false;

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $this->listingIsGraphQL = ! empty($context['graphql_operation_name']);

        return parent::provide($operation, $uriVariables, $context);
    }

    /**
     * Get sortable.
     */
    protected function getSortable(): array
    {
        return ['id', 'code', 'name'];
    }

    /**
     * Build query.
     */
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

    /**
     * Apply filters.
     */
    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['code'])) {
            $query->where('tax_categories.code', db_grammar()->caseInsensitiveLike(), '%'.$args['code'].'%');
        }

        if (! empty($args['name'])) {
            $query->where('tax_categories.name', db_grammar()->caseInsensitiveLike(), '%'.$args['name'].'%');
        }
    }

    /**
     * Apply sort.
     */
    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id' => 'tax_categories.id',
            'code' => 'tax_categories.code',
            'name' => 'tax_categories.name',
        ];

        $query->orderBy($columnMap[$column] ?? 'tax_categories.id', $direction);
    }

    /**
     * Map row.
     */
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
            'id' => (int) $row->id,
            'code' => $row->code,
            'name' => $row->name,
            'description' => $row->description,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);

        $model->setRelation('tax_rates', collect());

        return $model;
    }
}
