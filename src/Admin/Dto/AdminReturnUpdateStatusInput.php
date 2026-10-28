<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminReturnUpdateStatusInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Return (RMA) IRI, e.g. /api/admin/rma/requests/12')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The rma status id.
     */
    #[ApiProperty(description: 'Target RMA status id. 5 (received package) creates a refund; 8 (item canceled) cancels the item and restores inventory.')]
    #[Groups(['mutation'])]
    public ?int $rma_status_id = null;

    /**
     * The shipping.
     */
    #[ApiProperty(description: 'Shipping amount to include when a refund is created (status 5). Defaults to 0.')]
    #[Groups(['mutation'])]
    public ?float $shipping = null;

    /**
     * Get id.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set id.
     */
    public function setId(?string $value): void
    {
        $this->id = $value;
    }

    /**
     * Get rma status id.
     */
    public function getRma_status_id(): ?int
    {
        return $this->rma_status_id;
    }

    /**
     * Set rma status id.
     */
    public function setRma_status_id(?int $value): void
    {
        $this->rma_status_id = $value;
    }

    /**
     * Get shipping.
     */
    public function getShipping(): ?float
    {
        return $this->shipping;
    }

    /**
     * Set shipping.
     */
    public function setShipping(?float $value): void
    {
        $this->shipping = $value;
    }
}
