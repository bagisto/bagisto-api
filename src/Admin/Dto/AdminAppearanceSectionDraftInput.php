<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAppearanceSectionDraftInput
{
    #[ApiProperty(description: 'Section the draft belongs to')]
    #[Groups(['mutation'])]
    public ?int $section_id = null;

    #[ApiProperty(description: 'Locale the options are written for; defaults to the channel locale')]
    #[Groups(['mutation'])]
    public ?string $locale = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ApiProperty(description: 'Staged options for the locale')]
    #[Groups(['mutation'])]
    public ?array $options = null;
}
