<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionStatusInput;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSectionStatus;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Theme\Repositories\SectionRepository;

class AdminAppearanceSectionStatusProcessor implements ProcessorInterface
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

        $id = $this->scope->pathValue($uriVariables, 'id')
            ?? ($data instanceof AdminAppearanceSectionStatusInput ? $data->section_id : null)
            ?? ($args['sectionId'] ?? null);

        $section = $this->scope->sectionOrFail($id ? (int) basename((string) $id) : null);

        $status = ($data instanceof AdminAppearanceSectionStatusInput ? $data->status : null)
            ?? ($args['status'] ?? null)
            ?? (request()->has('status') ? request()->boolean('status') : null);

        if (is_null($status)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.appearance.section.status-required'), 422);
        }

        Event::dispatch('section.draft.save.before', $section->id);

        $saved = $this->sectionRepository->saveStatusDraft($section->id, (bool) $status);

        Event::dispatch('section.draft.save.after', $saved);

        $result = new AdminAppearanceSectionStatus;

        $result->section_id = (int) $saved->id;
        $result->draft_status = is_null($saved->draft_status) ? null : (bool) $saved->draft_status;
        $result->has_draft = $this->sectionRepository->hasDraft($saved);
        $result->message = __('bagistoapi::app.admin.appearance.section.status-staged');

        return $result;
    }
}
