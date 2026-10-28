<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceThemeImpact;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Theme\ThemeCatalog;

class AdminAppearanceThemeImpactProvider implements ProviderInterface
{
    use ChecksAdminPermission;

    public function __construct(
        protected ThemeCatalog $themeCatalog,
        protected ChannelRepository $channelRepository,
        protected AppearanceScopeGuard $scope,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->authorizedAdmin('appearance.themes', 'bagistoapi::app.admin.appearance.no-permission');

        $args = $context['args'] ?? [];

        $code = (string) ($this->scope->pathValue($uriVariables, 'code') ?? $args['code'] ?? '');

        if (! $this->themeCatalog->find($code)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.appearance.theme.not-found'));
        }

        $channelIds = $this->resolveChannelIds($args);

        $result = new AdminAppearanceThemeImpact;

        $result->code = $code;

        $result->impact = $this->channelRepository
            ->findWhereIn('id', $channelIds)
            ->map(fn ($channel) => [
                'channelId' => (int) $channel->id,
                'channel' => $channel->name,
                'currentTheme' => $this->themeName($channel->theme),
                'customizations' => $channel->theme && $channel->theme !== $code
                    ? $this->themeCatalog->sectionCount($channel->id, $channel->theme)
                    : 0,
            ])
            ->filter(fn (array $row) => $row['customizations'] > 0)
            ->values()
            ->all();

        return $result;
    }

    /**
     * @return array<int, int>
     */
    protected function resolveChannelIds(array $args): array
    {
        $ids = $args['channelIds']
            ?? request()->input('channel_ids')
            ?? request()->input('channelIds')
            ?? [];

        $ids = array_values(array_filter(array_map('intval', (array) $ids)));

        if (! $ids) {
            throw new InvalidInputException(__('bagistoapi::app.admin.appearance.theme.channel-ids-required'), 422);
        }

        $known = $this->channelRepository->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (array_diff($ids, $known)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.appearance.theme.unknown-channel'), 422);
        }

        return $ids;
    }

    protected function themeName(?string $code): ?string
    {
        if (! $code) {
            return null;
        }

        return config('themes.shop.'.$code.'.name') ?? $code;
    }
}
