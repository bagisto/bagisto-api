<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminOrderDetailRefund',
    operations: [],
    graphQlOperations: [],

    normalizationContext: ['attributes' => [
        'id', 'state', 'total_qty', 'grand_total', 'base_grand_total', 'created_at',
    ]],
)]
class AdminOrderDetailRefund extends Model
{
    /**
     * The table that stores the refunds.
     *
     * @var string
     */
    protected $table = 'refunds';

    /**
     * The native types the order refund attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'total_qty' => 'int',
        'grand_total' => 'float',
        'base_grand_total' => 'float',
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
