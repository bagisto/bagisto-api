<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Models\AdminSettingsLocale;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/settings/locales + adminSettingsLocales GraphQL query.
 */
class AdminSettingsLocaleCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'code', 'name'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('locales')->select(
            'id',
            'code',
            'name',
            'direction',
            'logo_path',
            'created_at',
            'updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['code'])) {
            $query->where('code', 'like', '%'.$args['code'].'%');
        }

        if (! empty($args['name'])) {
            $query->where('name', 'like', '%'.$args['name'].'%');
        }

        if (! empty($args['direction'])) {
            $query->where('direction', $args['direction']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'   => 'id',
            'code' => 'code',
            'name' => 'name',
        ];

        $query->orderBy($columnMap[$column] ?? 'id', $direction);
    }

    protected function mapRow(object $row): AdminSettingsLocale
    {
        $dto = new AdminSettingsLocale;

        $dto->id = (int) $row->id;
        $dto->code = $row->code;
        $dto->name = $row->name;
        $dto->direction = $row->direction;
        $dto->logoPath = $row->logo_path ?? null;
        $dto->logoUrl = ! empty($row->logo_path) ? Storage::url($row->logo_path) : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
