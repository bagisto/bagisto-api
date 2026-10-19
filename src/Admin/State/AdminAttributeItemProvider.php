<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Admin\Models\AdminAttribute;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;

class AdminAttributeItemProvider extends AbstractAdminItemProvider
{
    /**
     * Public alias so processors (AdminAttributeProcessor, AdminAttributeOptionProcessor)
     * can reuse the detail DTO mapping for their success responses.
     */
    public function mapToDtoPublic(object $attribute): AdminAttribute
    {
        return $this->mapToDto($attribute);
    }

    /**
     * Get not found lang key.
     */
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.attribute.not-found';
    }

    /**
     * Find entity.
     */
    protected function findEntity(int $id): ?object
    {
        return Attribute::with(['translations', 'options.translations'])->find($id);
    }

    /**
     * Map to dto.
     */
    protected function mapToDto(object $attribute): AdminAttribute
    {
        $dto = new AdminAttribute;

        $dto->id = (int) $attribute->id;
        $dto->code = $attribute->code;
        $dto->type = $attribute->type;
        $dto->adminName = $attribute->admin_name;
        $dto->isRequired = (int) $attribute->is_required;
        $dto->isUnique = (int) $attribute->is_unique;
        $dto->valuePerLocale = (int) $attribute->value_per_locale;
        $dto->valuePerChannel = (int) $attribute->value_per_channel;
        $dto->isFilterable = (int) $attribute->is_filterable;
        $dto->isConfigurable = (int) $attribute->is_configurable;
        $dto->isVisibleOnFront = (int) $attribute->is_visible_on_front;
        $dto->isUserDefined = (int) $attribute->is_user_defined;
        $dto->swatchType = $attribute->swatch_type;
        $dto->position = (int) ($attribute->position ?? 0);
        $dto->locale = app()->getLocale();
        $dto->createdAt = $attribute->created_at?->toIso8601String();
        $dto->updatedAt = $attribute->updated_at?->toIso8601String();

        $dto->validation = $attribute->validation;
        $dto->defaultValue = $attribute->default_value;
        $dto->isComparable = (int) $attribute->is_comparable;
        $dto->enableWysiwyg = (int) $attribute->enable_wysiwyg;
        $dto->regex = $attribute->regex;

        $dto->translations = $attribute->translations->map(fn ($translation) => [
            'locale' => $translation->locale,
            'name' => $translation->name,
        ])->values()->all();

        $dto->options = $attribute->options->map(function ($option) {
            $swatchUrl = null;
            try {
                $swatchUrl = $option->getSwatchValueUrlAttribute();
            } catch (\Throwable) {
            }

            return [
                'id' => (int) $option->id,
                'adminName' => $option->admin_name,
                'sortOrder' => (int) ($option->sort_order ?? 0),
                'swatchValue' => $option->swatch_value,
                'swatchValueUrl' => $swatchUrl,
                'translations' => $option->translations->map(fn ($translation) => [
                    'locale' => $translation->locale,
                    'label' => $translation->label,
                ])->values()->all(),
            ];
        })->values()->all();

        return $dto;
    }
}
