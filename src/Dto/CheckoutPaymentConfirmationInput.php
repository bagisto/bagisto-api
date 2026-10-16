<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CheckoutPaymentConfirmationInput
{
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Stripe Checkout Session ID returned by the gateway.')]
    #[SerializedName('sessionId')]
    public ?string $sessionId = null;
}
