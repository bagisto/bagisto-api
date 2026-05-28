<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/themes/mass-update-status.
 */
class AdminSettingsThemeMassUpdateStatusInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of theme customization IDs.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;

    #[ApiProperty(description: 'New status value (0 or 1).')]
    #[Groups(['mutation'])]
    public ?int $value = null;
}
