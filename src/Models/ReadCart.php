<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
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
                'groups'           => ['query', 'mutation'],
                'skip_null_values' => false,
            ],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Cart'],
                summary: 'Get cart details for the authenticated customer',
                description: 'Returns the active cart for the authenticated customer (identified by the Bearer token in the Authorization header). Response mirrors the GraphQL `createReadCart` mutation and includes items, totals, coupons, shipping/payment method, and addresses. Request body can be an empty JSON object `{}`.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => new \ArrayObject,
                                'example'    => new \ArrayObject,
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
            args: [],
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
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
     * @var \Webkul\BagistoApi\Dto\CartItemData[]|null
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
    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the channel record associated with the address.
     */
    #[ApiProperty(readableLink: true, writable: false, readable: true)]
    public function channel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Get shipping rates relationship
     */
    public function shipping_rates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ShippingRates::class, 'cart_id');
    }
}
