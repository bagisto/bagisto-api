<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class UpdateCartItemInput
{
    /**
     * The items.
     */
    #[Groups(['mutation'])]
    public ?array $items = null;

    /**
     * Get items.
     */
    public function getItems(): ?array
    {
        return $this->items;
    }

    /**
     * Set items.
     */
    public function setItems(?array $items): void
    {
        $this->items = $items;
    }
}
