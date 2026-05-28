<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/settings/users/{id} and the GraphQL
 * update/delete mutations (delete reuses the same input — only `id` is required).
 *
 * Email uniqueness excludes self. Password is optional — when present, it is
 * re-hashed via Hash::make() and replaces the existing one.
 */
class AdminSettingsUserUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/users/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Admin display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Admin login email (unique excluding self).')]
    #[Groups(['mutation'])]
    public ?string $email = null;

    #[ApiProperty(description: 'New password (min 6). Omit to keep existing.')]
    #[Groups(['mutation'])]
    public ?string $password = null;

    #[ApiProperty(description: 'Role ID (FK to roles.id).')]
    #[Groups(['mutation'])]
    public ?int $role_id = null;

    #[ApiProperty(description: 'Account status (0 disabled, 1 enabled).')]
    #[Groups(['mutation'])]
    public ?int $status = null;

    #[ApiProperty(description: 'Avatar image path string.')]
    #[Groups(['mutation'])]
    public ?string $image = null;
}
