<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class VerifyAccountInput
{
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public string $token;
}
