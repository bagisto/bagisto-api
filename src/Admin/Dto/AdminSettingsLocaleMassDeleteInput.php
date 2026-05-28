<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/locales/mass-delete.
 */
class AdminSettingsLocaleMassDeleteInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of locale IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
