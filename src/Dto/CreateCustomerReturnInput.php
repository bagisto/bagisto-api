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

    #[ApiProperty(description: 'Condition of the package: open | packed')]
    #[Groups(['mutation'])]
    public ?string $package_condition = null;

    /**
     * @var array<int|string,mixed>|null
     */
    #[ApiProperty(description: 'Custom field answers keyed by field id (see /return-custom-fields)')]
    #[Groups(['mutation'])]
    public ?array $custom_attributes = null;

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

    public function setOrder_id(?int $value): void
    {
        $this->order_id = $value;
    }

    public function getOrder_item_id(): ?int
    {
        return $this->order_item_id;
    }

    public function setOrder_item_id(?int $value): void
    {
        $this->order_item_id = $value;
    }

    public function getRma_qty(): ?int
    {
        return $this->rma_qty;
    }

    public function setRma_qty(?int $value): void
    {
        $this->rma_qty = $value;
    }

    public function getResolution_type(): ?string
    {
        return $this->resolution_type;
    }

    public function setResolution_type(?string $value): void
    {
        $this->resolution_type = $value;
    }

    public function getRma_reason_id(): ?int
    {
        return $this->rma_reason_id;
    }

    public function setRma_reason_id(?int $value): void
    {
        $this->rma_reason_id = $value;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $value): void
    {
        $this->information = $value;
    }

    public function getPackage_condition(): ?string
    {
        return $this->package_condition;
    }

    public function setPackage_condition(?string $value): void
    {
        $this->package_condition = $value;
    }

    public function getCustom_attributes(): ?array
    {
        return $this->custom_attributes;
    }

    public function setCustom_attributes(?array $value): void
    {
        $this->custom_attributes = $value;
    }

    public function getVariant(): ?int
    {
        return $this->variant;
    }

    public function setVariant(?int $value): void
    {
        $this->variant = $value;
    }

    public function getAgreement(): ?bool
    {
        return $this->agreement;
    }

    public function setAgreement(?bool $value): void
    {
        $this->agreement = $value;
    }
}
