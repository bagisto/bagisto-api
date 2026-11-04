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

    #[ApiProperty(description: 'PayPal Smart Button only: the PayPal order id the customer approved (the orderId returned by checkout-payments/initiate).')]
    #[Groups(['mutation'])]
    public ?string $paypal_order_id = null;

    #[ApiProperty(description: 'The reference returned by checkout-payments/initiate. Send it after a redirect payment (Stripe, PhonePe, PayU, PayGlocal) to read the order the gateway callback placed; with any method it returns the existing order instead of placing a second one.')]
    #[Groups(['mutation'])]
    public ?string $reference = null;
}
