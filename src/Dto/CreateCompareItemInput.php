<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

/**
 * DTO for creating a compare item
 *
 * Defines the input structure for the createCompareItem mutation
 * Customer ID is automatically determined from the authenticated user
 */
class CreateCompareItemInput
{
    /**
     * Product ID to add to comparison
     */
    #[ApiProperty(description: 'The ID of the product to add to comparison')]
    public ?int $productId = null;
}
