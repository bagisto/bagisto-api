<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/exchange-rates/mass-delete.
 */
class AdminSettingsExchangeRateMassDeleteInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of exchange-rate IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
