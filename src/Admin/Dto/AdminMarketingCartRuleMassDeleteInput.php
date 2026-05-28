<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/marketing/cart-rules/mass-delete.
 */
class AdminMarketingCartRuleMassDeleteInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of cart rule IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
