<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsChannelRestDto;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsChannel;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Models\Channel;

/**
 * Channel detail — GET /api/admin/settings/channels/{id} + adminSettingsChannel.
 *
 * Branches: GraphQL → the AdminSettingsChannel Eloquent model (translations/
 * locales/currencies/inventorySources connections + homeSeo object resolve);
 * REST → the flat AdminSettingsChannelRestDto built from the core Channel
 * relations (nested data as object arrays).
 */
class AdminSettingsChannelItemProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminSettingsChannel|AdminSettingsChannelRestDto
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.channel.not-found'));
        }

        if (! empty($context['graphql_operation_name'])) {
            $model = AdminSettingsChannel::with(['translations', 'locales', 'currencies', 'inventory_sources'])->find($id);

            if (! $model) {
                throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.channel.not-found'));
            }

            return $model;
        }

        $channel = Channel::with(['locales', 'currencies', 'inventory_sources', 'translations'])->find($id);

        if (! $channel) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.channel.not-found'));
        }

        return $this->buildRestDto($channel);
    }

    /**
     * Public alias used by the processor to reuse the REST mapping logic.
     */
    public function buildRestDtoPublic(object $channel): AdminSettingsChannelRestDto
    {
        return $this->buildRestDto($channel);
    }

    protected function buildRestDto(object $channel): AdminSettingsChannelRestDto
    {
        /** @var Channel $channel */
        $dto = new AdminSettingsChannelRestDto;

        $dto->id = (int) $channel->id;
        $dto->code = $channel->code;
        $dto->hostname = $channel->hostname;
        $dto->theme = $channel->theme;
        $dto->timezone = $channel->timezone;
        $dto->defaultLocaleId = $channel->default_locale_id !== null ? (int) $channel->default_locale_id : null;
        $dto->baseCurrencyId = $channel->base_currency_id !== null ? (int) $channel->base_currency_id : null;
        $dto->rootCategoryId = $channel->root_category_id !== null ? (int) $channel->root_category_id : null;
        $dto->isMaintenanceOn = (bool) $channel->is_maintenance_on;
        $dto->allowedIps = is_array($channel->allowed_ips)
            ? $channel->allowed_ips
            : (is_string($channel->allowed_ips) ? (array) json_decode($channel->allowed_ips, true) : null);
        $dto->logo = $channel->logo;
        $dto->logoUrl = $channel->logo ? Storage::url($channel->logo) : null;
        $dto->favicon = $channel->favicon;
        $dto->faviconUrl = $channel->favicon ? Storage::url($channel->favicon) : null;
        $dto->homeSeo = is_array($channel->home_seo)
            ? $channel->home_seo
            : (is_string($channel->home_seo) ? (array) json_decode($channel->home_seo, true) : null);
        $dto->createdAt = $channel->created_at?->toIso8601String();
        $dto->updatedAt = $channel->updated_at?->toIso8601String();

        $default = $this->resolveDefaultTranslation($channel);
        $dto->name = $default->name ?? null;
        $dto->description = $default->description ?? null;
        $dto->maintenanceModeText = $default->maintenance_mode_text ?? null;

        $dto->locales = $channel->locales->map(fn ($l) => [
            'id'        => (int) $l->id,
            'code'      => $l->code,
            'name'      => $l->name,
            'direction' => $l->direction,
        ])->values()->all();

        $dto->currencies = $channel->currencies->map(fn ($c) => [
            'id'     => (int) $c->id,
            'code'   => $c->code,
            'name'   => $c->name,
            'symbol' => $c->symbol,
        ])->values()->all();

        $dto->inventorySources = $channel->inventory_sources->map(fn ($s) => [
            'id'     => (int) $s->id,
            'code'   => $s->code,
            'name'   => $s->name,
            'status' => $s->status !== null ? (int) $s->status : null,
        ])->values()->all();

        $translations = [];
        foreach ($channel->translations as $t) {
            $translations[] = [
                'locale'              => $t->locale,
                'name'                => $t->name ?? null,
                'description'         => $t->description ?? null,
                'maintenanceModeText' => $t->maintenance_mode_text ?? null,
                'homeSeo'             => is_array($t->home_seo)
                    ? $t->home_seo
                    : (is_string($t->home_seo) ? (array) json_decode($t->home_seo, true) : null),
            ];
        }
        $dto->translations = $translations;

        return $dto;
    }

    private function resolveDefaultTranslation(object $channel): ?object
    {
        $localeCode = $channel->default_locale_id
            ? \Illuminate\Support\Facades\DB::table('locales')->where('id', $channel->default_locale_id)->value('code')
            : null;

        if ($localeCode) {
            $match = $channel->translations->firstWhere('locale', $localeCode);
            if ($match) {
                return $match;
            }
        }

        return $channel->translations->first();
    }
}
