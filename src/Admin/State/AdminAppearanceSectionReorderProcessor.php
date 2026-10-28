<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionReorderInput;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSectionReorder;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Theme\Models\Section;
use Webkul\Theme\Repositories\SectionRepository;

class AdminAppearanceSectionReorderProcessor implements ProcessorInterface
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

        $sectionIds = ($data instanceof AdminAppearanceSectionReorderInput ? $data->section_ids : null)
            ?? ($args['sectionIds'] ?? null)
            ?? request()->input('sectionIds')
            ?? request()->input('section_ids')
            ?? [];

        $sectionIds = array_values(array_filter(array_map('intval', (array) $sectionIds)));

        if (! $sectionIds) {
            throw new InvalidInputException(__('bagistoapi::app.admin.appearance.section.section-ids-required'), 422);
        }

        $sections = Section::query()->whereIn('id', $sectionIds)->get();

        if ($sections->count() !== count(array_unique($sectionIds))) {
            throw new InvalidInputException(__('bagistoapi::app.admin.appearance.section.unknown-section'), 422);
        }

        // Positions are read back as 1..n over the ids given, so a partial list would
        // renumber those sections on top of the ones left out of it. The order is only
        // meaningful for a whole theme + channel, so that is what has to be sent.
        $scopes = $sections
            ->map(fn ($section) => $section->theme_code.'|'.$section->channel_id)
            ->unique();

        if ($scopes->count() !== 1) {
            throw new InvalidInputException(__('bagistoapi::app.admin.appearance.section.reorder-single-scope'), 422);
        }

        [$themeCode, $channelId] = explode('|', (string) $scopes->first());

        $scopeIds = $this->scope->orderedSectionIds($themeCode, (int) $channelId);

        if (array_diff($scopeIds, $sectionIds) || array_diff($sectionIds, $scopeIds)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.appearance.section.reorder-incomplete'), 422);
        }

        $ordered = $this->scope->withPinnedLast($sectionIds);

        Event::dispatch('section.reorder.before', $ordered);

        $this->sectionRepository->saveOrderDraft($ordered);

        $sections = $this->sectionRepository->findWhereIn('id', $ordered);

        Event::dispatch('section.reorder.after', $sections);

        $result = new AdminAppearanceSectionReorder;

        $result->section_ids = $ordered;

        $result->has_draft = $sections
            ->mapWithKeys(fn ($section) => [(string) $section->id => $this->sectionRepository->hasDraft($section)])
            ->all();

        $result->message = __('bagistoapi::app.admin.appearance.section.order-staged');

        return $result;
    }
}
