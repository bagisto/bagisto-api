<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionDuplicateInput;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSectionDuplicate;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\Theme\Repositories\SectionRepository;

class AdminAppearanceSectionDuplicateProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected SectionRepository $sectionRepository,
        protected AppearanceScopeGuard $scope,
    ) {}

    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->authorizedAdmin('appearance.sections.create', 'bagistoapi::app.admin.appearance.no-permission');

        $args = $context['args']['input'] ?? [];

        $id = $this->scope->pathValue($uriVariables, 'id')
            ?? ($data instanceof AdminAppearanceSectionDuplicateInput ? $data->section_id : null)
            ?? ($args['sectionId'] ?? null);

        $section = $this->scope->sectionOrFail($id ? (int) basename((string) $id) : null);

        $this->scope->guardSingleton($section->type, $section->theme_code, (int) $section->channel_id);

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
