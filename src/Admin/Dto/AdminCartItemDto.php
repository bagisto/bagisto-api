<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Single cart-item line embedded in the admin draft cart payload.
 *
 * Mirrors the monolith CartItemResource shape but in camelCase. `additional`
 * carries the type-specific data the storefront add-to-cart routine recorded
 * (super_attribute, bundle_options, selected configurable option, etc).
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminCartItemDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $cartId = null;

    public ?int $productId = null;

    public ?int $parentId = null;

    public ?string $sku = null;

    public ?string $type = null;

    public ?string $name = null;

    public ?int $quantity = null;

    public ?float $price = null;

    public ?string $formattedPrice = null;

    public ?float $total = null;

    public ?string $formattedTotal = null;

    public ?float $taxAmount = null;

    public ?string $formattedTaxAmount = null;

    public ?float $discountAmount = null;

    public ?string $formattedDiscountAmount = null;

    /** Type-specific add-to-cart payload (super_attribute, bundle_options, etc). */
    public ?array $additional = null;

    /** Configurable child variant — null for non-configurable items. */
    public ?AdminCartItemDto $child = null;

    /** Bundle / grouped / configurable child rows. */
    public array $children = [];
}
