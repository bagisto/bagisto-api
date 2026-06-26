<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\OrderDetailRestDto;
use Webkul\BagistoApi\Admin\State\OrderDetailProvider;

/**
 * Admin Order detail — connection-shape resource (2026-06-18).
 *
 * Bare Eloquent model on the `orders` table (NOT extending core Order, which
 * carries morphTo/proxy relations that break API Platform serialization —
 * same reason the shop CustomerOrder is bare). Nested data follows the proven
 * AdminReview recipe:
 *   - to-many (items / invoices / shipments / refunds / comments) → HasMany to
 *     Eloquent sub-resources → GraphQL connections (`items { edges { node } }`).
 *   - to-one (customer / billingAddress / shippingAddress) → BelongsTo/HasOne
 *     to Eloquent sub-resources → typed objects the client sub-selects.
 *   - top-level scalars + formatted strings resolve via snake_case columns and
 *     accessors through the central output converter (no AcceptsCamelCaseWrites).
 *
 * REST keeps the historical flat shape via `output: OrderDetailRestDto` (the
 * provider maps it); GraphQL ops carry no `output:` so they return THIS model
 * and serve connections. The provider branches on the GraphQL context.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminOrderDetail',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            uriTemplate: '/orders/{id}',
            requirements: ['id' => '\d+'],
            provider: OrderDetailProvider::class,
            output: OrderDetailRestDto::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Get order detail',
                description: 'Full order-view payload — flat order fields plus embedded customer, billing/shipping addresses, items, invoices, shipments, refunds, and comments. Over GraphQL the nested collections are field-selectable connections (items { edges { node } }) and the to-one objects are typed (customer { name }); over REST they are flat arrays/objects.',
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: OrderDetailProvider::class,
            description: 'Full detail of a single order. Nested collections are connections — query items { edges { node { sku qtyOrdered } } }; to-one objects are typed — query customer { name email } and billingAddress { city }.',
        ),
    ],
)]
class OrderDetail extends EloquentModel
{
    /** @var string */
    protected $table = 'orders';

    /** @var array */
    protected $appends = [
        'status_label', 'payment_method', 'payment_title', 'total_due', 'base_total_due',
        'formatted_grand_total', 'formatted_sub_total', 'formatted_tax_amount',
        'formatted_discount_amount', 'formatted_shipping_amount', 'formatted_total_due',
        'formatted_grand_total_invoiced', 'formatted_grand_total_refunded',
    ];

    /** @var array */
    protected $casts = [
        'id'                   => 'int',
        'is_guest'             => 'boolean',
        'is_gift'              => 'boolean',
        'total_item_count'     => 'int',
        'total_qty_ordered'    => 'int',
        'grand_total'          => 'float',
        'base_grand_total'     => 'float',
        'grand_total_invoiced' => 'float',
        'grand_total_refunded' => 'float',
        'sub_total'            => 'float',
        'base_sub_total'       => 'float',
        'tax_amount'           => 'float',
        'discount_amount'      => 'float',
        'shipping_amount'      => 'float',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];

    /** Status code → label (mirrors core Order::$statusLabel). */
    private const STATUS_LABELS = [
        'pending'         => 'Pending',
        'pending_payment' => 'Pending Payment',
        'processing'      => 'Processing',
        'completed'       => 'Completed',
        'canceled'        => 'Canceled',
        'closed'          => 'Closed',
        'fraud'           => 'Fraud',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    // --- Nested relations (GraphQL connections / typed objects) ---

    #[ApiProperty(writable: false)]
    public function items(): HasMany
    {
        return $this->hasMany(AdminOrderDetailItem::class, 'order_id')->whereNull('parent_id');
    }

    #[ApiProperty(writable: false)]
    public function invoices(): HasMany
    {
        return $this->hasMany(AdminOrderDetailInvoice::class, 'order_id');
    }

    #[ApiProperty(writable: false)]
    public function shipments(): HasMany
    {
        return $this->hasMany(AdminOrderDetailShipment::class, 'order_id');
    }

    #[ApiProperty(writable: false)]
    public function refunds(): HasMany
    {
        return $this->hasMany(AdminOrderDetailRefund::class, 'order_id');
    }

    #[ApiProperty(writable: false)]
    public function comments(): HasMany
    {
        return $this->hasMany(AdminOrderDetailComment::class, 'order_id')->orderByDesc('id');
    }

    #[ApiProperty(writable: false)]
    public function customer(): BelongsTo
    {
        return $this->belongsTo(AdminOrderDetailCustomer::class, 'customer_id');
    }

    /**
     * Order addresses (billing + shipping) → connection. Each node carries
     * `addressType` so the client can tell them apart. A constrained HasOne
     * (filtered by address_type) cannot resolve as a typed to-one over GraphQL
     * (API Platform 500s on the relation's runtime `where`), so both addresses
     * are exposed in one connection. REST keeps separate billingAddress /
     * shippingAddress objects via the output DTO.
     */
    #[ApiProperty(writable: false)]
    public function addresses(): HasMany
    {
        return $this->hasMany(AdminOrderDetailAddress::class, 'order_id')
            ->whereIn('address_type', ['order_billing', 'order_shipping']);
    }

    // --- Computed scalars (appended; resolve over both transports) ---

    #[ApiProperty(writable: false)]
    public function getStatusLabelAttribute(): ?string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    #[ApiProperty(writable: false)]
    public function getPaymentMethodAttribute(): ?string
    {
        return $this->paymentMethodValue();
    }

    #[ApiProperty(writable: false)]
    public function getPaymentTitleAttribute(): ?string
    {
        $method = $this->paymentMethodValue();

        if (! $method) {
            return null;
        }

        return core()->getConfigData('sales.payment_methods.'.$method.'.title') ?: $method;
    }

    #[ApiProperty(writable: false)]
    public function getTotalDueAttribute(): float
    {
        return (float) $this->grand_total - (float) $this->grand_total_invoiced;
    }

    #[ApiProperty(writable: false)]
    public function getBaseTotalDueAttribute(): float
    {
        return (float) $this->base_grand_total - (float) $this->base_grand_total_invoiced;
    }

    #[ApiProperty(writable: false)]
    public function getFormattedGrandTotalAttribute(): ?string
    {
        return $this->fmt($this->grand_total);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedSubTotalAttribute(): ?string
    {
        return $this->fmt($this->sub_total);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedTaxAmountAttribute(): ?string
    {
        return $this->fmt($this->tax_amount);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedDiscountAmountAttribute(): ?string
    {
        return $this->fmt($this->discount_amount);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedShippingAmountAttribute(): ?string
    {
        return $this->fmt($this->shipping_amount);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedTotalDueAttribute(): ?string
    {
        return $this->fmt($this->total_due);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedGrandTotalInvoicedAttribute(): ?string
    {
        return $this->fmt($this->grand_total_invoiced);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedGrandTotalRefundedAttribute(): ?string
    {
        return $this->fmt($this->grand_total_refunded);
    }

    private function fmt($amount): string
    {
        return core()->formatPrice((float) $amount, $this->order_currency_code);
    }

    /** Resolve the order's payment method. */
    private function paymentMethodValue(): ?string
    {
        return DB::table('order_payment')->where('order_id', $this->id)->value('method');
    }
}
