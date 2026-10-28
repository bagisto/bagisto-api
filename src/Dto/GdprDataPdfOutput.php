<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class GdprDataPdfOutput
{
    /**
     * The file name.
     */
    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['query'])]
    public ?string $file_name = null;

    /**
     * The mime type.
     */
    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['query'])]
    public ?string $mime_type = null;

    /**
     * The size.
     */
    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['query'])]
    public ?int $size = null;

    /**
     * The content.
     */
    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['query'])]
    public ?string $content = null;
}
