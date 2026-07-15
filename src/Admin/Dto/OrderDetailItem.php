<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Order line-item embedded in the order detail.
 *
 * `type` is the product-type discriminator (simple / configurable / bundle /
 * downloadable / grouped / virtual). Type-specific data travels in
 * `additional`, `child`, `children`, and `downloadableLinks` — the frontend
 * switches on `type`.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderDetailItem
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $sku = null;

    public ?string $type = null;

    public ?string $name = null;

    public ?int $productId = null;

    public ?float $weight = null;

    public ?int $qtyOrdered = null;

    public ?int $qtyShipped = null;

    public ?int $qtyInvoiced = null;

    public ?int $qtyCanceled = null;

    public ?int $qtyRefunded = null;

    public ?float $price = null;

    public ?string $formattedPrice = null;

    public ?float $basePrice = null;

    public ?float $total = null;

    public ?string $formattedTotal = null;

    public ?float $baseTotal = null;

    public ?float $taxAmount = null;

    public ?string $formattedTaxAmount = null;

    public ?float $taxPercent = null;

    public ?float $discountAmount = null;

    public ?string $formattedDiscountAmount = null;

    public ?float $discountPercent = null;

    /** Type-specific cart data: super_attribute, bundle_options, selected options, etc. */
    public ?array $additional = null;

    /** Configurable: the chosen variant. Null for other types. */
    public ?OrderDetailItem $child = null;

    /** Bundle / grouped / configurable child line-items. */
    public array $children = [];

    /** Downloadable: purchased link rows. Empty for other types. */
    public array $downloadableLinks = [];

    public ?string $createdAt = null;
}
