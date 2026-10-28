<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CustomerInvoiceProvider;
use Webkul\Sales\Models\Order;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerInvoice',
    uriTemplate: '/customer-invoices',
    operations: [
        new GetCollection(
            uriTemplate: '/customer-invoices',
            provider: CustomerInvoiceProvider::class,
            openapi: new Operation(
                tags: ['Customer Order'],
                summary: 'List invoices for the authenticated customer',
                description: 'Mirrors the GraphQL `customerInvoices(orderId:)` query. Use `?order_id=N` to scope to a specific order, `?state=paid` (or pending, pending_payment, overdue, refunded) to filter by invoice state.',
                parameters: [
                    new Parameter(
                        name: 'order_id',
                        in: 'query',
                        description: 'Return only invoices for this order ID. Accepts `orderId` as an alias.',
                        required: false,
                        schema: [
                            'type' => 'integer',
                            'example' => 590,
                        ],
                    ),
                    new Parameter(
                        name: 'state',
                        in: 'query',
                        description: 'Filter by invoice state (pending, pending_payment, paid, overdue, refunded).',
                        required: false,
                        schema: ['type' => 'string'],
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/customer-invoices/{id}',
            provider: CustomerInvoiceProvider::class,
            openapi: new Operation(
                tags: ['Customer Order'],
                summary: 'Get an invoice of the customer',
                description: 'One invoice of an order placed by the authenticated customer; any other invoice or an unknown ID is a 404. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: BaseQueryItemResolver::class,
            description: 'One invoice of an order placed by the authenticated customer; any other invoice is not found. Pass the IRI or the numeric ID as `id`; an unknown ID is an error. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
        ),
        new QueryCollection(
            provider: CustomerInvoiceProvider::class,
            description: 'Invoices of the authenticated customer\'s orders, newest first. Filters: `orderId`, and `state` (paid, pending, pending_payment, overdue or refunded). Cursor pagination: `first` takes the first N rows (after `after` when given), `last` without `before` takes the last N, and `before` takes the N rows before that cursor; N defaults to 10 and is capped at 50, and `first: 0` returns an empty page. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
            paginationType: 'cursor',
            args: [
                'orderId' => [
                    'type' => 'Int',
                    'description' => 'Filter invoices by order ID',
                ],

                'state' => [
                    'type' => 'String',
                    'description' => 'Filter invoices by state (pending, pending_payment, paid, overdue, refunded)',
                ],

                'first' => [
                    'type' => 'Int',
                    'description' => 'Number of items to return from the start',
                ],

                'last' => [
                    'type' => 'Int',
                    'description' => 'Number of items to return from the end',
                ],

                'after' => [
                    'type' => 'String',
                    'description' => 'Cursor to start pagination after',
                ],

                'before' => [
                    'type' => 'String',
                    'description' => 'Cursor to start pagination before',
                ],
            ],
        ),
    ],
)]
class CustomerInvoice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoices';

    /**
     * The attributes cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'order_id' => 'int',
        'total_qty' => 'int',
        'email_sent' => 'boolean',
        'sub_total' => 'float',
        'base_sub_total' => 'float',
        'grand_total' => 'float',
        'base_grand_total' => 'float',
        'shipping_amount' => 'float',
        'base_shipping_amount' => 'float',
        'tax_amount' => 'float',
        'base_tax_amount' => 'float',
        'discount_amount' => 'float',
        'base_discount_amount' => 'float',
        'shipping_tax_amount' => 'float',
        'base_shipping_tax_amount' => 'float',
        'sub_total_incl_tax' => 'float',
        'base_sub_total_incl_tax' => 'float',
        'shipping_amount_incl_tax' => 'float',
        'base_shipping_amount_incl_tax' => 'float',
        'reminders' => 'int',
    ];

    /**
     * The accessors appended to the invoice's array form.
     *
     * @var array
     */
    protected $appends = ['downloadUrl'];

    /**
     * Download URL for the invoice PDF
     * Using Eloquent accessor pattern so it's automatically included in toArray().
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('bagistoapi.customer_invoice_pdf', ['id' => $this->id]);
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
     * Returns HasMany relationship for queries, but provides array access via toArray().
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
     * Ensure downloadUrl is included in array serialization for GraphQL.
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['downloadUrl'] = $this->downloadUrl;

        return $array;
    }
}
