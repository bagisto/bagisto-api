<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for Magic AI image generation.
 */
class AdminMagicAiImageInput
{
    #[ApiProperty(description: 'Prompt describing the image to generate.')]
    #[Groups(['mutation'])]
    public ?string $prompt = null;

    #[ApiProperty(description: 'AI image model to use. Omit to use the default model.')]
    #[Groups(['mutation'])]
    public ?string $model = null;

    #[ApiProperty(description: 'Number of images, 1 to 10.')]
    #[Groups(['mutation'])]
    public ?int $n = null;

    #[ApiProperty(description: 'Aspect ratio: 1:1, 2:3 or 3:2.')]
    #[Groups(['mutation'])]
    public ?string $size = null;

    #[ApiProperty(description: 'Quality: high, medium or low.')]
    #[Groups(['mutation'])]
    public ?string $quality = null;
}
