<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class RefundItemDto
{
    public ?int $id = null;

    public ?int $order_item_id = null;

    public ?int $product_id = null;

    public ?string $sku = null;

    public ?string $name = null;

    public ?int $qty = null;

    public ?float $price = null;

    public ?float $base_price = null;

    public ?float $price_incl_tax = null;

    public ?float $base_price_incl_tax = null;

    public ?float $total = null;

    public ?float $base_total = null;

    public ?float $total_incl_tax = null;

    public ?float $base_total_incl_tax = null;

    public ?float $tax_amount = null;

    public ?float $base_tax_amount = null;

    public ?float $discount_percent = null;

    public ?float $discount_amount = null;

    public ?float $base_discount_amount = null;

    public ?string $formatted_price = null;

    public ?string $formatted_price_incl_tax = null;

    public ?string $formatted_total = null;

    public ?string $formatted_total_incl_tax = null;

    public ?string $formatted_tax_amount = null;

    public ?string $formatted_discount_amount = null;

    public ?array $additional = null;
}
