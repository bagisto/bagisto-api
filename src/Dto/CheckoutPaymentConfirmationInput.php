<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CheckoutPaymentConfirmationInput
{
    #[ApiProperty(description: 'Razorpay only: razorpay_order_id from the Razorpay checkout response.')]
    #[Groups(['mutation'])]
    public ?string $razorpay_order_id = null;

    #[ApiProperty(description: 'Razorpay only: razorpay_payment_id from the Razorpay checkout response.')]
    #[Groups(['mutation'])]
    public ?string $razorpay_payment_id = null;

    #[ApiProperty(description: 'Razorpay only: razorpay_signature from the Razorpay checkout response.')]
    #[Groups(['mutation'])]
    public ?string $razorpay_signature = null;
}
