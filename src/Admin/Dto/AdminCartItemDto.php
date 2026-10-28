<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminCartItemDto
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The cart id.
     */
    public ?int $cartId = null;

    /**
     * The product id.
     */
    public ?int $productId = null;

    /**
     * The parent id.
     */
    public ?int $parentId = null;

    /**
     * The sku.
     */
    public ?string $sku = null;

    /**
     * The type.
     */
    public ?string $type = null;

    /**
     * The name.
     */
    public ?string $name = null;

    /**
     * The quantity.
     */
    public ?int $quantity = null;

    /**
     * The price.
     */
    public ?float $price = null;

    /**
     * The formatted price.
     */
    public ?string $formattedPrice = null;

    /**
     * The total.
     */
    public ?float $total = null;

    /**
     * The formatted total.
     */
    public ?string $formattedTotal = null;

    /**
     * The tax amount.
     */
    public ?float $taxAmount = null;

    /**
     * The formatted tax amount.
     */
    public ?string $formattedTaxAmount = null;

    /**
     * The discount amount.
     */
    public ?float $discountAmount = null;

    /**
     * The formatted discount amount.
     */
    public ?string $formattedDiscountAmount = null;

    /**
     * Type-specific add-to-cart payload (super_attribute, bundle_options, etc).
     */
    public ?array $additional = null;

    /**
     * Configurable child variant — null for non-configurable items.
     */
    public ?AdminCartItemDto $child = null;

    /**
     * Bundle / grouped / configurable child rows.
     */
    public array $children = [];
}
