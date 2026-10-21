<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCategoryMassDeleteInput
{
    /**
     * The ids of the categories to delete.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of category IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
