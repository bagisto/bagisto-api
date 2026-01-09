<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;

/**
 * CheckoutShippingMethodPayloadOutput - Response model for CreateCheckoutShippingMethod mutation
 *
 * Defines the structure of the GraphQL response for the checkout shipping method mutation
 */
class CheckoutShippingMethodPayloadOutput
{
    #[ApiProperty(writable: false, readable: true)]
    public bool $success = false;

    #[ApiProperty(writable: false, readable: true)]
    public string $message = '';

    #[ApiProperty(writable: false, readable: true)]
    public ?string $cartToken = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingMethod = null;
}
