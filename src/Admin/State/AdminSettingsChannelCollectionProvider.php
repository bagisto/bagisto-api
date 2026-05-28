<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminSettingsChannel;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/settings/channels + adminSettingsChannels.
 *
 * Mirrors Webkul\Admin\DataGrids\Settings\ChannelDataGrid — filters on code,
 * name (via channel_translations), hostname; sort on id, code, name.
 */
class AdminSettingsChannelCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'code', 'name'];
    }

    protected function buildQuery(array $args)
    {
        $locale = (string) ($args['locale'] ?? (function_exists('app') ? app()->getLocale() : 'en'));

        return DB::table('channels')
            ->leftJoin('channel_translations', function ($join) use ($locale) {
                $join->on('channels.id', '=', 'channel_translations.channel_id')
                    ->where('channel_translations.locale', '=', $locale);
            })
            ->select(
                'channels.id',
                'channels.code',
                'channels.hostname',
                'channels.theme',
                'channels.timezone',
                'channels.is_maintenance_on',
                'channels.allowed_ips',
                'channels.logo',
                'channels.favicon',
                'channels.root_category_id',
                'channels.default_locale_id',
                'channels.base_currency_id',
                'channels.created_at',
                'channels.updated_at',
                'channel_translations.name as translated_name',
                'channel_translations.description as translated_description',
            );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['code'])) {
            $query->where('channels.code', 'like', '%'.$args['code'].'%');
        }

        if (! empty($args['name'])) {
            $query->where('channel_translations.name', 'like', '%'.$args['name'].'%');
        }

        if (! empty($args['hostname'])) {
            $query->where('channels.hostname', 'like', '%'.$args['hostname'].'%');
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'   => 'channels.id',
            'code' => 'channels.code',
            'name' => 'channel_translations.name',
        ];

        $query->orderBy($columnMap[$column] ?? 'channels.id', $direction);
    }

    protected function mapRow(object $row): AdminSettingsChannel
    {
        $dto = new AdminSettingsChannel;

        $dto->id = (int) $row->id;
        $dto->code = $row->code;
        $dto->name = $row->translated_name ?? null;
        $dto->description = $row->translated_description ?? null;
        $dto->hostname = $row->hostname;
        $dto->theme = $row->theme;
        $dto->timezone = $row->timezone;
        $dto->defaultLocaleId = $row->default_locale_id !== null ? (int) $row->default_locale_id : null;
        $dto->baseCurrencyId = $row->base_currency_id !== null ? (int) $row->base_currency_id : null;
        $dto->rootCategoryId = $row->root_category_id !== null ? (int) $row->root_category_id : null;
        $dto->isMaintenanceOn = $row->is_maintenance_on !== null ? (bool) $row->is_maintenance_on : null;
        $dto->allowedIps = $row->allowed_ips;
        $dto->logo = $row->logo;
        $dto->logoUrl = $row->logo ? \Illuminate\Support\Facades\Storage::url($row->logo) : null;
        $dto->favicon = $row->favicon;
        $dto->faviconUrl = $row->favicon ? \Illuminate\Support\Facades\Storage::url($row->favicon) : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
