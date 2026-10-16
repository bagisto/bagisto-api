<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Contracts\SnakeCaseFieldsResource;
use Webkul\BagistoApi\Dto\CheckoutPaymentInitiationInput;
use Webkul\BagistoApi\State\CheckoutPaymentInitiationProcessor;

/**
 * Starts payment for the cart's selected online payment method.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CheckoutPaymentInitiation',
    operations: [
        new Post(
            uriTemplate: '/checkout-payments/initiate',
            input: false,
            output: self::class,
            processor: CheckoutPaymentInitiationProcessor::class,
            read: false,
            deserialize: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Operation(
                tags: ['Checkout'],
                summary: 'Start payment for an online payment method',
                description: 'Call this instead of placing the order when the selected payment method is an online gateway (the place-order response then has `redirect: true`). Uses the cart of the Bearer token; addresses, shipping and payment method must already be saved. The response says how to hand the customer to the gateway: `redirect` (open `redirectUrl`: Stripe, PhonePe, PayGlocal), `form` (POST `formFields` to `formAction`: PayPal Standard, PayU) or `sdk` (open the gateway SDK with `sdkData`: Razorpay). Stripe, PhonePe, PayGlocal and PayU create the order on their own return. For Razorpay and PayPal Standard, call `POST /api/shop/checkout-payments/confirm` after payment.',
                responses: [
                    '201' => new Response(
                        description: 'What the client needs to start the payment.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'examples' => [
                                    'redirect' => [
                                        'summary' => 'Stripe, PhonePe, PayGlocal',
                                        'value' => [
                                            'method' => 'stripe',
                                            'type' => 'redirect',
                                            'redirectUrl' => 'https://checkout.stripe.com/c/pay/cs_test_a1b2c3',
                                            'formAction' => null,
                                            'formFields' => null,
                                            'sdkData' => null,
                                        ],
                                    ],
                                    'sdk' => [
                                        'summary' => 'Razorpay',
                                        'value' => [
                                            'method' => 'razorpay',
                                            'type' => 'sdk',
                                            'redirectUrl' => null,
                                            'formAction' => null,
                                            'formFields' => null,
                                            'sdkData' => [
                                                'key' => 'rzp_test_xxxxx',
                                                'amount' => 10900,
                                                'currency' => 'INR',
                                                'order_id' => 'order_P9xYz',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Response(description: 'No cart token.'),
                    '422' => new Response(description: 'The cart is not ready, the method is not an online gateway, or the gateway rejected the request.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CheckoutPaymentInitiationInput::class,
            output: self::class,
            processor: CheckoutPaymentInitiationProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            description: 'Start payment for the selected online payment method of the cart of the Bearer token.',
        ),
    ],
)]
class CheckoutPaymentInitiation implements SnakeCaseFieldsResource
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?string $method = null;

    #[ApiProperty(writable: false, schema: ['type' => 'string', 'enum' => ['redirect', 'form', 'sdk']])]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?string $redirect_url = null;

    #[ApiProperty(writable: false)]
    public ?string $form_action = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ApiProperty(writable: false, schema: ['type' => 'object', 'nullable' => true, 'additionalProperties' => true])]
    public ?array $form_fields = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ApiProperty(writable: false, schema: ['type' => 'object', 'nullable' => true, 'additionalProperties' => true])]
    public ?array $sdk_data = null;
}
