<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminOrderDetailShipment',
    operations: [],
    graphQlOperations: [],

    normalizationContext: ['attributes' => [
        'id', 'status', 'total_qty', 'total_weight', 'carrier_code', 'carrier_title',
        'track_number', 'email_sent', 'inventory_source_name', 'created_at',
    ]],
)]
class AdminOrderDetailShipment extends Model
{
    /**
     * The table that stores the shipments.
     *
     * @var string
     */
    protected $table = 'shipments';

    /**
     * The native types the order shipment attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'total_qty' => 'int',
        'total_weight' => 'float',
        'email_sent' => 'boolean',
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
