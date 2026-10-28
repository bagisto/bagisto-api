<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSectionFields;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\Theme\SectionSchema;

class AdminAppearanceSectionFieldsProvider implements ProviderInterface
{
    use ChecksAdminPermission;

    public function __construct(
        protected SectionSchema $sectionSchema,
        protected AppearanceScopeGuard $scope,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->authorizedAdmin('appearance.sections', 'bagistoapi::app.admin.appearance.no-permission');

        $args = $context['args'] ?? [];

        $id = $this->scope->pathValue($uriVariables, 'id') ?? $args['sectionId'] ?? $args['id'] ?? null;

        $section = $this->scope->sectionOrFail($id ? (int) basename((string) $id) : null);

        $channel = $this->scope->channel(['channel' => $section->channel_id]);

        $locale = $this->scope->locale($channel, array_filter(['locale' => $args['locale'] ?? null]))->code;

        $translation = $section->translate($locale);

        $result = new AdminAppearanceSectionFields;

        $result->section_id = (int) $section->id;
        $result->type = $section->type;
        $result->locale = $locale;
        $result->schema = (array) $this->sectionSchema->for($section->type);
        $result->options = (array) ($translation?->draft_options ?? $translation?->options ?? []);

        return $result;
    }
}
