<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'CustomerOrderItem',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'qty_ordered', 'qty_shipped', 'qty_invoiced', 'qty_canceled', 'qty_refunded', 'sku', 'name', 'price', 'base_price', 'total', 'base_total', 'type']],
)]
class CustomerOrderItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_items';

    /**
     * The quantity accessors appended to the item's array form.
     *
     * @var array
     */
    protected $appends = ['qtyOrdered', 'qtyShipped', 'qtyInvoiced', 'qtyCanceled', 'qtyRefunded'];

    /**
     * The attributes cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'sku' => 'string',
        'type' => 'string',
        'name' => 'string',
        'coupon_code' => 'string',
        'weight' => 'float',
        'total_weight' => 'float',
        'qty_ordered' => 'int',
        'qty_shipped' => 'int',
        'qty_invoiced' => 'int',
        'qty_canceled' => 'int',
        'qty_refunded' => 'int',
        'price' => 'float',
        'base_price' => 'float',
        'total' => 'float',
        'base_total' => 'float',
        'total_invoiced' => 'float',
        'base_total_invoiced' => 'float',
        'amount_refunded' => 'float',
        'base_amount_refunded' => 'float',
        'discount_percent' => 'float',
        'discount_amount' => 'float',
        'base_discount_amount' => 'float',
        'discount_invoiced' => 'float',
        'base_discount_invoiced' => 'float',
        'discount_refunded' => 'float',
        'base_discount_refunded' => 'float',
        'tax_percent' => 'float',
        'tax_amount' => 'float',
        'base_tax_amount' => 'float',
        'tax_amount_invoiced' => 'float',
        'base_tax_amount_invoiced' => 'float',
        'tax_amount_refunded' => 'float',
        'base_tax_amount_refunded' => 'float',
        'price_incl_tax' => 'float',
        'base_price_incl_tax' => 'float',
        'total_incl_tax' => 'float',
        'base_total_incl_tax' => 'float',
        'product_id' => 'int',
        'product_type' => 'string',
        'order_id' => 'int',
        'tax_category_id' => 'int',
        'parent_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * API Platform identifier.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get sku.
     */
    #[ApiProperty(writable: false)]
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * Get name.
     */
    #[ApiProperty(writable: false)]
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get qty ordered.
     */
    #[ApiProperty(writable: false)]
    public function getQtyOrdered(): ?int
    {
        return $this->qty_ordered;
    }

    /**
     * Get qty shipped.
     */
    #[ApiProperty(writable: false)]
    public function getQtyShipped(): ?int
    {
        return $this->qty_shipped;
    }

    /**
     * Get qty invoiced.
     */
    #[ApiProperty(writable: false)]
    public function getQtyInvoiced(): ?int
    {
        return $this->qty_invoiced;
    }

    /**
     * Get qty canceled.
     */
    #[ApiProperty(writable: false)]
    public function getQtyCanceled(): ?int
    {
        return $this->qty_canceled;
    }

    /**
     * Get qty refunded.
     */
    #[ApiProperty(writable: false)]
    public function getQtyRefunded(): ?int
    {
        return $this->qty_refunded;
    }

    /**
     * Get type.
     */
    #[ApiProperty(writable: false)]
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Get price.
     */
    #[ApiProperty(writable: false)]
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Get base price.
     */
    #[ApiProperty(writable: false)]
    public function getBasePrice(): ?float
    {
        return $this->base_price;
    }

    /**
     * Get total.
     */
    #[ApiProperty(writable: false)]
    public function getTotal(): ?float
    {
        return $this->total;
    }

    /**
     * Get base total.
     */
    #[ApiProperty(writable: false)]
    public function getBaseTotal(): ?float
    {
        return $this->base_total;
    }

    /**
     * Override toArray to ensure qty fields are always included in serialization.
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        $array['qty_ordered'] = $this->qty_ordered;
        $array['qty_shipped'] = $this->qty_shipped;
        $array['qty_invoiced'] = $this->qty_invoiced;
        $array['qty_canceled'] = $this->qty_canceled;
        $array['qty_refunded'] = $this->qty_refunded;

        return $array;
    }
}
