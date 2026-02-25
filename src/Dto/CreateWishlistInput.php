<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

/**
 * DTO for creating a wishlist item
 *
 * Defines the input structure for the createWishlist mutation
 * Customer ID and channel ID are automatically determined from the authenticated user and current channel
 */
class CreateWishlistInput
{
    /**
     * Product ID to add to wishlist
     */
    #[ApiProperty(description: 'The ID of the product to add to wishlist')]
    public ?int $productId = null;
}
