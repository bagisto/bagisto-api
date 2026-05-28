<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/roles.
 *
 * Mirrors RoleController::store validation:
 *   - name: required
 *   - description: required
 *   - permission_type: required, in:all,custom
 *   - permissions: required when permission_type=custom
 */
class AdminSettingsRoleCreateInput
{
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
