<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class ResetPasswordInput
{
    /**
     * The token.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public string $token;

    /**
     * The email.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public string $email;

    /**
     * The password.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public string $password;

    /**
     * The password confirmation.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public ?string $password_confirmation = null;
}
