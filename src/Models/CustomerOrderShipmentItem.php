<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Customer Order Shipment Item API Resource
 *
 * Nested API resource for items in a customer's order shipment.
 * Contains product details and quantity shipped.
 */
#[ApiResource(
    shortName: 'CustomerOrderShipmentItem',
    operations: [
        new Get,
        new GetCollection,
    ],
    graphQlOperations: [
        new Query,
        new QueryCollection,
    ],
)]
class CustomerOrderShipmentItem extends Model
{
    /** @var string */
    protected $table = 'shipment_items';

    /** @var array */
    protected $casts = [
        'id'                => 'int',
        'shipment_id'       => 'int',
        'order_item_id'     => 'int',
        'qty'               => 'int',
        'weight'            => 'float',
        'child'             => 'boolean',
        'additional'        => 'array',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get SKU
     */
    #[ApiProperty(writable: false)]
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * Get product name
     */
    #[ApiProperty(writable: false)]
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get description
     */
    #[ApiProperty(writable: false)]
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get quantity shipped
     */
    #[ApiProperty(writable: false)]
    public function getQty(): ?int
    {
        return $this->qty;
    }

    /**
     * Get item weight
     */
    #[ApiProperty(writable: false)]
    public function getWeight(): ?float
    {
        return $this->weight;
    }

    /**
     * Get the shipment this item belongs to
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(CustomerOrderShipment::class, 'shipment_id');
    }

    /**
     * Get the order item this shipment item references
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Sales\Models\OrderItem::class, 'order_item_id');
    }

    /**
     * Override toArray to force field inclusion
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['sku'] = $this->sku;
        $array['name'] = $this->name;
        $array['description'] = $this->description;
        $array['qty'] = $this->qty;
        $array['weight'] = $this->weight;

        return $array;
    }
}
