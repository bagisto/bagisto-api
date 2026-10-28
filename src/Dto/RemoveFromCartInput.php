<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class RemoveFromCartInput
{
    /**
     * The cart item id.
     */
    #[Groups(['mutation'])]
    public ?int $cart_item_id = null;

    /**
     * Get cart item id.
     */
    public function getCart_item_id(): ?int
    {
        return $this->cart_item_id;
    }

    /**
     * Set cart item id.
     */
    public function setCart_item_id(?int $cart_item_id): void
    {
        $this->cart_item_id = $cart_item_id;
    }
}
