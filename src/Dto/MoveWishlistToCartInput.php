<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

class MoveWishlistToCartInput
{
    /**
     * Wishlist item ID to move to cart.
     */
    #[ApiProperty(description: 'The numeric ID of the wishlist item to move to cart')]
    public ?int $wishlistItemId = null;

    /**
     * Quantity of the item to add to cart.
     */
    #[ApiProperty(description: 'Quantity of the item to add to cart')]
    public int $quantity = 1;
}
