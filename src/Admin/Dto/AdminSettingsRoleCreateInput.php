<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsRoleCreateInput
{
    /**
     * The name.
     */
    #[ApiProperty(description: 'Display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty(description: 'Description.')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    /**
     * The permission type.
     */
    #[ApiProperty(description: 'Permission type. One of: all, custom.')]
    #[Groups(['mutation'])]
    public ?string $permission_type = null;

    /** @var array<int, string>|null */
    #[ApiProperty(description: 'Permission keys (required when permission_type=custom).')]
    #[Groups(['mutation'])]
    public ?array $permissions = null;
}
