<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Contracts\SnakeCaseFieldsResource;
use Webkul\BagistoApi\Dto\CheckoutPaymentConfirmationInput;
use Webkul\BagistoApi\State\CheckoutPaymentConfirmationProcessor;

/**
 * Places the order after payment for gateways whose storefront return needs the browser session.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CheckoutPaymentConfirmation',
    operations: [
        new Post(
            uriTemplate: '/checkout-payments/confirm',
            input: CheckoutPaymentConfirmationInput::class,
            output: self::class,
            processor: CheckoutPaymentConfirmationProcessor::class,
            read: false,
            normalizationContext: ['skip_null_values' => false],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Checkout'],
                summary: 'Confirm payment and place the order',
                description: 'Places the order of the cart of the Bearer token after the customer paid, for Razorpay, PayPal Standard and PayPal Smart Button (the other gateways place the order on their own return). Razorpay: send the three values the Razorpay checkout returns; the signature is verified and the Razorpay order must belong to this cart and amount. PayPal Standard: send an empty body after PayPal returns the customer; as on the storefront the order is created pending and PayPal\'s IPN marks it paid. PayPal Smart Button: send `paypal_order_id` once the customer approved the payment in the PayPal SDK; the PayPal order must belong to this cart and total, it is captured, and the order is placed paid and invoiced.',
                requestBody: new RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'razorpay_order_id' => ['type' => 'string', 'example' => 'order_P9xYz'],
                                    'razorpay_payment_id' => ['type' => 'string', 'example' => 'pay_P9xZa'],
                                    'razorpay_signature' => ['type' => 'string', 'example' => '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08'],
                                    'paypal_order_id' => ['type' => 'string', 'example' => '5O190127TN364715T'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'The order was placed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'orderId' => 42,
                                    'orderIncrementId' => '42',
                                    'status' => 'processing',
                                    'success' => true,
                                    'message' => 'Payment confirmed and order placed.',
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Response(description: 'No cart token.'),
                    '422' => new Response(description: 'The method needs no confirmation, the payment could not be verified, or the cart is not ready.'),
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
            description: 'Place the order after a Razorpay, PayPal Standard or PayPal Smart Button payment.',
        ),
    ],
)]
class CheckoutPaymentConfirmation implements SnakeCaseFieldsResource
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $order_id = null;

    #[ApiProperty(writable: false)]
    public ?string $order_increment_id = null;

    #[ApiProperty(writable: false)]
    public ?string $status = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
