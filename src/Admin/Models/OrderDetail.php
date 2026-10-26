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
    /**
     * Status code → label (mirrors core Order::$statusLabel).
     */
    private const STATUS_LABELS = [
        'pending' => 'Pending',
        'pending_payment' => 'Pending Payment',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'canceled' => 'Canceled',
        'closed' => 'Closed',
        'fraud' => 'Fraud',
    ];

    /**
     * The table that stores the orders.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * The computed attributes appended when the order is serialized.
     *
     * @var array
     */
    protected $appends = [
        'status_label', 'payment_method', 'payment_title', 'total_due', 'base_total_due',
        'formatted_grand_total', 'formatted_sub_total', 'formatted_tax_amount',
        'formatted_discount_amount', 'formatted_shipping_amount', 'formatted_total_due',
        'formatted_grand_total_invoiced', 'formatted_grand_total_refunded',
        'formatted_sub_total_incl_tax', 'formatted_shipping_amount_incl_tax',
    ];

    /**
     * The native types the order attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'is_guest' => 'boolean',
        'is_gift' => 'boolean',
        'total_item_count' => 'int',
        'total_qty_ordered' => 'int',
        'grand_total' => 'float',
        'base_grand_total' => 'float',
        'grand_total_invoiced' => 'float',
        'grand_total_refunded' => 'float',
        'sub_total' => 'float',
        'base_sub_total' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'shipping_amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    // --- Nested relations (GraphQL connections / typed objects) ---

    /**
     * Items.
     */
    #[ApiProperty(writable: false)]
    public function items(): HasMany
    {
        return $this->hasMany(AdminOrderDetailItem::class, 'order_id')->whereNull('parent_id');
    }

    /**
     * Invoices.
     */
    #[ApiProperty(writable: false)]
    public function invoices(): HasMany
    {
        return $this->hasMany(AdminOrderDetailInvoice::class, 'order_id');
    }

    /**
     * Shipments.
     */
    #[ApiProperty(writable: false)]
    public function shipments(): HasMany
    {
        return $this->hasMany(AdminOrderDetailShipment::class, 'order_id');
    }

    /**
     * Refunds.
     */
    #[ApiProperty(writable: false)]
    public function refunds(): HasMany
    {
        return $this->hasMany(AdminOrderDetailRefund::class, 'order_id');
    }

    /**
     * Comments.
     */
    #[ApiProperty(writable: false)]
    public function comments(): HasMany
    {
        return $this->hasMany(AdminOrderDetailComment::class, 'order_id')->orderByDesc('id');
    }

    /**
     * Customer.
     */
    #[ApiProperty(writable: false)]
    public function customer(): BelongsTo
    {
        return $this->belongsTo(AdminOrderDetailCustomer::class, 'customer_id');
    }

    /**
     * Order addresses (billing + shipping) → connection. Each node carries
     * `addressType` so the client can tell them apart. A constrained HasOne.
     */
    #[ApiProperty(writable: false)]
    public function addresses(): HasMany
    {
        return $this->hasMany(AdminOrderDetailAddress::class, 'order_id')
            ->whereIn('address_type', ['order_billing', 'order_shipping']);
    }

    // --- Computed scalars (appended; resolve over both transports) ---

    /**
     * Get status label attribute.
     */
    #[ApiProperty(writable: false)]
    public function getStatusLabelAttribute(): ?string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Get payment method attribute.
     */
    #[ApiProperty(writable: false)]
    public function getPaymentMethodAttribute(): ?string
    {
        return $this->paymentMethodValue();
    }

    /**
     * Get payment title attribute.
     */
    #[ApiProperty(writable: false)]
    public function getPaymentTitleAttribute(): ?string
    {
        $method = $this->paymentMethodValue();

        if (! $method) {
            return null;
        }

        return core()->getConfigData('sales.payment_methods.'.$method.'.title') ?: $method;
    }

    /**
     * Get total due attribute.
     */
    #[ApiProperty(writable: false)]
    public function getTotalDueAttribute(): float
    {
        return round((float) $this->grand_total - (float) $this->grand_total_invoiced, 2) + 0.0;
    }

    /**
     * Get base total due attribute.
     */
    #[ApiProperty(writable: false)]
    public function getBaseTotalDueAttribute(): float
    {
        return round((float) $this->base_grand_total - (float) $this->base_grand_total_invoiced, 2) + 0.0;
    }

    /**
     * Get formatted grand total attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedGrandTotalAttribute(): ?string
    {
        return $this->fmt($this->grand_total);
    }

    /**
     * Get formatted sub total attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedSubTotalAttribute(): ?string
    {
        return $this->fmt($this->sub_total);
    }

    /**
     * Get formatted tax amount attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedTaxAmountAttribute(): ?string
    {
        return $this->fmt($this->tax_amount);
    }

    /**
     * Get formatted discount amount attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedDiscountAmountAttribute(): ?string
    {
        return $this->fmt($this->discount_amount);
    }

    /**
     * Get formatted shipping amount attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedShippingAmountAttribute(): ?string
    {
        return $this->fmt($this->shipping_amount);
    }

    /**
     * Get formatted total due attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedTotalDueAttribute(): ?string
    {
        return $this->fmt($this->total_due);
    }

    /**
     * Get formatted grand total invoiced attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedGrandTotalInvoicedAttribute(): ?string
    {
        return $this->fmt($this->grand_total_invoiced);
    }

    /**
     * Get formatted sub total incl tax attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedSubTotalInclTaxAttribute(): ?string
    {
        return $this->fmt($this->sub_total_incl_tax);
    }

    /**
     * Get formatted shipping amount incl tax attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedShippingAmountInclTaxAttribute(): ?string
    {
        return $this->fmt($this->shipping_amount_incl_tax);
    }

    /**
     * Get formatted grand total refunded attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedGrandTotalRefundedAttribute(): ?string
    {
        return $this->fmt($this->grand_total_refunded);
    }

    /**
     * Fmt.
     */
    private function fmt($amount): string
    {
        return core()->formatPrice((float) $amount, $this->order_currency_code);
    }

    /**
     * Resolve the order's payment method.
     */
    private function paymentMethodValue(): ?string
    {
        return DB::table('order_payment')->where('order_id', $this->id)->value('method');
    }
}
