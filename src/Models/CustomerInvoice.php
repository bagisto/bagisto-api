<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CustomerInvoiceProvider;
use Webkul\Sales\Models\Order;

/**
 * Customer Invoice API Resource
 *
 * Returns invoices belonging to authenticated customer's orders.
 * Read-only, customer-scoped via order relationship.
 * Supports PDF download via separate route.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerInvoice',
    uriTemplate: '/customer-invoices',
    operations: [
        new GetCollection(
            uriTemplate: '/customer-invoices',
            provider: CustomerInvoiceProvider::class,
        ),
        new Get(
            uriTemplate: '/customer-invoices/{id}',
            provider: CustomerInvoiceProvider::class,
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: BaseQueryItemResolver::class,
        ),
        new QueryCollection(
            provider: CustomerInvoiceProvider::class,
            paginationType: 'cursor',
            args: [
                'orderId' => ['type' => 'Int', 'description' => 'Filter invoices by order ID'],
                'state'   => ['type' => 'String', 'description' => 'Filter invoices by state (pending, pending_payment, paid, overdue, refunded)'],
                'first'   => ['type' => 'Int', 'description' => 'Number of items to return from the start'],
                'last'    => ['type' => 'Int', 'description' => 'Number of items to return from the end'],
                'after'   => ['type' => 'String', 'description' => 'Cursor to start pagination after'],
                'before'  => ['type' => 'String', 'description' => 'Cursor to start pagination before'],
            ],
        ),
    ],
)]
class CustomerInvoice extends Model
{
    /** @var string */
    protected $table = 'invoices';

    /** @var array */
    protected $casts = [
        'id'                            => 'int',
        'order_id'                      => 'int',
        'total_qty'                     => 'int',
        'email_sent'                    => 'boolean',
        'sub_total'                     => 'float',
        'base_sub_total'                => 'float',
        'grand_total'                   => 'float',
        'base_grand_total'              => 'float',
        'shipping_amount'               => 'float',
        'base_shipping_amount'          => 'float',
        'tax_amount'                    => 'float',
        'base_tax_amount'               => 'float',
        'discount_amount'               => 'float',
        'base_discount_amount'          => 'float',
        'shipping_tax_amount'           => 'float',
        'base_shipping_tax_amount'      => 'float',
        'sub_total_incl_tax'            => 'float',
        'base_sub_total_incl_tax'       => 'float',
        'shipping_amount_incl_tax'      => 'float',
        'base_shipping_amount_incl_tax' => 'float',
        'reminders'                     => 'int',
    ];

    /** @var array */
    protected $appends = ['downloadUrl'];

    /**
     * Download URL for the invoice PDF
     * Using Eloquent accessor pattern so it's automatically included in toArray()
     * Routes through BagistoApi endpoint with bearer token authentication
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('bagistoapi.customer-invoice-pdf', ['id' => $this->id]);
    }

    /**
     * Get the order that belongs to the invoice.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the invoice items (top-level only).
     * Returns HasMany relationship for queries, but provides array access via toArray()
     */
    public function items(): HasMany
    {
        return $this->hasMany(CustomerInvoiceItem::class, 'invoice_id')
            ->whereNull('parent_id');
    }

    /**
     * Get the billing and shipping addresses for the invoice.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerInvoiceAddress::class, 'order_id', 'order_id')
            ->whereIn('address_type', ['order_billing', 'order_shipping']);
    }

    /**
     * Ensure downloadUrl is included in array serialization for GraphQL
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['downloadUrl'] = $this->downloadUrl;

        return $array;
    }
}
