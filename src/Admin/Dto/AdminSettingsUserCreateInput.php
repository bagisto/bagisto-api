<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/users.
 *
 * Mirrors Bagisto admin UserController::store validation:
 *   - name: required
 *   - email: required, unique, email format
 *   - password: required, min 6 (hashed via Hash::make())
 *   - role_id: required, exists
 *   - status: optional (0/1, default 1)
 *   - image: deferred — accepts path string only in v1 (no upload)
 */
class AdminSettingsUserCreateInput
{
    #[ApiProperty(description: 'Admin display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Admin login email (unique).')]
    #[Groups(['mutation'])]
    public ?string $email = null;

    #[ApiProperty(description: 'Admin login password (min 6 chars). Hashed before persistence.')]
    #[Groups(['mutation'])]
    public ?string $password = null;

    #[ApiProperty(description: 'Role ID (FK to roles.id).')]
    #[Groups(['mutation'])]
    public ?int $role_id = null;

    #[ApiProperty(description: 'Account status (0 disabled, 1 enabled). Defaults to 1.')]
    #[Groups(['mutation'])]
    public ?int $status = null;

    #[ApiProperty(description: 'Avatar image path string (file upload deferred in v1).')]
    #[Groups(['mutation'])]
    public ?string $image = null;
}
