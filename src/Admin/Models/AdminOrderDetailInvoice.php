<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminOrderDetailInvoice',
    operations: [],
    graphQlOperations: [],

    normalizationContext: ['attributes' => [
        'id', 'increment_id', 'state', 'email_sent', 'total_qty',
        'sub_total', 'grand_total', 'tax_amount', 'discount_amount', 'shipping_amount',
        'transaction_id', 'created_at',
    ]],
)]
class AdminOrderDetailInvoice extends Model
{
    /**
     * The table that stores the invoices.
     *
     * @var string
     */
    protected $table = 'invoices';

    /**
     * The native types the order invoice attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'email_sent' => 'boolean',
        'total_qty' => 'int',
        'sub_total' => 'float',
        'grand_total' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'shipping_amount' => 'float',
        'created_at' => 'datetime',
    ];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
