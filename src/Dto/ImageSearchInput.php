<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class ImageSearchInput
{
    #[ApiProperty(description: 'The image as a base64 data URI (data:image/jpeg;base64,...), max 2 MB.')]
    #[Groups(['mutation'])]
    public ?string $image = null;
}
