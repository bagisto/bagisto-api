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
            ],
            openapi: new Operation(
                tags: ['Checkout'],
                summary: 'Confirm a Stripe checkout payment',
                description: 'Verifies the Stripe Checkout Session server-side (paid, same cart, same amount and currency) and returns the order for the cart identified by the bearer token. Repeatable: when the Stripe webhook or an earlier call already placed the order, that order is returned and no second order is created.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['sessionId'],

                                'properties' => [
                                    'sessionId' => [
                                        'type' => 'string',
                                        'example' => 'cs_test_123',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    201 => new Response(
                        description: 'Payment verified and order created or already settled.',
                    ),
                    422 => new Response(
                        description: 'Missing, unpaid, invalid, or cart-mismatched Stripe session.',
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
            description: 'Verify a Stripe Checkout Session server-side and return the order for the bearer cart, placing it once when it does not exist yet.',
        ),
    ],
)]
class CheckoutPaymentConfirmation
{
    /**
     * Whether the payment was verified and the order exists.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public bool $success = false;

    /**
     * The cart the checkout session was paid for.
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
     * Human-readable result of the confirmation.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public string $message = '';
}
