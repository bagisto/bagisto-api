<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderItemDto
{
    public ?int $id = null;

    public ?string $sku = null;

    public ?string $type = null;

    public ?string $name = null;

    public ?int $product_id = null;

    public ?string $product_type = null;

    public ?int $qty_ordered = null;

    public ?int $qty_shipped = null;

    public ?int $qty_invoiced = null;

    public ?int $qty_canceled = null;

    public ?int $qty_refunded = null;

    public ?float $price = null;

    public ?float $base_price = null;

    public ?float $total = null;

    public ?float $base_total = null;

    public ?float $discount_percent = null;

    public ?float $discount_amount = null;

    public ?float $tax_percent = null;

    public ?float $tax_amount = null;

    public ?float $price_incl_tax = null;

    public ?float $total_incl_tax = null;
}
