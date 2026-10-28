<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Dto\CustomerOrder\CustomerRefundDto;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CustomerRefundProvider;

/**
 * Refund documents issued against the authenticated customer's orders, as shown on the
 * storefront order view's refunds tab. Read-only and scoped to the order's customer.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerRefund',
    uriTemplate: '/customer-refunds',
    operations: [
        new GetCollection(
            uriTemplate: '/customer-refunds',
            provider: CustomerRefundProvider::class,
            output: CustomerRefundDto::class,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Operation(
                tags: ['Customer Order'],
                summary: 'List refunds for the authenticated customer',
                description: 'Returns the refund documents issued against the authenticated customer\'s orders, newest first, each with its refunded items and totals. Use `?order_id=N` to scope to one order.',
                parameters: [
                    new Parameter(
                        name: 'order_id',
                        in: 'query',
                        description: 'Return only refunds for this order ID. Accepts `orderId` as an alias.',
                        required: false,
                        schema: ['type' => 'integer', 'example' => 5],
                    ),
                    new Parameter(
                        name: 'page',
                        in: 'query',
                        description: 'Page number (1-based).',
                        required: false,
                        schema: ['type' => 'integer', 'default' => 1],
                    ),
                    new Parameter(
                        name: 'per_page',
                        in: 'query',
                        description: 'Refunds per page (default 10, maximum 50).',
                        required: false,
                        schema: ['type' => 'integer', 'default' => 10],
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/customer-refunds/{id}',
            provider: CustomerRefundProvider::class,
            output: CustomerRefundDto::class,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Operation(
                tags: ['Customer Order'],
                summary: 'Get one refund of the authenticated customer',
                description: 'Returns a single refund document with its refunded items and totals. A refund of another customer\'s order answers 404.',
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: BaseQueryItemResolver::class,
        ),
        new QueryCollection(
            provider: CustomerRefundProvider::class,
            paginationType: 'cursor',
            args: [
                'orderId' => ['type' => 'Int', 'description' => 'Filter refunds by order ID'],
                'first' => ['type' => 'Int', 'description' => 'Number of items to return from the start'],
                'last' => ['type' => 'Int', 'description' => 'Number of items to return from the end'],
                'after' => ['type' => 'String', 'description' => 'Cursor to start pagination after'],
                'before' => ['type' => 'String', 'description' => 'Cursor to start pagination before'],
            ],
        ),
    ],
)]
class CustomerRefund extends Model
{
    /** @var string */
    protected $table = 'refunds';

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'order_id' => 'int',
        'total_qty' => 'int',
        'email_sent' => 'boolean',
        'adjustment_refund' => 'float',
        'base_adjustment_refund' => 'float',
        'adjustment_fee' => 'float',
        'base_adjustment_fee' => 'float',
        'sub_total' => 'float',
        'base_sub_total' => 'float',
        'grand_total' => 'float',
        'base_grand_total' => 'float',
        'shipping_amount' => 'float',
        'base_shipping_amount' => 'float',
        'tax_amount' => 'float',
        'base_tax_amount' => 'float',
        'discount_percent' => 'float',
        'discount_amount' => 'float',
        'base_discount_amount' => 'float',
        'shipping_tax_amount' => 'float',
        'base_shipping_tax_amount' => 'float',
        'sub_total_incl_tax' => 'float',
        'base_sub_total_incl_tax' => 'float',
        'shipping_amount_incl_tax' => 'float',
        'base_shipping_amount_incl_tax' => 'float',
    ];

    /** @var array */
    protected $appends = [
        'order_increment_id',
        'formatted_sub_total',
        'formatted_sub_total_incl_tax',
        'formatted_shipping_amount',
        'formatted_shipping_amount_incl_tax',
        'formatted_tax_amount',
        'formatted_discount_amount',
        'formatted_adjustment_refund',
        'formatted_adjustment_fee',
        'formatted_grand_total',
        'formatted_base_grand_total',
    ];

    /**
     * Restrict the query to refunds of the given customer's orders.
     */
    public function scopeOwnedBy(Builder $query, int $customerId): Builder
    {
        return $query->whereIn($query->qualifyColumn('order_id'), function ($orders) use ($customerId) {
            $orders->select('id')->from('orders')->where('customer_id', $customerId);
        });
    }

    /**
     * The refunded items (top-level only), as the storefront refunds tab lists them.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CustomerRefundItem::class, 'refund_id')
            ->whereNull('parent_id');
    }

    /**
     * Increment ID of the order the refund belongs to.
     */
    public function getOrderIncrementIdAttribute(mixed $value = null): ?string
    {
        if ($value === null && $this->order_id) {
            $value = DB::table('orders')->where('id', $this->order_id)->value('increment_id');
        }

        return $value === null ? null : (string) $value;
    }

    public function getFormattedSubTotalAttribute(): ?string
    {
        return $this->formatAmount('sub_total');
    }

    public function getFormattedSubTotalInclTaxAttribute(): ?string
    {
        return $this->formatAmount('sub_total_incl_tax');
    }

    public function getFormattedShippingAmountAttribute(): ?string
    {
        return $this->formatAmount('shipping_amount');
    }

    public function getFormattedShippingAmountInclTaxAttribute(): ?string
    {
        return $this->formatAmount('shipping_amount_incl_tax');
    }

    public function getFormattedTaxAmountAttribute(): ?string
    {
        return $this->formatAmount('tax_amount');
    }

    public function getFormattedDiscountAmountAttribute(): ?string
    {
        return $this->formatAmount('discount_amount');
    }

    public function getFormattedAdjustmentRefundAttribute(): ?string
    {
        return $this->formatAmount('adjustment_refund');
    }

    public function getFormattedAdjustmentFeeAttribute(): ?string
    {
        return $this->formatAmount('adjustment_fee');
    }

    public function getFormattedGrandTotalAttribute(): ?string
    {
        return $this->formatAmount('grand_total');
    }

    public function getFormattedBaseGrandTotalAttribute(): ?string
    {
        $amount = $this->getAttribute('base_grand_total');

        return $amount === null ? null : core()->formatBasePrice((float) $amount);
    }

    /**
     * Format a refund amount in the order currency, as the storefront refunds tab does.
     */
    private function formatAmount(string $column): ?string
    {
        $amount = $this->getAttribute($column);

        if ($amount === null) {
            return null;
        }

        return core()->formatPrice((float) $amount, $this->getAttribute('order_currency_code'));
    }
}
