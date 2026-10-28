<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAttributeFamilyUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Family IRI, e.g. /api/admin/catalog/families/1.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The code.
     */
    #[ApiProperty(description: 'Family code (must be unique among other families).')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The name.
     */
    #[ApiProperty(description: 'Family display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * Attribute groups map. Existing groups keyed by numeric id; new groups keyed
     * with a string `group_*`. See repository ::update() for full semantics.
     *
     * @var array<string|int, mixed>|null
     */
    #[ApiProperty(description: 'Attribute groups map (numeric keys = existing, group_* keys = new). Omitted ids are deleted.')]
    #[Groups(['mutation'])]
    public ?array $attribute_groups = null;
}
