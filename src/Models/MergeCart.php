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
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\State\CartTokenMutationProvider;
use Webkul\BagistoApi\State\CartTokenProcessor;

/**
 * MergeCart - GraphQL API Resource for Merging Guest Cart to Customer Cart
 *
 * Provides mutation for merging guest cart items to authenticated customer cart.
 * After a guest user logs in, their guest cart items are merged into their customer cart.
 *
 * Features:
 * - Merges guest cart items into authenticated customer cart
 * - Combines duplicate items (same product) by adding quantities
 * - Deactivates guest cart after merge
 * - Requires authentication token (bearer token)
 *
 * Input Parameters:
 * - token: Guest cart token (guest user identifier)
 *
 * Usage:
 * 1. Guest user creates cart and adds items
 * 2. Guest user logs in (gets bearer token)
 * 3. Call merge mutation with bearer token to merge guest cart into customer cart
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'MergeCart',
    uriTemplate: '/merge-carts',
    operations: [
        new Post(
            uriTemplate: '/merge-carts',
            input: CartInput::class,
            output: CartData::class,
            processor: CartTokenProcessor::class,
            openapi: new Operation(
                summary: 'Merge guest cart into customer cart',
                description: 'Merges a guest cart into the authenticated customer\'s cart. Requires a valid bearer token and the guest cart ID.',
                requestBody: new RequestBody(
                    description: 'Guest cart details to merge',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['cart_id'],
                                'properties' => [
                                    'cart_id' => [
                                        'type' => 'integer',
                                        'description' => 'The ID of the guest cart to merge into the customer cart',
                                        'example' => 6884,
                                    ],
                                ],
                            ],
                            'example' => [
                                'cart_id' => 6884,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Guest cart merged. Returns the customer\'s updated cart.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 6885,
                                    'cartToken' => '6885',
                                    'customerId' => 1533,
                                    'channelId' => 1,
                                    'itemsCount' => 1,
                                    'itemsQty' => 1,
                                    'items' => [
                                        [
                                            'id' => 7763,
                                            'cartId' => 6885,
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
                                    'success' => true,
                                    'message' => 'Cart merged successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Guest cart not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
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
            description: 'Merge guest cart into authenticated customer cart. Requires bearer token.',
        ),
    ]
)]
class MergeCart
{
    /**
     * Unique identifier for the merged cart
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    /**
     * Token identifier for the cart (cart ID)
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;

    /**
     * Unique identifier for internal API Platform operations
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $_id = null;

    /**
     * ID of the customer who owns this cart
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $customerId = null;

    /**
     * ID of the sales channel this cart belongs to
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $channelId = null;

    /**
     * Total number of items in the merged cart
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $itemsCount = null;

    /**
     * Total quantity of all items (sum of item quantities)
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $itemsQty = null;

    /**
     * Cart subtotal before discounts and taxes
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $subtotal = null;

    /**
     * Base currency subtotal
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseSubtotal = null;

    /**
     * Total discount amount applied to cart
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $discountAmount = null;

    /**
     * Base currency discount amount
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseDiscountAmount = null;

    /**
     * Total tax amount on cart
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $taxAmount = null;

    /**
     * Base currency tax amount
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseTaxAmount = null;

    /**
     * Shipping cost for the cart
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $shippingAmount = null;

    /**
     * Base currency shipping amount
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseShippingAmount = null;

    /**
     * Grand total of the cart (subtotal + tax + shipping - discount)
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $grandTotal = null;

    /**
     * Base currency grand total
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $baseGrandTotal = null;

    /**
     * Formatted subtotal price (with currency symbol)
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedSubtotal = null;

    /**
     * Formatted discount amount
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedDiscountAmount = null;

    /**
     * Formatted tax amount
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedTaxAmount = null;

    /**
     * Formatted shipping amount
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedShippingAmount = null;

    /**
     * Formatted grand total price
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $formattedGrandTotal = null;

    /**
     * Applied coupon code (if any)
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $couponCode = null;

    /**
     * Session token for the cart
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $sessionToken = null;

    /**
     * Indicates if cart is for a guest user
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?bool $isGuest = null;

    /**
     * Success status of the merge operation
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?bool $success = null;

    /**
     * Response message from merge operation
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $message = null;

    /**
     * Whether cart has stockable items
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?bool $haveStockableItems = null;
}
