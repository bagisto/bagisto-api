<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CheckoutPaymentSessionInput
{
    /**
     * The payment method the client expects the cart to pay with.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Optional. When sent, it must match the payment method saved on the cart.')]
    #[SerializedName('paymentMethod')]
    public ?string $paymentMethod = null;
}
