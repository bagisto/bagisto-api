<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Slim shipment summary — nested sub-resource of AdminOrderDetail. For the full
 * shipment use GET /api/admin/shipments/{id}.
 */
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
    /** @var string */
    protected $table = 'shipments';

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'total_qty' => 'int',
        'total_weight' => 'float',
        'email_sent' => 'boolean',
        'created_at' => 'datetime',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
