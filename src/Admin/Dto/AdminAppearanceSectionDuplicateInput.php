<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAppearanceSectionDuplicateInput
{
    /**
     * The section id.
     */
    #[ApiProperty(description: 'Section being copied')]
    #[Groups(['mutation'])]
    public ?int $section_id = null;
}
