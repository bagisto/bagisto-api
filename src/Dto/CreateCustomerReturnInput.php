<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CreateCustomerReturnInput
{
    #[ApiProperty(description: 'Order id the returned item belongs to')]
    #[Groups(['mutation'])]
    public ?int $order_id = null;

    #[ApiProperty(description: 'Order item id to return')]
    #[Groups(['mutation'])]
    public ?int $order_item_id = null;

    #[ApiProperty(description: 'Quantity to return; capped server-side by the returnable quantity')]
    #[Groups(['mutation'])]
    public ?int $rma_qty = null;

    #[ApiProperty(description: 'return | cancel_items')]
    #[Groups(['mutation'])]
    public ?string $resolution_type = null;

    #[ApiProperty(description: 'Reason id (from /return-reasons for this resolution type)')]
    #[Groups(['mutation'])]
    public ?int $rma_reason_id = null;

    #[ApiProperty(description: 'Free-text detail about the return')]
    #[Groups(['mutation'])]
    public ?string $information = null;

    #[ApiProperty(description: 'Condition of the package')]
    #[Groups(['mutation'])]
    public ?string $package_condition = null;

    #[ApiProperty(description: 'Variant product id for configurable products')]
    #[Groups(['mutation'])]
    public ?int $variant = null;

    #[ApiProperty(description: 'Must be true to accept the return policy')]
    #[Groups(['mutation'])]
    public ?bool $agreement = null;

    public function getOrder_id(): ?int
    {
        return $this->order_id;
    }

    public function setOrder_id(?int $v): void
    {
        $this->order_id = $v;
    }

    public function getOrder_item_id(): ?int
    {
        return $this->order_item_id;
    }

    public function setOrder_item_id(?int $v): void
    {
        $this->order_item_id = $v;
    }

    public function getRma_qty(): ?int
    {
        return $this->rma_qty;
    }

    public function setRma_qty(?int $v): void
    {
        $this->rma_qty = $v;
    }

    public function getResolution_type(): ?string
    {
        return $this->resolution_type;
    }

    public function setResolution_type(?string $v): void
    {
        $this->resolution_type = $v;
    }

    public function getRma_reason_id(): ?int
    {
        return $this->rma_reason_id;
    }

    public function setRma_reason_id(?int $v): void
    {
        $this->rma_reason_id = $v;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $v): void
    {
        $this->information = $v;
    }

    public function getPackage_condition(): ?string
    {
        return $this->package_condition;
    }

    public function setPackage_condition(?string $v): void
    {
        $this->package_condition = $v;
    }

    public function getVariant(): ?int
    {
        return $this->variant;
    }

    public function setVariant(?int $v): void
    {
        $this->variant = $v;
    }

    public function getAgreement(): ?bool
    {
        return $this->agreement;
    }

    public function setAgreement(?bool $v): void
    {
        $this->agreement = $v;
    }
}
