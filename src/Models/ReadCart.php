<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\Dto\CartItemData;
use Webkul\BagistoApi\State\CartTokenMutationProvider;
use Webkul\BagistoApi\State\CartTokenProcessor;
use Webkul\Checkout\Models\Cart;

/**
 * ReadCart - GraphQL API Resource for Reading Cart Details
 *
 * Provides mutation for retrieving cart details by ID or token.
 * Using 'create' operation name ensures ID is NOT required in input.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ReadCart',
    uriTemplate: '/read-carts',
    operations: [
        new Post(
            uriTemplate: '/cart',
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            normalizationContext: [
                'groups' => ['query', 'mutation'],
                'skip_null_values' => false,
            ],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Cart'],
                summary: 'Get cart details (authenticated customer or guest)',
                description: 'Returns the active cart with items, totals, coupons, shipping/payment method, and addresses. Works for both a logged-in customer (identified by the Bearer token) and a guest (pass the guest cart `token` — from POST /api/shop/cart-token — in the request body). It is a POST (not GET) because the cart is identified by a token/id carried in the body and reading it recalculates totals; the GraphQL equivalent is the `createReadCart` mutation for the same reason. Body may be an empty `{}` for a logged-in customer.',
                requestBody: new RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => new \ArrayObject,
                                'example' => new \ArrayObject,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'The cart with items, totals, coupon, and selected shipping/payment.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 6888,
                                    'cartToken' => '6888',
                                    'customerId' => 1537,
                                    'channelId' => 1,
                                    'itemsCount' => 1,
                                    'items' => [
                                        [
                                            'id' => 7765,
                                            'cartId' => 6888,
                                            'productId' => 1,
                                            'name' => 'Coastal Breeze Men\'s Blue Zipper Hoodie',
                                            'sku' => 'COASTALBREEZEMENSHOODIE',
                                            'quantity' => 1,
                                            'price' => 100,
                                            'total' => 100,
                                            'type' => 'simple',
                                            'formattedPrice' => '$100.00',
                                            'formattedTotal' => '$100.00',
                                        ],
                                    ],
                                    'subtotal' => 100,
                                    'grandTotal' => 100,
                                    'taxAmount' => 0,
                                    'discountAmount' => 0,
                                    'couponCode' => null,
                                    'formattedSubtotal' => '$100.00',
                                    'formattedGrandTotal' => '$100.00',
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Response(description: 'No cart found for the given token, or not authenticated.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            args: [],
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Get cart details by cartId or token - pass cartId or token in input',
        ),
    ]
)]
class ReadCart extends Cart
{
    protected $appends = [
        'selected_shipping_rate',
    ];

    protected $with = [
        'selected_shipping_rate',
    ];

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $cartToken = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?int $customerId = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?int $channelId = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?int $itemsCount = null;

    /**
     * Cart items - array of CartItemData objects
     *
     * @var CartItemData[]|null
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?array $items = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?float $subtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?float $baseSubtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?float $discountAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?float $baseDiscountAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?float $taxAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?float $baseTaxAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?float $shippingAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?float $baseShippingAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?float $grandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?float $baseGrandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $formattedSubtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $formattedGrandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $formattedTaxAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $formattedShippingAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $formattedDiscountAmount = null;

    #[ApiProperty(readableLink: true, writable: false, readable: true)]
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the channel record associated with the address.
     */
    #[ApiProperty(readableLink: true, writable: false, readable: true)]
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Get shipping rates relationship
     */
    public function shipping_rates(): HasMany
    {
        return $this->hasMany(ShippingRates::class, 'cart_id');
    }
}
