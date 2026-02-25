<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

/**
 * DTO for deleting wishlist items
 *
 * Defines the input structure for deleting items from wishlist
 */
class DeleteWishlistInput
{
    /**
     * Wishlist item ID to delete
     */
    #[ApiProperty(description: 'The ID of the wishlist item to delete')]
    public ?string $id = null;
}
