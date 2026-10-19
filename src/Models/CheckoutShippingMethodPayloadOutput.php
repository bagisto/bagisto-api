<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;

class CheckoutShippingMethodPayloadOutput
{
    /**
     * The success.
     */
    #[ApiProperty(writable: false, readable: true)]
    public bool $success = false;

    /**
     * The message.
     */
    #[ApiProperty(writable: false, readable: true)]
    public string $message = '';

    /**
     * The cart token.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $cartToken = null;

    /**
     * The shipping method.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingMethod = null;
}
