<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCmsPageMassDeleteInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of CMS page IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
