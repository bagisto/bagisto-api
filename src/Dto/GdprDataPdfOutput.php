<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class GdprDataPdfOutput
{
    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['query'])]
    public ?string $file_name = null;

    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['query'])]
    public ?string $mime_type = null;

    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['query'])]
    public ?int $size = null;

    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['query'])]
    public ?string $content = null;
}
