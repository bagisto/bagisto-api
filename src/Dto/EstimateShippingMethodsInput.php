<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class EstimateShippingMethodsInput
{
    /**
     * The country.
     */
    #[Groups(['mutation'])]
    public ?string $country = null;

    /**
     * The state.
     */
    #[Groups(['mutation'])]
    public ?string $state = null;

    /**
     * The postcode.
     */
    #[Groups(['mutation'])]
    public ?string $postcode = null;

    /**
     * The shipping method.
     */
    #[Groups(['mutation'])]
    public ?string $shipping_method = null;

    /**
     * Get country.
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Set country.
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    /**
     * Get state.
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * Set state.
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    /**
     * Get postcode.
     */
    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    /**
     * Set postcode.
     */
    public function setPostcode(?string $postcode): void
    {
        $this->postcode = $postcode;
    }

    /**
     * Get shipping method.
     */
    public function getShipping_method(): ?string
    {
        return $this->shipping_method;
    }

    /**
     * Set shipping method.
     */
    public function setShipping_method(?string $shipping_method): void
    {
        $this->shipping_method = $shipping_method;
    }
}
