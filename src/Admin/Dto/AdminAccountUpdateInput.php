<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for updating the authenticated admin's own account.
 */
class AdminAccountUpdateInput
{
    #[ApiProperty(description: 'Display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Login email, unique across admins.')]
    #[Groups(['mutation'])]
    public ?string $email = null;

    #[ApiProperty(description: 'New password, at least 6 characters. Omit to keep the current one.')]
    #[Groups(['mutation'])]
    public ?string $password = null;

    #[ApiProperty(description: 'Must match password when a new password is sent.')]
    #[Groups(['mutation'])]
    public ?string $password_confirmation = null;

    #[ApiProperty(description: 'The admin\'s current password. Required for every change.')]
    #[Groups(['mutation'])]
    public ?string $current_password = null;
}
