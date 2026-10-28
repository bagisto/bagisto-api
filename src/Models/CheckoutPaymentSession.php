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
            ],
            openapi: new Operation(
                tags: ['Checkout'],
                summary: 'Start a Stripe checkout payment',
                description: 'Creates a Stripe Checkout Session for the cart identified by the bearer token, after the checkout-order call answered with redirect true. Open redirectUrl for the shopper to pay, then send sessionId to POST /api/shop/checkout-payments/confirm. Send an empty JSON body `{}`.',
                requestBody: new RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => new \ArrayObject,
                            ],

                            'example' => new \ArrayObject,
                        ],
                    ]),
                ),
                responses: [
                    201 => new Response(
                        description: 'The Stripe Checkout Session was created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'cartId' => 12,
                                    'sessionId' => 'cs_test_123',
                                    'redirectUrl' => 'https://checkout.stripe.com/c/pay/cs_test_123',
                                    'message' => 'This payment method requires the shopper to complete payment on the gateway. Send them to redirectUrl; the order is created once the gateway confirms the payment.',
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
                        description: 'The cart is not paying with Stripe, Stripe is not available, or the cart is not ready to be paid.',
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
            description: 'Create a Stripe Checkout Session for the bearer cart and return its id and payment page URL.',
        ),
    ],
)]
class CheckoutPaymentSession
{
    /**
     * Whether the checkout session was created.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public bool $success = false;

    /**
     * The cart the checkout session charges for.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?int $cartId = null;

    /**
     * The Stripe Checkout Session id to confirm once the shopper has paid.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $sessionId = null;

    /**
     * The Stripe payment page to open for the shopper.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $redirectUrl = null;

    /**
     * Human-readable result of the call.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public string $message = '';
}
