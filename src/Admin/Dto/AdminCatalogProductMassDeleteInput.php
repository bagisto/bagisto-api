<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCatalogProductMassDeleteInput
{
    /**
     * The ids of the products to delete.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of product IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
