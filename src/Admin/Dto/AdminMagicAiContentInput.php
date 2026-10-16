<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for Magic AI text generation.
 */
class AdminMagicAiContentInput
{
    #[ApiProperty(description: 'Prompt describing the text to generate.')]
    #[Groups(['mutation'])]
    public ?string $prompt = null;

    #[ApiProperty(description: 'AI model to use, e.g. gpt-4o-mini. Omit to use the default model.')]
    #[Groups(['mutation'])]
    public ?string $model = null;
}
