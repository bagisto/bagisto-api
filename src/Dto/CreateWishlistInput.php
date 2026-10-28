<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CreateWishlistInput
{
    /**
     * Product ID to add to wishlist.
     */
    #[ApiProperty(description: 'The ID of the product to add to wishlist')]
    #[Groups(['mutation'])]
    public ?int $product_id = null;

    /**
     * Get product id.
     */
    public function getProduct_id(): ?int
    {
        return $this->product_id;
    }

    /**
     * Set product id.
     */
    public function setProduct_id(?int $product_id): void
    {
        $this->product_id = $product_id;
    }
}
