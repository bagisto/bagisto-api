<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Shared line-item DTO embedded in Invoice, Shipment, and Refund detail
 * responses. All three monolith repos build the same shape (`order_item_id`,
 * `sku`, `name`, `qty`, `price`, `base_price`, `total`, `tax_amount`,
 * `discount_amount`, plus the formatted variants) — so we expose one DTO
 * across the three detail payloads instead of three near-identical ones.
 *
 * `formattedTotal` and friends are filled by the per-resource provider since
 * the source-of-truth currency is on the parent order.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderActionItemDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $orderItemId = null;

    public ?string $sku = null;

    public ?string $name = null;

    public ?int $qty = null;

    public ?float $price = null;

    public ?string $formattedPrice = null;

    public ?float $basePrice = null;

    public ?float $total = null;

    public ?string $formattedTotal = null;

    public ?float $baseTotal = null;

    public ?float $taxAmount = null;

    public ?string $formattedTaxAmount = null;

    public ?float $discountAmount = null;

    public ?string $formattedDiscountAmount = null;

    public ?int $productId = null;

    public ?string $productType = null;

    public ?array $additional = null;
}
