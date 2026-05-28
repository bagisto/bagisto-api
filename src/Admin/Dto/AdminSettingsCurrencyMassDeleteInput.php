<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/currencies/mass-delete.
 */
class AdminSettingsCurrencyMassDeleteInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of currency IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
