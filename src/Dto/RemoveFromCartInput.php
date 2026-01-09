<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class RemoveFromCartInput
{
    #[Groups(['mutation'])]
    public ?int $cart_item_id = null;

    public function getCart_item_id(): ?int
    {
        return $this->cart_item_id;
    }

    public function setCart_item_id(?int $cart_item_id): void
    {
        $this->cart_item_id = $cart_item_id;
    }
}
