<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class MoveToWishlistInput
{
    /**
     * The ids.
     */
    #[Groups(['mutation'])]
    public ?array $ids = null;

    /**
     * The qty.
     */
    #[Groups(['mutation'])]
    public ?array $qty = null;

    /**
     * Get ids.
     */
    public function getIds(): ?array
    {
        return $this->ids;
    }

    /**
     * Set ids.
     */
    public function setIds(?array $ids): void
    {
        $this->ids = $ids;
    }

    /**
     * Get qty.
     */
    public function getQty(): ?array
    {
        return $this->qty;
    }

    /**
     * Set qty.
     */
    public function setQty(?array $qty): void
    {
        $this->qty = $qty;
    }
}
