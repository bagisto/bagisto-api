<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CheckoutPaymentInitiationInput
{
    #[ApiProperty(description: 'Optional. When sent, it must match the payment method saved on the cart.')]
    #[Groups(['mutation'])]
    public ?string $payment_method = null;
}
