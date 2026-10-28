<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsChannelRestDto;
use Webkul\BagistoApi\Admin\Models\AdminSettingsChannel;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

class AdminSettingsChannelCollectionProvider extends AbstractAdminCollectionProvider
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
                'channels.home_seo',
                'channels.logo',
                'channels.favicon',
                'channels.root_category_id',
                'channels.default_locale_id',
                'channels.base_currency_id',
                'channels.created_at',
                'channels.updated_at',
                'channel_translations.name as translated_name',
                'channel_translations.description as translated_description',
                'channel_translations.maintenance_mode_text as translated_maintenance_mode_text',
            );
    }

    /**
     * Apply filters.
     */
    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['code'])) {
            $query->where('channels.code', db_grammar()->caseInsensitiveLike(), '%'.$args['code'].'%');
        }

        if (! empty($args['name'])) {
            $query->where('channel_translations.name', db_grammar()->caseInsensitiveLike(), '%'.$args['name'].'%');
        }

        if (! empty($args['hostname'])) {
            $query->where('channels.hostname', db_grammar()->caseInsensitiveLike(), '%'.$args['hostname'].'%');
        }
    }

    /**
     * Apply sort.
     */
    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id' => 'channels.id',
            'code' => 'channels.code',
            'name' => 'channel_translations.name',
        ];

        $query->orderBy($columnMap[$column] ?? 'channels.id', $direction);
    }

    /**
     * Map row.
     */
    protected function mapRow(object $row): object
    {
        if ($this->listingIsGraphQL) {
            return $this->mapRowToEloquent($row);
        }

        $dto = new AdminSettingsChannelRestDto;

        $dto->id = (int) $row->id;
        $dto->code = $row->code;
        $dto->name = $row->translated_name ?? null;
        $dto->description = $row->translated_description ?? null;
        $dto->maintenanceModeText = $row->translated_maintenance_mode_text ?? null;
        $dto->hostname = $row->hostname;
        $dto->theme = $row->theme;
        $dto->timezone = $row->timezone;
        $dto->defaultLocaleId = $row->default_locale_id !== null ? (int) $row->default_locale_id : null;
        $dto->baseCurrencyId = $row->base_currency_id !== null ? (int) $row->base_currency_id : null;
        $dto->rootCategoryId = $row->root_category_id !== null ? (int) $row->root_category_id : null;
        $dto->isMaintenanceOn = $row->is_maintenance_on !== null ? (bool) $row->is_maintenance_on : null;
        $dto->allowedIps = $row->allowed_ips ? (array) json_decode($row->allowed_ips, true) : null;
        $dto->homeSeo = $row->home_seo ? (array) json_decode($row->home_seo, true) : null;
        $dto->logo = $row->logo;
        $dto->logoUrl = $row->logo ? Storage::url($row->logo) : null;
        $dto->favicon = $row->favicon;
        $dto->faviconUrl = $row->favicon ? Storage::url($row->favicon) : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }

    /**
     * GraphQL listing row → Eloquent AdminSettingsChannel. The relations are set
     * empty (detail-only on the listing — no per-row query / N+1).
     */
    protected function mapRowToEloquent(object $row): AdminSettingsChannel
    {
        $model = (new AdminSettingsChannel)->forceFill([
            'id' => (int) $row->id,
            'code' => $row->code,
            'hostname' => $row->hostname,
            'theme' => $row->theme,
            'timezone' => $row->timezone,
            'is_maintenance_on' => $row->is_maintenance_on,
            'allowed_ips' => $row->allowed_ips,
            'home_seo' => $row->home_seo,
            'logo' => $row->logo,
            'favicon' => $row->favicon,
            'root_category_id' => $row->root_category_id,
            'default_locale_id' => $row->default_locale_id,
            'base_currency_id' => $row->base_currency_id,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);

        $model->setRelation('translations', collect());
        $model->setRelation('locales', collect());
        $model->setRelation('currencies', collect());
        $model->setRelation('inventory_sources', collect());

        return $model;
    }
}
