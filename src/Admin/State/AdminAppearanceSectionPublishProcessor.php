<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSectionPublish;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\Theme\Repositories\SectionRepository;

/**
 * Mirrors SectionController::publish — every drafted section of the theme and channel is
 * promoted at once, firing the update events core listeners expect.
 */
class AdminAppearanceSectionPublishProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    public function __construct(
        protected SectionRepository $sectionRepository,
        protected AppearanceScopeGuard $scope,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->authorizedAdmin('appearance.sections.edit', 'bagistoapi::app.admin.appearance.no-permission');

        $args = $context['args']['input'] ?? [];

        $theme = $this->scope->themeOrFail(
            $this->scope->pathValue($uriVariables, 'code') ?? ($data->code ?? null) ?? ($args['code'] ?? null)
        );

        $channel = $this->scope->channel(array_filter([
            'channel' => ($data->channel ?? null) ?? ($args['channel'] ?? null),
        ]));

        $drafted = $this->sectionRepository->draftedSections($channel->id, $theme['code']);

        $drafted->each(fn ($section) => Event::dispatch('section.update.before', $section->id));

        $published = $this->sectionRepository->publishDrafts($drafted);

        $published->each(fn ($section) => Event::dispatch('section.update.after', $section));

        $result = new AdminAppearanceSectionPublish;

        $result->theme_code = $theme['code'];
        $result->channel_id = (int) $channel->id;
        $result->section_ids = $published->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $result->message = __('bagistoapi::app.admin.appearance.section.published');

        return $result;
    }
}
