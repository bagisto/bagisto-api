<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/themes/mass-delete.
 */
class AdminSettingsThemeMassDeleteInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of theme customization IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
