<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

class DeleteCompareItemInput
{
    /**
     * Compare item ID to delete.
     */
    #[ApiProperty(description: 'The ID of the compare item to delete')]
    public ?string $id = null;
}
