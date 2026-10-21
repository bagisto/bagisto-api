<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderDetailItem
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

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
     * The product id.
     */
    public ?int $productId = null;

    /**
     * The weight.
     */
    public ?float $weight = null;

    /**
     * The qty ordered.
     */
    public ?int $qtyOrdered = null;

    /**
     * The qty shipped.
     */
    public ?int $qtyShipped = null;

    /**
     * The qty invoiced.
     */
    public ?int $qtyInvoiced = null;

    /**
     * The qty canceled.
     */
    public ?int $qtyCanceled = null;

    /**
     * The qty refunded.
     */
    public ?int $qtyRefunded = null;

    /**
     * The price.
     */
    public ?float $price = null;

    /**
     * The formatted price.
     */
    public ?string $formattedPrice = null;

    /**
     * The base price.
     */
    public ?float $basePrice = null;

    /**
     * The total.
     */
    public ?float $total = null;

    /**
     * The formatted total.
     */
    public ?string $formattedTotal = null;

    /**
     * The base total.
     */
    public ?float $baseTotal = null;

    /**
     * The tax amount.
     */
    public ?float $taxAmount = null;

    /**
     * The formatted tax amount.
     */
    public ?string $formattedTaxAmount = null;

    /**
     * The tax percent.
     */
    public ?float $taxPercent = null;

    /**
     * The discount amount.
     */
    public ?float $discountAmount = null;

    /**
     * The formatted discount amount.
     */
    public ?string $formattedDiscountAmount = null;

    /**
     * The discount percent.
     */
    public ?float $discountPercent = null;

    /**
     * Type-specific cart data: super_attribute, bundle_options, selected options, etc.
     */
    public ?array $additional = null;

    /**
     * Configurable: the chosen variant. Null for other types.
     */
    public ?OrderDetailItem $child = null;

    /**
     * Bundle / grouped / configurable child line-items.
     */
    public array $children = [];

    /**
     * Downloadable: purchased link rows. Empty for other types.
     */
    public array $downloadableLinks = [];

    /**
     * The created at.
     */
    public ?string $createdAt = null;
}
