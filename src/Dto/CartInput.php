<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for cart operations with token-based authentication
 *
 * Supports both authenticated users (via bearer token) and guest users (via cart token).
 * Operations: add product, update item quantity, remove item, get cart, get all carts
 *
 * Authentication token is passed via Authorization: Bearer header, not as input parameter.
 */
class CartInput
{
    /**
     * ID field (optional, for GraphQL API Platform compatibility)
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation', 'query'])]
    public ?string $id = null;

    /**
     * Cart ID (optional, for specific cart operations)
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation', 'query'])]
    public ?int $cartId = null;

    /**
     * Product ID (required for addProduct operation)
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?int $productId = null;

    /**
     * Cart item ID (required for update/remove operations)
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?int $cartItemId = null;

    /**
     * Quantity of items to add/update
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?int $quantity = null;

    /**
     * Product options/attributes (JSON)
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?array $options = null;

    /**
     * Coupon code for discount
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?string $couponCode = null;

    /**
     * Shipping address ID
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?int $shippingAddressId = null;

    /**
     * Billing address ID
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?int $billingAddressId = null;

    /**
     * Shipping method code
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?string $shippingMethod = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Selected shipping rate object')]
    public $selectedShippingRate = null;

    /**
     * Payment method code
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?string $paymentMethod = null;

    /**
     * Session ID for creating new guest cart (createOrGetCart operation)
     * Used to identify guest session and generate unique token
     */
    #[ApiProperty(required: false, description: 'Session ID for cart creation')]
    #[Groups(['mutation', 'query'])]
    public ?string $sessionId = null;

    /**
     * Flag to create new cart instead of using existing one
     * Used in createOrGetCart mutation
     */
    #[ApiProperty(required: false, description: 'Generate new cart with unique token')]
    #[Groups(['mutation'])]
    public ?bool $createNew = false;

    /**
     * Array of cart item IDs for bulk operations (remove multiple, move to wishlist)
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?array $itemIds = null;

    /**
     * Array of quantities for bulk operations
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?array $quantities = null;

    /**
     * Country code for shipping estimation
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?string $country = null;

    /**
     * State/Province code for shipping estimation
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?string $state = null;

    /**
     * Postal code for shipping estimation
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?string $postcode = null;
}
