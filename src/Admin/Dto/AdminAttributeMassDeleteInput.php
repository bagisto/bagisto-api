<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/catalog/attributes/mass-delete.
 */
class AdminAttributeMassDeleteInput
{
    /**
     * Array of attribute IDs to delete.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of attribute IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
