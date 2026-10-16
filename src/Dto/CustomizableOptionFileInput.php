<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CustomizableOptionFileInput
{
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public int $product_id = 0;

    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public int $option_id = 0;

    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public string $file = '';

    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public ?string $file_name = null;
}
