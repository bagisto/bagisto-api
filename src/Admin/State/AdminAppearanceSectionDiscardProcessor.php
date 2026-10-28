<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSectionDiscard;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\Theme\Repositories\SectionRepository;

/**
 * Mirrors SectionController::discard — throws away every staged edit of the theme and
 * channel, leaving the published content untouched.
 */
class AdminAppearanceSectionDiscardProcessor implements ProcessorInterface
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

        $drafted->each(fn ($section) => Event::dispatch('section.draft.discard.before', $section->id));

        $discarded = $this->sectionRepository->discardDrafts($drafted);

        $discarded->each(fn ($section) => Event::dispatch('section.draft.discard.after', $section));

        $result = new AdminAppearanceSectionDiscard;

        $result->theme_code = $theme['code'];
        $result->channel_id = (int) $channel->id;
        $result->section_ids = $discarded->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $result->message = __('bagistoapi::app.admin.appearance.section.discarded');

        return $result;
    }
}
