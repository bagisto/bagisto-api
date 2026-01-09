<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
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
        new Post(uriTemplate: '/merge-carts/{id}'),
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
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
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
