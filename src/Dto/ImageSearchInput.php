<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class ImageSearchInput
{
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public string $image = '';

    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public ?string $file_name = null;

    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public ?string $mime_type = null;
}
