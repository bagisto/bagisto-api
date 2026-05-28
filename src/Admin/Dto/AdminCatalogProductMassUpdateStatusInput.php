<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/catalog/products/mass-update-status.
 */
class AdminCatalogProductMassUpdateStatusInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of product IDs to update.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;

    #[ApiProperty(description: 'New status value (0 = disabled, 1 = enabled).')]
    #[Groups(['mutation'])]
    public ?int $value = null;
}
