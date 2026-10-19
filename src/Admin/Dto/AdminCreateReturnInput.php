<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCreateReturnInput
{
    /**
     * The order id.
     */
    #[ApiProperty(description: 'Order id the returned item belongs to')]
    #[Groups(['mutation'])]
    public ?int $order_id = null;

    /**
     * The order item id.
     */
    #[ApiProperty(description: 'Order item id to return')]
    #[Groups(['mutation'])]
    public ?int $order_item_id = null;

    /**
     * The rma qty.
     */
    #[ApiProperty(description: 'Quantity to return; capped server-side by the returnable quantity')]
    #[Groups(['mutation'])]
    public ?int $rma_qty = null;

    /**
     * The resolution type.
     */
    #[ApiProperty(description: 'return | cancel_items')]
    #[Groups(['mutation'])]
    public ?string $resolution_type = null;

    /**
     * The rma reason id.
     */
    #[ApiProperty(description: 'Reason id')]
    #[Groups(['mutation'])]
    public ?int $rma_reason_id = null;

    /**
     * The information.
     */
    #[ApiProperty(description: 'Free-text detail about the return')]
    #[Groups(['mutation'])]
    public ?string $information = null;

    /**
     * The package condition.
     */
    #[ApiProperty(description: 'Condition of the package')]
    #[Groups(['mutation'])]
    public ?string $package_condition = null;

    /**
     * The variant.
     */
    #[ApiProperty(description: 'Variant product id for configurable products')]
    #[Groups(['mutation'])]
    public ?int $variant = null;

    /**
     * Get order id.
     */
    public function getOrder_id(): ?int
    {
        return $this->order_id;
    }

    /**
     * Set order id.
     */
    public function setOrder_id(?int $value): void
    {
        $this->order_id = $value;
    }

    /**
     * Get order item id.
     */
    public function getOrder_item_id(): ?int
    {
        return $this->order_item_id;
    }

    /**
     * Set order item id.
     */
    public function setOrder_item_id(?int $value): void
    {
        $this->order_item_id = $value;
    }

    /**
     * Get rma qty.
     */
    public function getRma_qty(): ?int
    {
        return $this->rma_qty;
    }

    /**
     * Set rma qty.
     */
    public function setRma_qty(?int $value): void
    {
        $this->rma_qty = $value;
    }

    /**
     * Get resolution type.
     */
    public function getResolution_type(): ?string
    {
        return $this->resolution_type;
    }

    /**
     * Set resolution type.
     */
    public function setResolution_type(?string $value): void
    {
        $this->resolution_type = $value;
    }

    /**
     * Get rma reason id.
     */
    public function getRma_reason_id(): ?int
    {
        return $this->rma_reason_id;
    }

    /**
     * Set rma reason id.
     */
    public function setRma_reason_id(?int $value): void
    {
        $this->rma_reason_id = $value;
    }

    /**
     * Get information.
     */
    public function getInformation(): ?string
    {
        return $this->information;
    }

    /**
     * Set information.
     */
    public function setInformation(?string $value): void
    {
        $this->information = $value;
    }

    /**
     * Get package condition.
     */
    public function getPackage_condition(): ?string
    {
        return $this->package_condition;
    }

    /**
     * Set package condition.
     */
    public function setPackage_condition(?string $value): void
    {
        $this->package_condition = $value;
    }

    /**
     * Get variant.
     */
    public function getVariant(): ?int
    {
        return $this->variant;
    }

    /**
     * Set variant.
     */
    public function setVariant(?int $value): void
    {
        $this->variant = $value;
    }
}
