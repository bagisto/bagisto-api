<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CheckoutPaymentConfirmationInput
{
    /**
     * The payment reference returned when the payment was started.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'The reference returned by checkout-payments/initiate. Send it once the WebView reaches successUrl or failureUrl to read the order the gateway placed; with any method it returns the order already placed for the cart.')]
    #[SerializedName('reference')]
    public ?string $reference = null;

    /**
     * The session id.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Stripe only: the Stripe Checkout Session ID returned when the payment was started.')]
    #[SerializedName('sessionId')]
    public ?string $sessionId = null;

    /**
     * The Razorpay order id from the Razorpay checkout response.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Razorpay only: razorpay_order_id from the Razorpay checkout response.')]
    #[SerializedName('razorpayOrderId')]
    public ?string $razorpayOrderId = null;

    /**
     * The Razorpay payment id from the Razorpay checkout response.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Razorpay only: razorpay_payment_id from the Razorpay checkout response.')]
    #[SerializedName('razorpayPaymentId')]
    public ?string $razorpayPaymentId = null;

    /**
     * The Razorpay signature from the Razorpay checkout response.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Razorpay only: razorpay_signature from the Razorpay checkout response.')]
    #[SerializedName('razorpaySignature')]
    public ?string $razorpaySignature = null;

    /**
     * The PayPal order id the customer approved.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'PayPal Smart Button only: the PayPal order id the customer approved, the sdkData.orderId returned when the payment was started.')]
    #[SerializedName('paypalOrderId')]
    public ?string $paypalOrderId = null;
}
