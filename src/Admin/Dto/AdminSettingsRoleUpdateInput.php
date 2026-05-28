<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/settings/roles/{id} and the delete mutation.
 */
class AdminSettingsRoleUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/roles/2). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Description.')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    #[ApiProperty(description: 'Permission type. One of: all, custom.')]
    #[Groups(['mutation'])]
    public ?string $permission_type = null;

    /** @var array<int, string>|null */
    #[ApiProperty(description: 'Permission keys (required when permission_type=custom).')]
    #[Groups(['mutation'])]
    public ?array $permissions = null;
}
