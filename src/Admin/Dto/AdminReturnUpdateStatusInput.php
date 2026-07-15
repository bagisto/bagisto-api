<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminReturnUpdateStatusInput
{
    #[ApiProperty(description: 'Return (RMA) IRI, e.g. /api/admin/rma/requests/12')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Target RMA status id. 5 (received package) creates a refund; 8 (item canceled) cancels the item and restores inventory.')]
    #[Groups(['mutation'])]
    public ?int $rma_status_id = null;

    #[ApiProperty(description: 'Shipping amount to include when a refund is created (status 5). Defaults to 0.')]
    #[Groups(['mutation'])]
    public ?float $shipping = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $v): void
    {
        $this->id = $v;
    }

    public function getRma_status_id(): ?int
    {
        return $this->rma_status_id;
    }

    public function setRma_status_id(?int $v): void
    {
        $this->rma_status_id = $v;
    }

    public function getShipping(): ?float
    {
        return $this->shipping;
    }

    public function setShipping(?float $v): void
    {
        $this->shipping = $v;
    }
}
