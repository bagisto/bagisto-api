<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionDuplicateInput;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSectionDuplicate;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Theme\Models\Section;
use Webkul\Theme\Repositories\SectionRepository;

class AdminAppearanceSectionDuplicateProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    public function __construct(
        protected SectionRepository $sectionRepository,
        protected AppearanceScopeGuard $scope,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->authorizedAdmin('appearance.sections.create', 'bagistoapi::app.admin.appearance.no-permission');

        $args = $context['args']['input'] ?? [];

        $id = $this->scope->pathValue($uriVariables, 'id')
            ?? ($data instanceof AdminAppearanceSectionDuplicateInput ? $data->section_id : null)
            ?? ($args['sectionId'] ?? null);

        $section = $this->scope->sectionOrFail($id ? (int) basename((string) $id) : null);

        // A channel has room for one footer links section, and the copy would be a
        // second one, so the type cannot be copied at all.
        if ($section->type === Section::FOOTER_LINKS) {
            throw new InvalidInputException(__('bagistoapi::app.admin.appearance.section.footer-links-exists'), 422);
        }

        Event::dispatch('section.create.before');

        $copy = $this->sectionRepository->duplicate($section->id);

        Event::dispatch('section.create.after', $copy);

        $result = new AdminAppearanceSectionDuplicate;

        $result->source_id = (int) $section->id;
        $result->section_id = (int) $copy->id;
        $result->name = $copy->name;
        $result->type = $copy->type;
        $result->message = __('bagistoapi::app.admin.appearance.section.duplicated');

        return $result;
    }
}
