<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingSearchTermMassDeleteInput
{
    /**
     * The ids of the search terms to delete.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of search term IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
