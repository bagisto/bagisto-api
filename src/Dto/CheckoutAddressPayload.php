<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CheckoutAddressPayload
{
    /**
     * The checkout address.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'The created or updated cart address')]
    public ?CheckoutAddressOutput $checkoutAddress = null;

    /**
     * The cart.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Current cart state')]
    public ?CartData $cart = null;

    /**
     * Create a new instance.
     */
    public function __construct(
        ?CheckoutAddressOutput $checkoutAddress = null,
        ?CartData $cart = null
    ) {
        $this->checkoutAddress = $checkoutAddress;
        $this->cart = $cart;
    }
}
