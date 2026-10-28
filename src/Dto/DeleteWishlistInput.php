<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

class DeleteWishlistInput
{
    /**
     * Wishlist item ID to delete.
     */
    #[ApiProperty(description: 'The ID of the wishlist item to delete')]
    public ?string $id = null;
}
