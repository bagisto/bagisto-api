<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CheckoutAddressInput;
use Webkul\BagistoApi\State\CheckoutProcessor;

/**
 * CheckoutPaymentMethod - GraphQL API Resource for Checkout Payment Method
 *
 * Provides mutation for selecting and saving payment method during checkout
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CheckoutPaymentMethod',
    operations: [
        new Post(
            uriTemplate: '/checkout-payment-methods',
            output: self::class,
            processor: CheckoutProcessor::class,
            normalizationContext: [
                'groups'            => ['mutation'],
                'skip_null_values'  => false,
            ],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Checkout'],
                summary: 'Save selected payment method for checkout',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['paymentMethod'],
                                'properties' => [
                                    'paymentMethod'     => ['type' => 'string', 'example' => 'moneytransfer'],
                                    'paymentSuccessUrl' => ['type' => 'string', 'example' => 'https://myapp.com/payment/success'],
                                    'paymentFailureUrl' => ['type' => 'string', 'example' => 'https://myapp.com/payment/failure'],
                                    'paymentCancelUrl'  => ['type' => 'string', 'example' => 'https://myapp.com/payment/cancel'],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CheckoutAddressInput::class,
            output: self::class,
            processor: CheckoutProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Save selected payment method for checkout. Returns success status and message.',
        ),
    ]
)]
class CheckoutPaymentMethod
{
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public bool $success = false;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public string $message = '';

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $cartToken = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $paymentMethod = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $paymentRedirectUrl = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $paymentGatewayUrl = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $paymentData = null;
}
