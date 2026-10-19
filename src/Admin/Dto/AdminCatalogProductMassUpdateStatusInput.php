<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCatalogProductMassUpdateStatusInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of product IDs to update.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;

    /**
     * The value.
     */
    #[ApiProperty(description: 'New status value (0 = disabled, 1 = enabled).')]
    #[Groups(['mutation'])]
    public ?int $value = null;
}
