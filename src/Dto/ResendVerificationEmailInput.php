<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class ResendVerificationEmailInput
{
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public string $email;
}
