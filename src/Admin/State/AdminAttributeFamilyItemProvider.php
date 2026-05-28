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

        // Embed attribute groups with their attributes as plain associative arrays.
        // attribute_groups are ordered by position (the hasMany relationship does ->orderBy('position')).
        // custom_attributes (attributes) are ordered by pivot_position via belongsToMany pivot.
        $dto->attributeGroups = $family->attribute_groups->map(function ($group) {
            $attributes = $group->custom_attributes->map(fn ($attr) => [
                'id'         => (int) $attr->id,
                'code'       => $attr->code,
                'type'       => $attr->type,
                'isRequired' => (int) $attr->is_required,
                'column'     => (int) ($attr->pivot->position ?? 0), // pivot only has position
                'position'   => (int) ($attr->pivot->position ?? 0),
            ])->values()->all();

            return [
                'id'         => (int) $group->id,
                'code'       => $group->code,
                'name'       => $group->name,
                'column'     => (int) $group->column,
                'position'   => (int) $group->position,
                'attributes' => $attributes,
            ];
        })->values()->all();

        return $dto;
    }
}
