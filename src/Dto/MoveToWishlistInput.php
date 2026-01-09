<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class MoveToWishlistInput
{
    #[Groups(['mutation'])]
    public ?array $ids = null;

    #[Groups(['mutation'])]
    public ?array $qty = null;

    public function getIds(): ?array
    {
        return $this->ids;
    }

    public function setIds(?array $ids): void
    {
        $this->ids = $ids;
    }

    public function getQty(): ?array
    {
        return $this->qty;
    }

    public function setQty(?array $qty): void
    {
        $this->qty = $qty;
    }
}
