<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/marketing/catalog-rules/mass-delete.
 */
class AdminMarketingCatalogRuleMassDeleteInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of catalog rule IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
