<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Webkul\Product\Models\Product;
use Webkul\Sales\Models\OrderItem;

#[ApiResource(
    shortName: 'AdminInvoiceItem',
    operations: [],
    graphQlOperations: [],

    normalizationContext: ['attributes' => [
        'id', 'order_item_id', 'sku', 'name', 'qty', 'price', 'formatted_price',
        'base_price', 'base_price_incl_tax', 'total', 'formatted_total', 'base_total',
        'base_total_incl_tax', 'tax_amount', 'formatted_tax_amount', 'discount_amount',
        'formatted_discount_amount', 'product_id', 'product_type', 'base_image_url', 'additional',
    ]],
)]
class AdminInvoiceItem extends Model
{
    /** @var string */
    protected $table = 'invoice_items';

    /** @var array */
    protected $appends = [
        'formatted_price', 'formatted_total', 'formatted_tax_amount',
        'formatted_discount_amount', 'product_type', 'base_image_url',
    ];

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'order_item_id' => 'int',
        'qty' => 'int',
        'price' => 'float',
        'base_price' => 'float',
        'base_price_incl_tax' => 'float',
        'total' => 'float',
        'base_total' => 'float',
        'base_total_incl_tax' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'product_id' => 'int',
        'additional' => 'array',
    ];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get formatted price attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedPriceAttribute(): ?string
    {
        return core()->formatBasePrice((float) $this->price);
    }

    /**
     * Get formatted total attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedTotalAttribute(): ?string
    {
        return core()->formatBasePrice((float) $this->total);
    }

    /**
     * Get formatted tax amount attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedTaxAmountAttribute(): ?string
    {
        return core()->formatBasePrice((float) $this->tax_amount);
    }

    /**
     * Get formatted discount amount attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedDiscountAmountAttribute(): ?string
    {
        return core()->formatBasePrice((float) $this->discount_amount);
    }

    /**
     * The `product_type` column is the polymorphic morph class; resolve the real
     * catalog type from the order item.
     */
    #[ApiProperty(writable: false)]
    public function getProductTypeAttribute(): ?string
    {
        try {
            if ($type = OrderItem::find($this->order_item_id)?->type) {
                return $type;
            }
        } catch (\Throwable) {
        }

        $raw = $this->attributes['product_type'] ?? null;

        return ($raw && ! str_contains((string) $raw, '\\')) ? $raw : null;
    }

    /**
     * Get base image url attribute.
     */
    #[ApiProperty(writable: false)]
    public function getBaseImageUrlAttribute(): ?string
    {
        try {
            return Product::find($this->product_id)?->base_image_url;
        } catch (\Throwable) {
            return null;
        }
    }
}
