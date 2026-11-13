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
use Webkul\BagistoApi\Dto\CheckoutPaymentSessionInput;
use Webkul\BagistoApi\State\CheckoutPaymentSessionProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CheckoutPaymentSession',
    operations: [
        new Post(
            uriTemplate: '/checkout-payments/initiate',
            input: CheckoutPaymentSessionInput::class,
            output: self::class,
            processor: CheckoutPaymentSessionProcessor::class,
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
                summary: 'Start payment for an online payment method',
                description: 'Starts payment for the online payment method saved on the cart of the Bearer token, instead of placing the order. Addresses, shipping method and payment method must already be saved. `type` says how to hand the customer to the gateway: `redirect` (open `redirectUrl`: Stripe, PhonePe, PayGlocal), `form` (POST `formFields` to `formAction`: PayU, PayPal Standard) or `sdk` (open the gateway SDK with `sdkData`: Razorpay, PayPal Smart Button). For `redirect` and `form`, open the page in a WebView and watch for `successUrl` (paid) or `failureUrl` (failed or cancelled); the gateway\'s own return places the order. Then call `POST /api/shop/checkout-payments/confirm` with `reference` to read it. For `sdk`, call the same endpoint with what the SDK returned. `reference` identifies this cart\'s payment and still resolves after the order is placed. Cash on delivery and money transfer are placed with `POST /api/shop/checkout-orders` instead. Stripe also returns `sessionId`, which the confirmation still accepts.',
                requestBody: new RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'paymentMethod' => [
                                        'type' => 'string',
                                        'description' => 'Optional. When sent, it must match the payment method saved on the cart.',
                                        'example' => 'razorpay',
                                    ],
                                ],
                            ],

                            'example' => new \ArrayObject,
                        ],
                    ]),
                ),
                responses: [
                    201 => new Response(
                        description: 'What the client needs to start the payment.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'examples' => [
                                    'redirect' => [
                                        'summary' => 'Stripe, PhonePe, PayGlocal',
                                        'value' => [
                                            'success' => true,
                                            'cartId' => 12,
                                            'method' => 'stripe',
                                            'type' => 'redirect',
                                            'reference' => '12',
                                            'sessionId' => 'cs_test_a1b2c3',
                                            'redirectUrl' => 'https://checkout.stripe.com/c/pay/cs_test_a1b2c3',
                                            'formAction' => null,
                                            'formFields' => null,
                                            'sdkData' => null,
                                            'successUrl' => 'https://example.com/checkout/onepage/success',
                                            'failureUrl' => 'https://example.com/checkout/cart',
                                            'message' => 'Payment started. Hand the customer to the gateway.',
                                        ],
                                    ],
                                    'form' => [
                                        'summary' => 'PayU, PayPal Standard',
                                        'value' => [
                                            'success' => true,
                                            'cartId' => 12,
                                            'method' => 'payu',
                                            'type' => 'form',
                                            'reference' => '12',
                                            'sessionId' => null,
                                            'redirectUrl' => null,
                                            'formAction' => 'https://test.payu.in/_payment',
                                            'formFields' => [
                                                'key' => 'gtKFFx',
                                                'txnid' => '12_1730798400',
                                                'amount' => '109.00',
                                                'hash' => 'a1b2c3',
                                            ],
                                            'sdkData' => null,
                                            'successUrl' => 'https://example.com/checkout/onepage/success',
                                            'failureUrl' => 'https://example.com/checkout/cart',
                                            'message' => 'Payment started. Hand the customer to the gateway.',
                                        ],
                                    ],
                                    'razorpay' => [
                                        'summary' => 'Razorpay',
                                        'value' => [
                                            'success' => true,
                                            'cartId' => 12,
                                            'method' => 'razorpay',
                                            'type' => 'sdk',
                                            'reference' => '12',
                                            'sessionId' => null,
                                            'redirectUrl' => null,
                                            'formAction' => null,
                                            'formFields' => null,
                                            'sdkData' => [
                                                'key' => 'rzp_test_xxxxx',
                                                'amount' => 10900,
                                                'currency' => 'INR',
                                                'order_id' => 'order_P9xYz',
                                            ],
                                            'successUrl' => null,
                                            'failureUrl' => null,
                                            'message' => 'Payment started. Hand the customer to the gateway.',
                                        ],
                                    ],
                                    'paypal_smart_button' => [
                                        'summary' => 'PayPal Smart Button',
                                        'value' => [
                                            'success' => true,
                                            'cartId' => 12,
                                            'method' => 'paypal_smart_button',
                                            'type' => 'sdk',
                                            'reference' => '12',
                                            'sessionId' => null,
                                            'redirectUrl' => null,
                                            'formAction' => null,
                                            'formFields' => null,
                                            'sdkData' => [
                                                'orderId' => '5O190127TN364715T',
                                                'clientId' => 'AbCdEf-sandbox-client-id',
                                                'currency' => 'USD',
                                                'intent' => 'capture',
                                            ],
                                            'successUrl' => null,
                                            'failureUrl' => null,
                                            'message' => 'Payment started. Hand the customer to the gateway.',
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
                        description: 'The bearer token has no active cart.',
                    ),
                    422 => new Response(
                        description: 'The cart is not ready, its payment method is not an online gateway or is not configured, the store currency is not supported, or the gateway refused the payment.',
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CheckoutPaymentSessionInput::class,
            output: self::class,
            processor: CheckoutPaymentSessionProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Start payment for the online payment method of the bearer cart and return how to hand the customer to the gateway.',
        ),
    ],
)]
class CheckoutPaymentSession
{
    /**
     * Whether the payment was started.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public bool $success = false;

    /**
     * The cart the payment is for.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?int $cartId = null;

    /**
     * The payment method the cart pays with.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $method = null;

    /**
     * How to hand the customer to the gateway: redirect, form or sdk.
     */
    #[ApiProperty(readable: true, writable: false, schema: ['type' => 'string', 'enum' => ['redirect', 'form', 'sdk']])]
    #[Groups(['mutation'])]
    public ?string $type = null;

    /**
     * The payment reference to send to the confirmation.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $reference = null;

    /**
     * The Stripe Checkout Session id, which the confirmation also accepts.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $sessionId = null;

    /**
     * The gateway page to open, for redirect payments.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $redirectUrl = null;

    /**
     * The gateway URL to post the form fields to, for form payments.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $formAction = null;

    /**
     * The fields to post to the gateway, for form payments.
     */
    #[ApiProperty(readable: true, writable: false, schema: ['type' => 'object', 'nullable' => true, 'additionalProperties' => true])]
    #[Groups(['mutation'])]
    public ?array $formFields = null;

    /**
     * The options to open the gateway SDK with, for sdk payments.
     */
    #[ApiProperty(readable: true, writable: false, schema: ['type' => 'object', 'nullable' => true, 'additionalProperties' => true])]
    #[Groups(['mutation'])]
    public ?array $sdkData = null;

    /**
     * The page the WebView reaches once the gateway reports the payment paid.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $successUrl = null;

    /**
     * The page the WebView reaches when the payment failed or was cancelled.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $failureUrl = null;

    /**
     * Human-readable result of the call.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public string $message = '';
}
