<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CustomerOrderShipmentItemProvider;
use Webkul\Sales\Models\OrderItem;

/**
 * Customer Order Shipment Item API Resource
 *
 * Nested API resource for items in a customer's order shipment.
 * Contains product details and quantity shipped.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerOrderShipmentItem',
    operations: [
        new Get(
            uriTemplate: '/customer-order-shipment-items/{id}',
            provider: CustomerOrderShipmentItemProvider::class,
            openapi: new Operation(tags: ['Customer Order']),
        ),
        new GetCollection(
            uriTemplate: '/customer-order-shipment-items',
            provider: CustomerOrderShipmentItemProvider::class,
            openapi: new Operation(tags: ['Customer Order']),
        ),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection(provider: CustomerOrderShipmentItemProvider::class),
    ],
)]
class CustomerOrderShipmentItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shipment_items';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'shipment_id' => 'int',
        'order_item_id' => 'int',
        'qty' => 'int',
        'weight' => 'float',
        'child' => 'boolean',
        'additional' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the shipment item identifier used by API Platform.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the SKU of the shipped item.
     */
    #[ApiProperty(writable: false)]
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * Get the product name of the shipped item.
     */
    #[ApiProperty(writable: false)]
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the description of the shipped item.
     */
    #[ApiProperty(writable: false)]
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get the quantity shipped.
     */
    #[ApiProperty(writable: false)]
    public function getQty(): ?int
    {
        return $this->qty;
    }

    /**
     * Get the weight of the shipped item.
     */
    #[ApiProperty(writable: false)]
    public function getWeight(): ?float
    {
        return $this->weight;
    }

    /**
     * Get the shipment this item belongs to.
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(CustomerOrderShipment::class, 'shipment_id');
    }

    /**
     * Get the order item this shipment item references.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    /**
     * Convert the item to an array, always including SKU, name, description, quantity and weight.
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
