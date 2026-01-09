<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class AddToCartInput
{
    #[Groups(['mutation'])]
    public ?int $product_id = null;

    #[Groups(['mutation'])]
    public ?int $quantity = null;

    #[Groups(['mutation'])]
    public ?int $is_buy_now = null;

    #[Groups(['mutation'])]
    public ?array $options = null;

    public function getProduct_id(): ?int
    {
        return $this->product_id;
    }

    public function setProduct_id(?int $product_id): void
    {
        $this->product_id = $product_id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getIs_buy_now(): ?int
    {
        return $this->is_buy_now;
    }

    public function setIs_buy_now(?int $is_buy_now): void
    {
        $this->is_buy_now = $is_buy_now;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): void
    {
        $this->options = $options;
    }
}
