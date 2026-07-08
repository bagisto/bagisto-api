<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsThemeRestDto;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTheme;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/settings/themes + adminSettingsThemes GraphQL query.
 *
 * Slim listing — translations are NOT inlined here (would be N+1 across rows).
 * Use the detail endpoint to get the per-locale options blob.
 *
 * Branches: GraphQL → an AdminSettingsTheme Eloquent row per result (the
 * `translations` connection is set empty on listings — detail-only, no N+1);
 * REST → the flat AdminSettingsThemeRestDto.
 */
class AdminSettingsThemeCollectionProvider extends AbstractAdminCollectionProvider
{
    protected bool $listingIsGraphQL = false;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $this->listingIsGraphQL = ! empty($context['graphql_operation_name']);

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return ['id', 'name', 'type', 'sort_order', 'theme_code', 'channel_id', 'status'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('theme_customizations')->select(
            'id',
            'name',
            'type',
            'sort_order',
            'status',
            'channel_id',
            'theme_code',
            'created_at',
            'updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['name'])) {
            $query->where('name', 'like', '%'.$args['name'].'%');
        }

        if (! empty($args['type'])) {
            $query->where('type', $args['type']);
        }

        if (! empty($args['theme_code'])) {
            $query->where('theme_code', $args['theme_code']);
        }

        if (isset($args['channel_id']) && $args['channel_id'] !== '' && $args['channel_id'] !== null) {
            $query->where('channel_id', (int) $args['channel_id']);
        }

        if (isset($args['status']) && $args['status'] !== '' && $args['status'] !== null) {
            $query->where('status', (int) $args['status']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $sortable = $this->getSortable();
        $column = in_array($column, $sortable, true) ? $column : 'id';

        $query->orderBy($column, $direction);
    }

    protected function mapRow(object $row): object
    {
        if ($this->listingIsGraphQL) {
            return $this->mapRowToEloquent($row);
        }

        $dto = new AdminSettingsThemeRestDto;

        $dto->id = (int) $row->id;
        $dto->name = $row->name;
        $dto->type = $row->type;
        $dto->sortOrder = (int) $row->sort_order;
        $dto->status = (bool) $row->status;
        $dto->channelId = (int) $row->channel_id;
        $dto->themeCode = $row->theme_code;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }

    /**
     * GraphQL listing row → Eloquent AdminSettingsTheme. The `translations`
     * relation is set empty (detail-only on the listing — no per-row query).
     */
    protected function mapRowToEloquent(object $row): AdminSettingsTheme
    {
        $model = (new AdminSettingsTheme)->forceFill([
            'id' => (int) $row->id,
            'name' => $row->name,
            'type' => $row->type,
            'sort_order' => (int) $row->sort_order,
            'status' => (bool) $row->status,
            'channel_id' => (int) $row->channel_id,
            'theme_code' => $row->theme_code,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);

        $model->setRelation('translations', collect());

        return $model;
    }
}
