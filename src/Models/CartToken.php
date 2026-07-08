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

/**
 * CartToken - GraphQL API Resource for Cart Operations
 *
 * Provides mutations and queries for shopping cart management with token-based authentication.
 * Supports both authenticated users and guest users via cart tokens.
 *
 * Operations:
 * - createCartToken: Create new guest cart with unique UUID token
 * - updateCartItem: Update cart item quantity
 * - removeCartItem: Remove item from cart
 * - readCart: Get single cart details
 * - cartCollection: Get all customer carts
 * - mergeGuestCart: Merge guest cart to customer cart on login
 * - applyCouponCode: Apply discount coupon
 */
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
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $customerId = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $channelId = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $itemsCount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?array $items = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $subtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseSubtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $discountAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseDiscountAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $taxAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseTaxAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $shippingAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseShippingAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $grandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseGrandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedSubtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedDiscountAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedTaxAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedShippingAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedGrandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $couponCode = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?bool $success = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $message = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?array $carts = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $sessionToken = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?bool $isGuest = null;
}
