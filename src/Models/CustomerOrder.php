<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Webkul\BagistoApi\State\CustomerOrderProvider;

/**
 * Customer Order API Resource
 *
 * Standalone model pointing to the `orders` table with explicit field casts
 * and hand-picked relationships. Follows the same pattern as CustomerInvoice
 * to avoid inheriting unwanted relationships from the base Order model
 * (morphTo fields, proxy relationships) that can break API Platform serialization.
 *
 * This is a read-only, customer-scoped resource.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerOrder',
    uriTemplate: '/customer-orders',
    operations: [
        new GetCollection(
            uriTemplate: '/customer-orders',
            provider: CustomerOrderProvider::class,
        ),
        new Get(
            uriTemplate: '/customer-orders/{id}',
            provider: CustomerOrderProvider::class,
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: CustomerOrderProvider::class,
        ),
        new QueryCollection(
            provider: CustomerOrderProvider::class,
            paginationType: 'cursor',
            args: [
                'status' => ['type' => 'String', 'description' => 'Filter orders by status (pending, processing, completed, canceled, closed, fraud)'],
                'first'  => ['type' => 'Int', 'description' => 'Number of items to return from the start'],
                'last'   => ['type' => 'Int', 'description' => 'Number of items to return from the end'],
                'after'  => ['type' => 'String', 'description' => 'Cursor to start pagination after'],
                'before' => ['type' => 'String', 'description' => 'Cursor to start pagination before'],
            ],
        ),
    ],
)]
class CustomerOrder extends Model
{
    /** @var string */
    protected $table = 'orders';

    /** @var array */
    protected $casts = [
        'id'                            => 'int',
        'increment_id'                  => 'string',
        'status'                        => 'string',
        'channel_name'                  => 'string',
        'is_guest'                      => 'boolean',
        'customer_email'                => 'string',
        'customer_first_name'           => 'string',
        'customer_last_name'            => 'string',
        'shipping_method'               => 'string',
        'shipping_title'                => 'string',
        'shipping_description'          => 'string',
        'coupon_code'                   => 'string',
        'is_gift'                       => 'boolean',
        'total_item_count'              => 'int',
        'total_qty_ordered'             => 'int',
        'base_currency_code'            => 'string',
        'channel_currency_code'         => 'string',
        'order_currency_code'           => 'string',
        'grand_total'                   => 'float',
        'base_grand_total'              => 'float',
        'grand_total_invoiced'          => 'float',
        'base_grand_total_invoiced'     => 'float',
        'grand_total_refunded'          => 'float',
        'base_grand_total_refunded'     => 'float',
        'sub_total'                     => 'float',
        'base_sub_total'                => 'float',
        'sub_total_invoiced'            => 'float',
        'base_sub_total_invoiced'       => 'float',
        'sub_total_refunded'            => 'float',
        'base_sub_total_refunded'       => 'float',
        'discount_percent'              => 'float',
        'discount_amount'               => 'float',
        'base_discount_amount'          => 'float',
        'discount_invoiced'             => 'float',
        'base_discount_invoiced'        => 'float',
        'discount_refunded'             => 'float',
        'base_discount_refunded'        => 'float',
        'tax_amount'                    => 'float',
        'base_tax_amount'               => 'float',
        'tax_amount_invoiced'           => 'float',
        'base_tax_amount_invoiced'      => 'float',
        'tax_amount_refunded'           => 'float',
        'base_tax_amount_refunded'      => 'float',
        'shipping_amount'               => 'float',
        'base_shipping_amount'          => 'float',
        'shipping_invoiced'             => 'float',
        'base_shipping_invoiced'        => 'float',
        'shipping_refunded'             => 'float',
        'base_shipping_refunded'        => 'float',
        'shipping_discount_amount'      => 'float',
        'base_shipping_discount_amount' => 'float',
        'shipping_tax_amount'           => 'float',
        'base_shipping_tax_amount'      => 'float',
        'shipping_tax_refunded'         => 'float',
        'base_shipping_tax_refunded'    => 'float',
        'sub_total_incl_tax'            => 'float',
        'base_sub_total_incl_tax'       => 'float',
        'shipping_amount_incl_tax'      => 'float',
        'base_shipping_amount_incl_tax' => 'float',
        'customer_id'                   => 'int',
        'channel_id'                    => 'int',
        'applied_cart_rule_ids'         => 'string',
        'created_at'                    => 'datetime',
        'updated_at'                    => 'datetime',
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
     * Customer full name computed from first + last name.
     */
    #[ApiProperty(writable: false, description: 'Customer full name')]
    public function getCustomerFullNameAttribute(): string
    {
        return $this->customer_first_name.' '.$this->customer_last_name;
    }

    /**
     * Get the top-level order items (excludes child items).
     */
    #[ApiProperty(writable: false)]
    public function items(): HasMany
    {
        return $this->hasMany(CustomerOrderItem::class, 'order_id')
            ->whereNull('parent_id');
    }

    /**
     * Get the order addresses (billing + shipping).
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerOrderAddress::class, 'order_id')
            ->whereIn('address_type', ['order_billing', 'order_shipping']);
    }

    /**
     * Get the payment record for the order.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(CustomerOrderPayment::class, 'order_id');
    }

    /**
     * Get the shipments for this order.
     */
    #[ApiProperty(writable: false)]
    public function shipments(): HasMany
    {
        return $this->hasMany(CustomerOrderShipment::class, 'order_id');
    }
}
