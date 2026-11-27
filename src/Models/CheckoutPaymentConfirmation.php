<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CheckoutPaymentConfirmationInput;
use Webkul\BagistoApi\State\CheckoutPaymentConfirmationProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CheckoutPaymentConfirmation',
    operations: [
        new Post(
            uriTemplate: '/checkout-payments/confirm',
            input: CheckoutPaymentConfirmationInput::class,
            output: self::class,
            processor: CheckoutPaymentConfirmationProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
                'skip_null_values' => false,
            ],
            openapi: new Operation(
                tags: ['Checkout'],
                summary: 'Confirm a payment and return its order',
                description: 'Returns the order of a payment started with `POST /api/shop/checkout-payments/initiate`, for the cart of the Bearer token. Stripe, PhonePe, PayGlocal, PayU and PayPal Standard: the gateway\'s own return places the order, so once the WebView reaches `successUrl` or `failureUrl` send `reference`; the answer carries the order with `paymentStatus: success`, or `paymentStatus: pending` while no order exists yet. A PayPal Standard order is placed when PayPal returns the customer and stays `paymentStatus: pending` until PayPal\'s IPN records the payment, so repeat the call until it reports success. Stripe also accepts `sessionId`, which verifies the session and places the order when neither the webhook nor the return did. Razorpay: send the three values the Razorpay checkout returns; the signature, the Razorpay order and the payment are verified against Razorpay before the order is placed paid and invoiced. PayPal Smart Button: send `paypalOrderId` once the customer approved the payment; the PayPal order must belong to this cart and total, it is captured, and the order is placed paid and invoiced. Nothing the app reports is trusted. Repeatable: once an order exists for the cart, every call returns it and no second order is placed.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'reference' => ['type' => 'string', 'example' => '12'],
                                    'sessionId' => ['type' => 'string', 'example' => 'cs_test_123'],
                                    'razorpayOrderId' => ['type' => 'string', 'example' => 'order_P9xYz'],
                                    'razorpayPaymentId' => ['type' => 'string', 'example' => 'pay_P9xZa'],
                                    'razorpaySignature' => ['type' => 'string', 'example' => '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08'],
                                    'paypalOrderId' => ['type' => 'string', 'example' => '5O190127TN364715T'],
                                ],
                            ],

                            'examples' => [
                                'reference' => [
                                    'summary' => 'Stripe, PhonePe, PayGlocal, PayU, PayPal Standard',
                                    'value' => ['reference' => '12'],
                                ],
                                'razorpay' => [
                                    'summary' => 'Razorpay',
                                    'value' => [
                                        'razorpayOrderId' => 'order_P9xYz',
                                        'razorpayPaymentId' => 'pay_P9xZa',
                                        'razorpaySignature' => '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08',
                                    ],
                                ],
                                'paypal_smart_button' => [
                                    'summary' => 'PayPal Smart Button',
                                    'value' => ['paypalOrderId' => '5O190127TN364715T'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    201 => new Response(
                        description: 'The order of the payment, or no order yet.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'examples' => [
                                    'success' => [
                                        'summary' => 'Paid and placed',
                                        'value' => [
                                            'success' => true,
                                            'cartId' => 12,
                                            'orderId' => '42',
                                            'orderIncrementId' => '42',
                                            'status' => 'processing',
                                            'paymentStatus' => 'success',
                                            'message' => 'Payment confirmed and order placed.',
                                        ],
                                    ],
                                    'pending' => [
                                        'summary' => 'No order yet',
                                        'value' => [
                                            'success' => false,
                                            'cartId' => 12,
                                            'orderId' => null,
                                            'orderIncrementId' => null,
                                            'status' => null,
                                            'paymentStatus' => 'pending',
                                            'message' => 'The payment has not been completed yet. No order has been placed.',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    401 => new Response(
                        description: 'A bearer cart token is required.',
                    ),
                    404 => new Response(
                        description: 'The reference is not a cart of the bearer token, or the bearer has no active cart.',
                    ),
                    422 => new Response(
                        description: 'The payment method needs no confirmation, a required value is missing, or the payment could not be verified.',
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CheckoutPaymentConfirmationInput::class,
            output: self::class,
            processor: CheckoutPaymentConfirmationProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Return the order of a payment started with createCheckoutPaymentSession, placing it after a verified Razorpay or PayPal Smart Button payment.',
        ),
    ],
)]
class CheckoutPaymentConfirmation
{
    /**
     * Whether the payment is confirmed and the order exists.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public bool $success = false;

    /**
     * The cart that was paid for.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?int $cartId = null;

    /**
     * The order placed for the cart.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $orderId = null;

    /**
     * The order number the storefront shows.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $orderIncrementId = null;

    /**
     * The order status.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $status = null;

    /**
     * Whether the gateway reported the payment: success or pending.
     */
    #[ApiProperty(readable: true, writable: false, schema: ['type' => 'string', 'enum' => ['success', 'pending']])]
    #[Groups(['mutation'])]
    public ?string $paymentStatus = null;

    /**
     * Human-readable result of the confirmation.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public string $message = '';
}
