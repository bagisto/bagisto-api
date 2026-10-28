<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Prettus\Repository\Events\RepositoryEntityUpdated;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceThemeActivateInput;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceThemeActivate;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Theme\ThemeCatalog;

/**
 * Mirrors Appearance\ThemeController::activate — same guard, same four events per
 * channel, so anything listening for a theme switch keeps working.
 */
class AdminAppearanceThemeActivateProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    public function __construct(
        protected ThemeCatalog $themeCatalog,
        protected ChannelRepository $channelRepository,
        protected AppearanceScopeGuard $scope,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->authorizedAdmin('appearance.themes.activate', 'bagistoapi::app.admin.appearance.no-permission');

        $args = $context['args']['input'] ?? [];

        $code = (string) ($this->scope->pathValue($uriVariables, 'code')
            ?? ($data instanceof AdminAppearanceThemeActivateInput ? $data->code : null)
            ?? ($args['code'] ?? ''));

        $theme = $code ? $this->themeCatalog->find($code) : null;

        if (
            ! $theme
            || ! ($theme['is_installed'] ?? false)
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.appearance.theme.not-installed'));
        }

        $channelIds = $this->resolveChannelIds($data, $args);

        $channels = $this->channelRepository->findWhereIn('id', $channelIds);

        foreach ($channels as $channel) {
            $this->activateOn($channel, $code);
        }

        $result = new AdminAppearanceThemeActivate;

        $result->code = $code;

        $result->activated_on = $channels
            ->map(fn ($channel) => ['id' => (int) $channel->id, 'name' => $channel->name])
            ->values()
            ->all();

        $result->message = __('bagistoapi::app.admin.appearance.theme.activated');

        return $result;
    }

    /**
     * @return array<int, int>
     */
    protected function resolveChannelIds(mixed $data, array $args): array
    {
        $ids = ($data instanceof AdminAppearanceThemeActivateInput ? $data->channel_ids : null)
            ?? $args['channelIds']
            ?? request()->input('channelIds')
            ?? request()->input('channel_ids')
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

    protected function activateOn(object $channel, string $code): void
    {
        Event::dispatch('appearance.theme.activate.before', $channel->id);

        Event::dispatch('core.channel.update.before', $channel->id);

        $channel->theme = $code;

        $channel->save();

        Event::dispatch(new RepositoryEntityUpdated($this->channelRepository, $channel));

        Event::dispatch('core.channel.update.after', $channel);

        Event::dispatch('appearance.theme.activate.after', $channel);
    }
}
