<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class ImageSearchInput
{
    /**
     * The image.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public string $image = '';

    /**
     * The file name.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public ?string $file_name = null;

    /**
     * The mime type.
     */
    #[ApiProperty(writable: true, readable: false)]
    #[Groups(['mutation'])]
    public ?string $mime_type = null;
}
