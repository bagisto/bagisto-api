<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class AddToCartInput
{
    /**
     * The product id.
     */
    #[Groups(['mutation'])]
    public ?int $product_id = null;

    /**
     * The quantity.
     */
    #[Groups(['mutation'])]
    public ?int $quantity = null;

    /**
     * The is buy now.
     */
    #[Groups(['mutation'])]
    public ?int $is_buy_now = null;

    /**
     * The options.
     */
    #[Groups(['mutation'])]
    public ?array $options = null;

    /**
     * Get product id.
     */
    public function getProduct_id(): ?int
    {
        return $this->product_id;
    }

    /**
     * Set product id.
     */
    public function setProduct_id(?int $product_id): void
    {
        $this->product_id = $product_id;
    }

    /**
     * Get quantity.
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * Set quantity.
     */
    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * Get is buy now.
     */
    public function getIs_buy_now(): ?int
    {
        return $this->is_buy_now;
    }

    /**
     * Set is buy now.
     */
    public function setIs_buy_now(?int $is_buy_now): void
    {
        $this->is_buy_now = $is_buy_now;
    }

    /**
     * Get options.
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * Set options.
     */
    public function setOptions(?array $options): void
    {
        $this->options = $options;
    }
}
