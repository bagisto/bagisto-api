<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingSearchSynonymMassDeleteInput
{
    /**
     * The ids of the search synonyms to delete.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of search synonym IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
