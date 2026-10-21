<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderActionItemDto
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The order item id.
     */
    public ?int $order_item_id = null;

    /**
     * The sku.
     */
    public ?string $sku = null;

    /**
     * The name.
     */
    public ?string $name = null;

    /**
     * The qty.
     */
    public ?int $qty = null;

    /**
     * The price.
     */
    public ?float $price = null;

    /**
     * The formatted price.
     */
    public ?string $formatted_price = null;

    /**
     * The base price.
     */
    public ?float $base_price = null;

    /**
     * Per-unit price including tax (base currency) — shown on the admin invoice/refund view.
     */
    public ?float $base_price_incl_tax = null;

    /**
     * The total.
     */
    public ?float $total = null;

    /**
     * The formatted total.
     */
    public ?string $formatted_total = null;

    /**
     * The base total.
     */
    public ?float $base_total = null;

    /**
     * Row total including tax (base currency) — shown on the admin invoice/refund view.
     */
    public ?float $base_total_incl_tax = null;

    /**
     * Product base image URL — shown beside each line item on the admin view.
     */
    public ?string $base_image_url = null;

    /**
     * The tax amount.
     */
    public ?float $tax_amount = null;

    /**
     * The formatted tax amount.
     */
    public ?string $formatted_tax_amount = null;

    /**
     * The discount amount.
     */
    public ?float $discount_amount = null;

    /**
     * The formatted discount amount.
     */
    public ?string $formatted_discount_amount = null;

    /**
     * The product id.
     */
    public ?int $product_id = null;

    /**
     * The product type.
     */
    public ?string $product_type = null;

    /**
     * The additional.
     */
    public ?array $additional = null;
}
