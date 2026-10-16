<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class ResetPasswordInput
{
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public string $token;

    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public string $email;

    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public string $password;

    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public ?string $password_confirmation = null;
}
