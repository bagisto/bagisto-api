<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

/**
 * DTO for deleting compare items
 *
 * Defines the input structure for deleting items from compare list
 */
class DeleteCompareItemInput
{
    /**
     * Compare item ID to delete
     */
    #[ApiProperty(description: 'The ID of the compare item to delete')]
    public ?string $id = null;
}
