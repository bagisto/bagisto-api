<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

class AdminForgotPasswordInput
{
    use AcceptsCamelCaseWrites;

    /**
     * The email address to send the reset link to.
     */
    #[ApiProperty(description: 'Admin email address.', example: 'admin@example.com')]
    public ?string $email = null;
}
