<?php

namespace Webkul\BagistoApi\Admin\State;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Models\AdminSettingsChannel;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Core\Models\Channel;

class AdminSettingsChannelItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.settings.channel.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return Channel::with(['locales', 'currencies', 'inventory_sources', 'translations'])->find($id);
    }

    protected function mapToDto(object $channel): AdminSettingsChannel
    {
        /** @var Channel $channel */
        $dto = new AdminSettingsChannel;

        $dto->id = (int) $channel->id;
        $dto->code = $channel->code;
        $dto->hostname = $channel->hostname;
        $dto->theme = $channel->theme;
        $dto->timezone = $channel->timezone;
        $dto->defaultLocaleId = $channel->default_locale_id !== null ? (int) $channel->default_locale_id : null;
        $dto->baseCurrencyId = $channel->base_currency_id !== null ? (int) $channel->base_currency_id : null;
        $dto->rootCategoryId = $channel->root_category_id !== null ? (int) $channel->root_category_id : null;
        $dto->isMaintenanceOn = (bool) $channel->is_maintenance_on;
        $dto->allowedIps = $channel->allowed_ips;
        $dto->logo = $channel->logo;
        $dto->logoUrl = $channel->logo ? Storage::url($channel->logo) : null;
        $dto->favicon = $channel->favicon;
        $dto->faviconUrl = $channel->favicon ? Storage::url($channel->favicon) : null;
        $dto->createdAt = $channel->created_at?->toIso8601String();
        $dto->updatedAt = $channel->updated_at?->toIso8601String();

        // Resolve current-locale translated fields (name, description, maintenance_mode_text)
        try {
            $dto->name = $channel->name;
            $dto->description = $channel->description;
            $dto->maintenanceModeText = $channel->maintenance_mode_text;
            $dto->homeSeo = is_array($channel->home_seo) ? $channel->home_seo : null;
        } catch (\Throwable $e) {
            // Fall back to a direct read on the translations join if the magic accessor throws
            $row = DB::table('channel_translations')->where('channel_id', $channel->id)->first();
            if ($row) {
                $dto->name = $row->name ?? null;
                $dto->description = $row->description ?? null;
                $dto->maintenanceModeText = $row->maintenance_mode_text ?? null;
                $dto->homeSeo = $row->home_seo ? (array) json_decode($row->home_seo, true) : null;
            }
        }

        // BelongsToMany id lists
        $dto->localeIds = $channel->locales->pluck('id')->map(fn ($v) => (int) $v)->values()->all();
        $dto->currencyIds = $channel->currencies->pluck('id')->map(fn ($v) => (int) $v)->values()->all();
        $dto->inventorySourceIds = $channel->inventory_sources->pluck('id')->map(fn ($v) => (int) $v)->values()->all();

        // All-locale translations as a plain array (avoids API Platform IRI serialization).
        $translations = [];
        foreach ($channel->translations as $t) {
            $translations[] = [
                'locale'              => $t->locale,
                'name'                => $t->name ?? null,
                'description'         => $t->description ?? null,
                'homePageContent'     => $t->home_page_content ?? null,
                'footerContent'       => $t->footer_content ?? null,
                'maintenanceModeText' => $t->maintenance_mode_text ?? null,
                'homeSeo'             => is_array($t->home_seo)
                    ? $t->home_seo
                    : (is_string($t->home_seo) ? (array) json_decode($t->home_seo, true) : null),
            ];
        }
        $dto->translations = $translations;

        return $dto;
    }

    /**
     * Public alias used by the processor to share the mapping logic.
     */
    public function mapToDtoPublic(object $channel): AdminSettingsChannel
    {
        return $this->mapToDto($channel);
    }
}
