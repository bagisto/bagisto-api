<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionRestDto;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSection;
use Webkul\Theme\Models\Section;

/**
 * One section, shaped for either transport.
 *
 * GraphQL is served the Eloquent resource so `translations` resolves as a connection;
 * REST is served the flat DTO.
 */
trait MapsAppearanceSection
{
    protected function isGraphQL(array $context): bool
    {
        return ! empty($context['graphql_operation_name']);
    }

    protected function toEloquent(Section $section): AdminAppearanceSection
    {
        return AdminAppearanceSection::with('translations')->findOrFail($section->id);
    }

    protected function toRestDto(Section $section): AdminAppearanceSectionRestDto
    {
        $dto = new AdminAppearanceSectionRestDto;

        $dto->id = (int) $section->id;
        $dto->name = $section->name;
        $dto->type = $section->type;
        $dto->theme_code = $section->theme_code;
        $dto->channel_id = (int) $section->channel_id;
        $dto->sort_order = (int) $section->sort_order;
        $dto->status = (int) $section->status;
        $dto->draft_status = is_null($section->draft_status) ? null : (bool) $section->draft_status;
        $dto->draft_sort_order = is_null($section->draft_sort_order) ? null : (int) $section->draft_sort_order;
        $dto->has_draft = $this->sectionHasDraft($section);
        $dto->is_pinned = $section->type === Section::FOOTER_LINKS;
        $dto->created_at = $section->created_at?->toIso8601String();
        $dto->updated_at = $section->updated_at?->toIso8601String();

        $dto->translations = $section->translations
            ->map(fn ($translation) => [
                'locale' => $translation->locale,
                'options' => $translation->options,
                'draftOptions' => $translation->draft_options,
            ])
            ->values()
            ->all();

        return $dto;
    }

    /**
     * Mirrors SectionRepository::hasDraft.
     */
    protected function sectionHasDraft(Section $section): bool
    {
        return ! is_null($section->draft_status)
            || ! is_null($section->draft_sort_order)
            || $section->translations->contains(fn ($translation) => ! is_null($translation->draft_options));
    }
}
