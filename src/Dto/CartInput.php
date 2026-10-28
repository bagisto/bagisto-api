<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CartInput
{
    /**
     * ID field (optional, for GraphQL API Platform compatibility).
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation', 'query'])]
    public ?string $id = null;

    /**
     * Cart ID (optional, for specific cart operations).
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation', 'query'])]
    public ?int $cartId = null;

    /**
     * Guest cart token proving ownership of the guest cart (required for merge).
     */
    #[ApiProperty(required: false, description: 'Guest cart token of the cart being merged')]
    #[Groups(['mutation'])]
    public ?string $guestCartToken = null;

    /**
     * Product ID (required for addProduct operation).
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?int $productId = null;

    /**
     * Cart item ID (required for update/remove operations).
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?int $cartItemId = null;

    /**
     * Quantity of items to add/update.
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?int $quantity = null;

    /**
     * Product options/attributes (JSON).
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?array $options = null;

    /**
     * Coupon code for discount.
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?string $couponCode = null;

    /**
     * Shipping address ID.
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?int $shippingAddressId = null;

    /**
     * Billing address ID.
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?int $billingAddressId = null;

    /**
     * Shipping method code.
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?string $shippingMethod = null;

    /**
     * The selected shipping rate.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Selected shipping rate object')]
    public $selectedShippingRate = null;

    /**
     * Payment method code.
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?string $paymentMethod = null;

    /**
     * Session ID for creating new guest cart (createOrGetCart operation)
     * Used to identify guest session and generate unique token.
     */
    #[ApiProperty(required: false, description: 'Session ID for cart creation')]
    #[Groups(['mutation', 'query'])]
    public ?string $sessionId = null;

    /**
     * Flag to create new cart instead of using existing one
     * Used in createOrGetCart mutation.
     */
    #[ApiProperty(required: false, description: 'Generate new cart with unique token')]
    #[Groups(['mutation'])]
    public ?bool $createNew = false;

    /**
     * Array of cart item IDs for bulk operations (remove multiple, move to wishlist).
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?array $itemIds = null;

    /**
     * Array of quantities for bulk operations.
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?array $quantities = null;

    /**
     * Country code for shipping estimation.
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?string $country = null;

    /**
     * State/Province code for shipping estimation.
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?string $state = null;

    /**
     * Postal code for shipping estimation.
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?string $postcode = null;

    /**
     * Device token for push notifications (optional).
     */
    #[ApiProperty(required: false, description: 'Device token for push notifications')]
    #[Groups(['mutation'])]
    public ?string $deviceToken = null;

    /**
     * Is buy now flag (0 = add to cart, 1 = buy now)
     * Used for direct checkout.
     */
    #[ApiProperty(required: false, description: 'Is buy now (0 = cart, 1 = buy now)')]
    #[Groups(['mutation'])]
    public ?int $isBuyNow = null;

    /**
     * Bundle product options
     * Format: [option_id => [product_id1, product_id2, ...]].
     */
    #[ApiProperty(required: false, description: 'Bundle options JSON string. Example: {"1":[1],"2":[2]}')]
    #[Groups(['mutation'])]
    public ?string $bundleOptions = null;

    /**
     * Bundle product option quantities
     * Format: [option_id => quantity].
     */
    #[ApiProperty(required: false, description: 'Bundle option quantities JSON string. Example: {"1":1,"2":2}')]
    #[Groups(['mutation'])]
    public ?string $bundleOptionQty = null;

    /**
     * Selected configurable product option (child product ID)
     * Used for Configurable products.
     */
    #[ApiProperty(required: false, description: 'Selected configurable option (child product ID)')]
    #[Groups(['mutation'])]
    public ?int $selectedConfigurableOption = null;

    /**
     * Super attribute values for configurable products
     * Format: [attribute_id => option_value].
     */
    #[ApiProperty(required: false, description: 'Super attributes for configurable products')]
    #[Groups(['mutation'])]
    public ?array $superAttribute = null;

    /**
     * Quantities for grouped product associated products
     * Format: [associated_product_id => quantity].
     */
    #[ApiProperty(required: false, description: 'Quantities for grouped product associated products')]
    #[Groups(['mutation'])]
    public ?array $qty = null;

    /**
     * Quantities for grouped product associated products (GraphQL-friendly).
     * GraphQL input objects cannot have numeric keys (e.g. {101: 2}), so this accepts a JSON string.
     */
    #[ApiProperty(required: false, description: 'Grouped product quantities as JSON string. Example: {"101":2,"102":1}')]
    #[Groups(['mutation'])]
    public ?string $groupedQty = null;

    /**
     * Downloadable product links to purchase
     * Format: [link_id1, link_id2, ...].
     */
    #[ApiProperty(
        required: false,
        description: 'Downloadable product link IDs'
    )]
    #[Groups(['mutation'])]
    public ?array $links = null;

    /**
     * Customizable options for products
     * Format: [option_id => value].
     */
    #[ApiProperty(required: false, description: 'Customizable options')]
    #[Groups(['mutation'])]
    public ?array $customizableOptions = null;

    /**
     * Additional data for products
     * Used for any extra product-specific data.
     */
    #[ApiProperty(required: false, description: 'Additional product data')]
    #[Groups(['mutation'])]
    public ?array $additional = null;

    /**
     * Booking product options (GraphQL-friendly).
     * Bagisto expects booking options under the `booking` key when adding a booking product to cart.
     */
    #[ApiProperty(required: false, description: 'Booking options as JSON string')]
    #[Groups(['mutation'])]
    public ?string $booking = null;

    /**
     * Optional booking note (mainly for table booking).
     * If provided, it will be merged into the decoded `booking` payload as `booking.note`.
     */
    #[ApiProperty(required: false, description: 'Booking note / special request')]
    #[Groups(['mutation'])]
    public ?string $specialNote = null;

    /**
     * Backward compatibility: previous name for `specialNote`.
     */
    #[ApiProperty(required: false, description: 'Deprecated: use specialNote')]
    #[Groups(['mutation'])]
    public ?string $bookingNote = null;
}
