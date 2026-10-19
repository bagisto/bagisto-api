<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAttributeFamilyCreateInput
{
    /**
     * The code.
     */
    #[ApiProperty(description: 'Family code — lowercase letters, digits, underscore. Must be unique.')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The name.
     */
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
