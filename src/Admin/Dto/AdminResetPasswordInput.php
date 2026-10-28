<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

class AdminResetPasswordInput
{
    use AcceptsCamelCaseWrites;

    /**
     * The reset token from the emailed link.
     */
    #[ApiProperty(description: 'The token from the reset link emailed by forgot-password.', example: '6f1c0e...')]
    public ?string $token = null;

    /**
     * The admin's email address.
     */
    #[ApiProperty(description: 'Admin email address.', example: 'admin@example.com')]
    public ?string $email = null;

    /**
     * The new password.
     */
    #[ApiProperty(description: 'New password (min 6).', example: 'new-secret')]
    public ?string $password = null;

    /**
     * The new password again.
     */
    #[ApiProperty(description: 'New password again.', example: 'new-secret')]
    public ?string $password_confirmation = null;
}
