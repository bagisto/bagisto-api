<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Customer Invoice Item — nested API resource (no standalone endpoints).
 *
 * Exposed only as a relationship of CustomerInvoice.
 * Points to the `invoice_items` table with explicit field casts.
 */
#[ApiResource(
    shortName: 'CustomerInvoiceItem',
    operations: [],
    graphQlOperations: [],
)]
class CustomerInvoiceItem extends Model
{
    /** @var string */
    protected $table = 'invoice_items';

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'invoice_id' => 'int',
        'order_item_id' => 'int',
        'parent_id' => 'int',
        'sku' => 'string',
        'name' => 'string',
        'description' => 'string',
        'qty' => 'int',
        'price' => 'float',
        'base_price' => 'float',
        'total' => 'float',
        'base_total' => 'float',
        'tax_amount' => 'float',
        'base_tax_amount' => 'float',
        'discount_percent' => 'float',
        'discount_amount' => 'float',
        'base_discount_amount' => 'float',
        'additional' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * toArray override to ensure all properties are included
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        // Ensure all fields are explicitly included for GraphQL
        $array['id'] = $this->id;
        $array['sku'] = $this->sku;
        $array['name'] = $this->name;
        $array['qty'] = $this->qty;
        $array['price'] = $this->price;
        $array['base_price'] = $this->base_price;
        $array['total'] = $this->total;
        $array['base_total'] = $this->base_total;
        $array['tax_amount'] = $this->tax_amount;
        $array['discount_amount'] = $this->discount_amount;

        return $array;
    }
}
