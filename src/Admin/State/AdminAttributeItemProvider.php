<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Admin\Models\AdminAttribute;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;

class AdminAttributeItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.attribute.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return Attribute::with(['translations', 'options.translations'])->find($id);
    }

    /**
     * Public alias so processors (AdminAttributeProcessor, AdminAttributeOptionProcessor)
     * can reuse the detail DTO mapping for their success responses.
     */
    public function mapToDtoPublic(object $attribute): AdminAttribute
    {
        return $this->mapToDto($attribute);
    }

    protected function mapToDto(object $attribute): AdminAttribute
    {
        /** @var Attribute $attribute */
        $dto = new AdminAttribute;

        // Listing fields (mirror AdminAttributeCollectionProvider->mapRow logic).
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

        // Detail-only fields.
        $dto->validation = $attribute->validation;
        $dto->defaultValue = $attribute->default_value;

        $dto->translations = $attribute->translations->map(fn ($t) => [
            'locale' => $t->locale,
            'name'   => $t->name,
        ])->values()->all();

        $dto->options = $attribute->options->map(function ($o) {
            $swatchUrl = null;
            try {
                $swatchUrl = $o->getSwatchValueUrlAttribute();
            } catch (\Throwable) {
                // Some swatch types (e.g. text/color) don't have URL accessors; ignore.
            }

            return [
                'id'             => (int) $o->id,
                'adminName'      => $o->admin_name,
                'sortOrder'      => (int) ($o->sort_order ?? 0),
                'swatchValue'    => $o->swatch_value,
                'swatchValueUrl' => $swatchUrl,
                'translations'   => $o->translations->map(fn ($t) => [
                    'locale' => $t->locale,
                    'label'  => $t->label,
                ])->values()->all(),
            ];
        })->values()->all();

        return $dto;
    }
}
