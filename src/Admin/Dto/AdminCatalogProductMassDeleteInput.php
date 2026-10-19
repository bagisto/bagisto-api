<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCatalogProductMassDeleteInput
{
    /** @var int[]|null */
    #[ApiProperty(description: 'Array of product IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
