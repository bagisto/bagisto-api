<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAppearanceSectionStatusInput
{
    /**
     * The section id.
     */
    #[ApiProperty(description: 'Section whose on/off state is being staged')]
    #[Groups(['mutation'])]
    public ?int $section_id = null;

    /**
     * The status.
     */
    #[ApiProperty(description: 'Whether the section should be shown once published')]
    #[Groups(['mutation'])]
    public ?bool $status = null;
}
