<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CustomizableOptionFileOutput
{
    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['mutation'])]
    public ?string $token = null;

    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['mutation'])]
    public ?string $fileName = null;

    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['mutation'])]
    public ?int $optionId = null;
}
