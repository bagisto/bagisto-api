<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsUserUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/users/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The name.
     */
    #[ApiProperty(description: 'Admin display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The email.
     */
    #[ApiProperty(description: 'Admin login email (unique excluding self).')]
    #[Groups(['mutation'])]
    public ?string $email = null;

    /**
     * The password.
     */
    #[ApiProperty(description: 'New password (min 6). Omit to keep existing.')]
    #[Groups(['mutation'])]
    public ?string $password = null;

    /**
     * The role id.
     */
    #[ApiProperty(description: 'Role ID (FK to roles.id).')]
    #[Groups(['mutation'])]
    public ?int $role_id = null;

    /**
     * The status.
     */
    #[ApiProperty(description: 'Account status (0 disabled, 1 enabled).')]
    #[Groups(['mutation'])]
    public ?int $status = null;

    /**
     * The image.
     */
    #[ApiProperty(description: 'Avatar image path string.')]
    #[Groups(['mutation'])]
    public ?string $image = null;
}
