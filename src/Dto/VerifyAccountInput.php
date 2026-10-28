<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class VerifyAccountInput
{
    /**
     * The token.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public string $token;
}
