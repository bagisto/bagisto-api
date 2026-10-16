<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\Attribute\Models\AttributeFamily;
use Webkul\BagistoApi\Admin\Models\AdminAttributeFamily;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;

class AdminAttributeFamilyItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.family.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return AttributeFamily::with([
            'attribute_groups.custom_attributes',
        ])->find($id);
    }

    protected function mapToDto(object $family): AdminAttributeFamily
    {
        /** @var AttributeFamily $family */
        $dto = new AdminAttributeFamily;

        $dto->id = (int) $family->id;
        $dto->code = $family->code;
        $dto->name = $family->name;

        $dto->attributeGroups = $family->attribute_groups->map(function ($group) {
            $attributes = $group->custom_attributes->map(fn ($attribute) => [
                'id' => (int) $attribute->id,
                'code' => $attribute->code,
                'type' => $attribute->type,
                'isRequired' => (int) $attribute->is_required,
                'column' => (int) ($attribute->pivot->position ?? 0),
                'position' => (int) ($attribute->pivot->position ?? 0),
            ])->values()->all();

            return [
                'id' => (int) $group->id,
                'code' => $group->code,
                'name' => $group->name,
                'column' => (int) $group->column,
                'position' => (int) $group->position,
                'attributes' => $attributes,
            ];
        })->values()->all();

        return $dto;
    }
}
