<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * CheckoutAddressPayload - Response DTO for CreateCheckoutAddress mutation
 *
 * Wraps the created address and cart information in a payload structure
 * that matches the expected GraphQL response format
 */
class CheckoutAddressPayload
{
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'The created or updated cart address')]
    public ?CheckoutAddressOutput $checkoutAddress = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Current cart state')]
    public ?CartData $cart = null;

    public function __construct(
        ?CheckoutAddressOutput $checkoutAddress = null,
        ?CartData $cart = null
    ) {
        $this->checkoutAddress = $checkoutAddress;
        $this->cart = $cart;
    }
}
