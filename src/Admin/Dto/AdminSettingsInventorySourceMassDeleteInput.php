<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/inventory-sources/mass-delete.
 */
class AdminSettingsInventorySourceMassDeleteInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of inventory-source IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
