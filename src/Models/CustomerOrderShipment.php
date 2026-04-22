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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CustomerOrderShipmentProvider;
use Webkul\Sales\Models\Order;

/**
 * Customer Order Shipment API Resource
 *
 * Standalone API resource for shipments belonging to authenticated customer's orders.
 * Provides shipment details including tracking numbers, items, addresses, and payment info.
 * Customer-scoped via order relationship — customers can only access their own shipments.
 *
 * Relationships:
 * - order: The parent order
 * - items: Shipment line items
 * - shippingAddress: Destination address for the shipment
 * - billingAddress: Billing address from the order
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerOrderShipment',
    uriTemplate: '/customer-order-shipments',
    operations: [
        new GetCollection(
            uriTemplate: '/customer-order-shipments',
            provider: CustomerOrderShipmentProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(tags: ['Customer Order']),
        ),
        new Get(
            uriTemplate: '/customer-order-shipments/{id}',
            provider: CustomerOrderShipmentProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(tags: ['Customer Order']),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: BaseQueryItemResolver::class,
        ),
        new QueryCollection(
            provider: CustomerOrderShipmentProvider::class,
            paginationType: 'cursor',
            args: [
                'orderId' => ['type' => 'Int', 'description' => 'Filter shipments by order ID'],
                'status'  => ['type' => 'String', 'description' => 'Filter shipments by status (pending, shipped, canceled)'],
                'first'   => ['type' => 'Int', 'description' => 'Number of items to return from the start'],
                'last'    => ['type' => 'Int', 'description' => 'Number of items to return from the end'],
                'after'   => ['type' => 'String', 'description' => 'Cursor to start pagination after'],
                'before'  => ['type' => 'String', 'description' => 'Cursor to start pagination before'],
            ],
        ),
    ],
)]
class CustomerOrderShipment extends Model
{
    /** @var string */
    protected $table = 'shipments';

    /** @var array */
    protected $casts = [
        'id'                      => 'int',
        'order_id'                => 'int',
        'total_qty'               => 'int',
        'total_weight'            => 'float',
        'email_sent'              => 'boolean',
        'customer_id'             => 'int',
        'order_address_id'        => 'int',
        'inventory_source_id'     => 'int',
        'created_at'              => 'datetime',
        'updated_at'              => 'datetime',
    ];

    /** @var array */
    protected $appends = ['shippingNumber'];

    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get shipment number (for display - uses ID as shipment number)
     */
    #[ApiProperty(writable: false)]
    public function getShippingNumberAttribute(): string
    {
        return '#'.$this->id;
    }

    /**
     * Get tracking number
     */
    #[ApiProperty(writable: false)]
    public function getTrackingNumber(): ?string
    {
        return $this->track_number;
    }

    /**
     * Get carrier title
     */
    #[ApiProperty(writable: false)]
    public function getCarrierTitle(): ?string
    {
        return $this->carrier_title;
    }

    /**
     * Get carrier code
     */
    #[ApiProperty(writable: false)]
    public function getCarrierCode(): ?string
    {
        return $this->carrier_code;
    }

    /**
     * Get total quantity  shipped
     */
    #[ApiProperty(writable: false)]
    public function getTotalQty(): ?int
    {
        return $this->total_qty;
    }

    /**
     * Get total weight shipped
     */
    #[ApiProperty(writable: false)]
    public function getTotalWeight(): ?float
    {
        return $this->total_weight;
    }

    /**
     * Get the order that owns this shipment
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get shipment items
     */
    #[ApiProperty(writable: false)]
    public function items(): HasMany
    {
        return $this->hasMany(CustomerOrderShipmentItem::class, 'shipment_id');
    }

    /**
     * Get the shipping address for this shipment
     */
    #[ApiProperty(writable: false)]
    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerOrderAddress::class, 'order_address_id', 'id');
    }

    /**
     * Get the billing address from the parent order
     */
    #[ApiProperty(writable: false)]
    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerOrderAddress::class, 'order_id', 'order_id')
            ->where('address_type', 'order_billing');
    }

    /**
     * Get order payment method info
     */
    #[ApiProperty(writable: false)]
    public function getPaymentMethodTitle(): ?string
    {
        return $this->order?->payment?->method_title;
    }

    /**
     * Get order shipping method info
     */
    #[ApiProperty(writable: false)]
    public function getShippingMethodTitle(): ?string
    {
        return $this->order?->shipping_title;
    }

    /**
     * Override toArray to ensure custom accessors are included
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['shippingNumber'] = $this->shippingNumber;
        $array['trackingNumber'] = $this->track_number;
        $array['carrierTitle'] = $this->carrier_title;
        $array['carrierCode'] = $this->carrier_code;
        $array['totalQty'] = $this->total_qty;
        $array['totalWeight'] = $this->total_weight;
        $array['paymentMethodTitle'] = $this->getPaymentMethodTitle();
        $array['shippingMethodTitle'] = $this->getShippingMethodTitle();

        // Set addresses to null if not found
        if (! array_key_exists('shippingAddress', $array) || ! $this->shippingAddress) {
            $array['shippingAddress'] = null;
        }
        if (! array_key_exists('billingAddress', $array) || ! $this->billingAddress) {
            $array['billingAddress'] = null;
        }

        return $array;
    }
}
