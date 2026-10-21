<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

class AdminAccountUpdateInput
{
    use AcceptsCamelCaseWrites;

    /**
     * The admin's name.
     */
    #[ApiProperty(description: 'Name.', example: 'Example Admin')]
    public ?string $name = null;

    /**
     * The admin's email address.
     */
    #[ApiProperty(description: 'Email address; left unchanged when omitted.', example: 'admin@example.com')]
    public ?string $email = null;

    /**
     * The new password, when it changes.
     */
    #[ApiProperty(description: 'New password (min 6); omit to keep the current one.', example: 'new-secret')]
    public ?string $password = null;

    /**
     * The new password again.
     */
    #[ApiProperty(description: 'New password again, required with password.', example: 'new-secret')]
    public ?string $password_confirmation = null;

    /**
     * The current password, which every update needs.
     */
    #[ApiProperty(description: 'Current password; required.', example: 'admin123')]
    public ?string $current_password = null;
}
