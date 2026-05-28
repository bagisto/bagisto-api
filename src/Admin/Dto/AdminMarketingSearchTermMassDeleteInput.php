<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingSearchTermMassDeleteInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of search term IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
