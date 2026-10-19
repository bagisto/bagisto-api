<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CustomizableOptionFileInput
{
    /**
     * The product id.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public int $product_id = 0;

    /**
     * The option id.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public int $option_id = 0;

    /**
     * The file.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public string $file = '';

    /**
     * The file name.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public ?string $file_name = null;
}
