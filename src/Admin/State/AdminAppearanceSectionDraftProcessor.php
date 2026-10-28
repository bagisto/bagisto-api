<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionDraftInput;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSectionDraft;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\MapsAppearanceSection;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Theme\Repositories\SectionRepository;

class AdminAppearanceSectionDraftProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;
    use MapsAppearanceSection;

    public function __construct(
        protected SectionRepository $sectionRepository,
        protected AppearanceScopeGuard $scope,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->authorizedAdmin('appearance.sections.edit', 'bagistoapi::app.admin.appearance.no-permission');

        $args = $context['args']['input'] ?? [];

        $id = $this->scope->pathValue($uriVariables, 'id')
            ?? ($data instanceof AdminAppearanceSectionDraftInput ? $data->section_id : null)
            ?? ($args['sectionId'] ?? null);

        $section = $this->scope->sectionOrFail($id ? (int) basename((string) $id) : null);

        $options = ($data instanceof AdminAppearanceSectionDraftInput ? $data->options : null)
            ?? ($args['options'] ?? null)
            ?? request()->input('options');

        if (! is_array($options)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.appearance.section.options-required'), 422);
        }

        $channel = $this->scope->channel(['channel' => $section->channel_id]);

        $requestedLocale = ($data instanceof AdminAppearanceSectionDraftInput ? $data->locale : null)
            ?? ($args['locale'] ?? null);

        $locale = $this->scope->locale($channel, array_filter(['locale' => $requestedLocale]))->code;

        Event::dispatch('section.draft.save.before', $section->id);

        $saved = $this->sectionRepository->saveDraft($section->id, $locale, $options);

        Event::dispatch('section.draft.save.after', $saved);

        $result = new AdminAppearanceSectionDraft;

        $result->section_id = (int) $saved->id;
        $result->locale = $locale;
        $result->has_draft = $this->sectionRepository->hasDraft($saved);
        $result->message = __('bagistoapi::app.admin.appearance.section.draft-saved');

        return $result;
    }
}
