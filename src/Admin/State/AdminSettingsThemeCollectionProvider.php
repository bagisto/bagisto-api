<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTheme;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/settings/themes + adminSettingsThemes GraphQL query.
 *
 * Slim listing — translations are NOT inlined here (would be N+1 across rows).
 * Use the detail endpoint to get the per-locale options blob.
 */
class AdminSettingsThemeCollectionProvider extends AbstractAdminCollectionProvider
{
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

    protected function mapRow(object $row): AdminSettingsTheme
    {
        $dto = new AdminSettingsTheme;

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
}
