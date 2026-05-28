<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/catalog/categories/mass-update-status.
 */
class AdminCategoryMassUpdateStatusInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of category IDs to update.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;

    #[ApiProperty(description: 'New status value (0 = disabled, 1 = enabled).')]
    #[Groups(['mutation'])]
    public ?int $value = null;
}
