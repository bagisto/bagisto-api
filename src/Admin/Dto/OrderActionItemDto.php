<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

/**
 * Shared line-item DTO embedded in Invoice, Shipment, and Refund detail
 * responses. All three monolith repos build the same shape (`order_item_id`,
 * `sku`, `name`, `qty`, `price`, `base_price`, `total`, `tax_amount`,
 * `discount_amount`, plus the formatted variants) — so we expose one DTO
 * across the three detail payloads instead of three near-identical ones.
 *
 * Properties are snake_case so they resolve over both REST and GraphQL; the name
 * converter surfaces them as camelCase to clients. The AcceptsCamelCaseWrites trait
 * lets the per-resource providers keep assigning camelCase ($item->orderItemId = …).
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderActionItemDto
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $order_item_id = null;

    public ?string $sku = null;

    public ?string $name = null;

    public ?int $qty = null;

    public ?float $price = null;

    public ?string $formatted_price = null;

    public ?float $base_price = null;

    /** Per-unit price including tax (base currency) — shown on the admin invoice/refund view. */
    public ?float $base_price_incl_tax = null;

    public ?float $total = null;

    public ?string $formatted_total = null;

    public ?float $base_total = null;

    /** Row total including tax (base currency) — shown on the admin invoice/refund view. */
    public ?float $base_total_incl_tax = null;

    /** Product base image URL — shown beside each line item on the admin view. */
    public ?string $base_image_url = null;

    public ?float $tax_amount = null;

    public ?string $formatted_tax_amount = null;

    public ?float $discount_amount = null;

    public ?string $formatted_discount_amount = null;

    public ?int $product_id = null;

    public ?string $product_type = null;

    public ?array $additional = null;
}
