<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAppearanceSectionReorderInput
{
    /**
     * @var array<int, int>|null
     */
    #[ApiProperty(description: 'Section IDs in the order they should be drawn')]
    #[Groups(['mutation'])]
    public ?array $section_ids = null;
}
