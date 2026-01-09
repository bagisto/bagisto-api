<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class EstimateShippingMethodsInput
{
    #[Groups(['mutation'])]
    public ?string $country = null;

    #[Groups(['mutation'])]
    public ?string $state = null;

    #[Groups(['mutation'])]
    public ?string $postcode = null;

    #[Groups(['mutation'])]
    public ?string $shipping_method = null;

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): void
    {
        $this->postcode = $postcode;
    }

    public function getShipping_method(): ?string
    {
        return $this->shipping_method;
    }

    public function setShipping_method(?string $shipping_method): void
    {
        $this->shipping_method = $shipping_method;
    }
}
