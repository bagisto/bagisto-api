<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsUserCreateInput
{
    /**
     * The name.
     */
    #[ApiProperty(description: 'Admin display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The email.
     */
    #[ApiProperty(description: 'Admin login email (unique).')]
    #[Groups(['mutation'])]
    public ?string $email = null;

    /**
     * The password.
     */
    #[ApiProperty(description: 'Admin login password (min 6 chars). Hashed before persistence.')]
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
    #[ApiProperty(description: 'Account status (0 disabled, 1 enabled). Defaults to 1.')]
    #[Groups(['mutation'])]
    public ?int $status = null;

    /**
     * The image.
     */
    #[ApiProperty(description: 'Avatar image path string (file upload deferred in v1).')]
    #[Groups(['mutation'])]
    public ?string $image = null;
}
