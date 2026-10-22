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

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerOrderShipmentItem',
    operations: [
        new Get(
            uriTemplate: '/customer-order-shipment-items/{id}',
            provider: CustomerOrderShipmentItemProvider::class,
            openapi: new Operation(
                tags: ['Customer Order'],
                summary: 'Get a shipped item of the customer',
                description: 'One shipped item of the authenticated customer\'s shipments; any other item or an unknown ID is a 404. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
            ),
        ),
        new GetCollection(
            uriTemplate: '/customer-order-shipment-items',
            provider: CustomerOrderShipmentItemProvider::class,
            openapi: new Operation(
                tags: ['Customer Order'],
                summary: 'List the customer\'s shipped items',
                description: 'Shipped items across the authenticated customer\'s shipments, newest first. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: BaseQueryItemResolver::class,
            description: 'One shipped item of the authenticated customer\'s shipments; any other item is not found. Pass the IRI or the numeric ID as `id`; an unknown ID is an error. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
        ),
        new QueryCollection(
            provider: CustomerOrderShipmentItemProvider::class,
            description: 'Shipped items across the authenticated customer\'s shipments, newest first. Paginate forward only: `first` (default 30, between 1 and 50) and `after`; `last` and `before` are ignored. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
        ),
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
