<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\State\CartTokenMutationProvider;
use Webkul\BagistoApi\State\CartTokenProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CartToken',
    paginationEnabled: false,
    uriTemplate: '/cart-tokens/{id}',
    operations: [
        new Post(
            name: 'createCartToken',
            uriTemplate: '/cart-tokens',
            input: CartInput::class,
            output: CartToken::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Create new guest cart with unique UUID token or get authenticated customer cart. Returns sessionToken for guests.',
            openapi: new Model\Operation(
                tags: ['Cart'],
                summary: 'Create cart token',
                description: 'Create a new guest cart with unique UUID token or get authenticated customer cart. Returns sessionToken for guests.',
                requestBody: new Model\RequestBody(
                    description: 'Cart creation payload',
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',

                                'properties' => [
                                    'sessionId' => [
                                        'type' => 'string',
                                        'example' => 'my-session-id',
                                        'description' => 'Session ID for cart creation (optional)',
                                    ],

                                    'createNew' => [
                                        'type' => 'boolean',
                                        'example' => true,
                                        'description' => 'Generate new cart with unique token (optional)',
                                    ],
                                ],
                            ],

                            'examples' => [
                                'guest_cart' => [
                                    'summary' => 'Create Guest Cart',
                                    'description' => 'Create a new guest cart token',

                                    'value' => [
                                        'createNew' => true,
                                    ],
                                ],

                                'guest_cart_with_session' => [
                                    'summary' => 'Create Guest Cart with Session ID',
                                    'description' => 'Create a guest cart linked to a session',

                                    'value' => [
                                        'sessionId' => 'my-session-id',
                                        'createNew' => true,
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Guest cart created; returns the cart token used to identify the guest for subsequent cart/checkout calls.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 6891,
                                    'cartToken' => '81975c94-4d61-4be8-b2f9-3562d58e646c',
                                    'customerId' => null,
                                    'channelId' => 1,
                                    'itemsCount' => 0,
                                    'items' => [],
                                    'subtotal' => 0,
                                    'grandTotal' => 0,
                                    'taxAmount' => 0,
                                    'discountAmount' => 0,
                                    'couponCode' => null,
                                    'formattedSubtotal' => '$0.00',
                                    'formattedGrandTotal' => '$0.00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CartInput::class,
            output: CartToken::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Create new guest cart with unique UUID token or get authenticated customer cart. Returns sessionToken for guests.',
        ),
    ]
)]
class CartToken
{
    /**
     * The id.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    /**
     * The cart token.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;

    /**
     * The customer id.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $customerId = null;

    /**
     * The channel id.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $channelId = null;

    /**
     * The items count.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $itemsCount = null;

    /**
     * The items.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?array $items = null;

    /**
     * The subtotal.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $subtotal = null;

    /**
     * The base subtotal.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseSubtotal = null;

    /**
     * The discount amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $discountAmount = null;

    /**
     * The base discount amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseDiscountAmount = null;

    /**
     * The tax amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $taxAmount = null;

    /**
     * The base tax amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseTaxAmount = null;

    /**
     * The shipping amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $shippingAmount = null;

    /**
     * The base shipping amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseShippingAmount = null;

    /**
     * The grand total.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $grandTotal = null;

    /**
     * The base grand total.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseGrandTotal = null;

    /**
     * The formatted subtotal.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedSubtotal = null;

    /**
     * The formatted discount amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedDiscountAmount = null;

    /**
     * The formatted tax amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedTaxAmount = null;

    /**
     * The formatted shipping amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedShippingAmount = null;

    /**
     * The formatted grand total.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedGrandTotal = null;

    /**
     * The coupon code.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $couponCode = null;

    /**
     * The success.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?bool $success = null;

    /**
     * The message.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $message = null;

    /**
     * The carts.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?array $carts = null;

    /**
     * The session token.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $sessionToken = null;

    /**
     * The is guest.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?bool $isGuest = null;
}
