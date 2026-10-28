<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CustomizableOptionFileOutput
{
    /**
     * The token.
     */
    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['mutation'])]
    public ?string $token = null;

    /**
     * The file name.
     */
    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['mutation'])]
    public ?string $fileName = null;

    /**
     * The option id.
     */
    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['mutation'])]
    public ?int $optionId = null;
}
