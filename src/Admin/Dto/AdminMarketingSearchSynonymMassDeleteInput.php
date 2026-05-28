<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/marketing/search-synonyms/mass-delete.
 */
class AdminMarketingSearchSynonymMassDeleteInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of search synonym IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
