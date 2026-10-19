<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderItemDto
{
    /**
     * The id.
     */
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
    public ?int $product_id = null;

    /**
     * The product type.
     */
    public ?string $product_type = null;

    /**
     * The qty ordered.
     */
    public ?int $qty_ordered = null;

    /**
     * The qty shipped.
     */
    public ?int $qty_shipped = null;

    /**
     * The qty invoiced.
     */
    public ?int $qty_invoiced = null;

    /**
     * The qty canceled.
     */
    public ?int $qty_canceled = null;

    /**
     * The qty refunded.
     */
    public ?int $qty_refunded = null;

    /**
     * The price.
     */
    public ?float $price = null;

    /**
     * The base price.
     */
    public ?float $base_price = null;

    /**
     * The total.
     */
    public ?float $total = null;

    /**
     * The base total.
     */
    public ?float $base_total = null;

    /**
     * The discount percent.
     */
    public ?float $discount_percent = null;

    /**
     * The discount amount.
     */
    public ?float $discount_amount = null;

    /**
     * The tax percent.
     */
    public ?float $tax_percent = null;

    /**
     * The tax amount.
     */
    public ?float $tax_amount = null;

    /**
     * The price incl tax.
     */
    public ?float $price_incl_tax = null;

    /**
     * The total incl tax.
     */
    public ?float $total_incl_tax = null;
}
