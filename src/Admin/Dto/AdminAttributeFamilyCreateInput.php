<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/catalog/families.
 *
 * `attribute_groups` is the same nested shape AttributeFamilyRepository::create() expects:
 *   [
 *     ['code' => 'general', 'name' => 'General', 'column' => 1, 'position' => 1,
 *      'custom_attributes' => [ ['id' => 1], ['id' => 2] ]],
 *     ...
 *   ]
 */
class AdminAttributeFamilyCreateInput
{
    #[ApiProperty(description: 'Family code — lowercase letters, digits, underscore. Must be unique.')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty(description: 'Family display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * Attribute groups list. Each group: { code, name, column (1|2), position?, custom_attributes? }.
     * `custom_attributes` is an array of `{ id }` or `{ code }` for each attribute in the group.
     *
     * @var array<int, mixed>|null
     */
    #[ApiProperty(description: 'Attribute groups (each with optional custom_attributes list).')]
    #[Groups(['mutation'])]
    public ?array $attribute_groups = null;
}
