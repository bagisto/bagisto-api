<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * A refunded line of a customer refund — exposed only through CustomerRefund.items.
 */
#[ApiResource(
    shortName: 'CustomerRefundItem',
    operations: [],
    graphQlOperations: [],
)]
class CustomerRefundItem extends Model
{
    /** @var string */
    protected $table = 'refund_items';

    /** @var array<int, string> */
    protected static array $currencyCodes = [];

    /** @var array */
    protected $hidden = ['product_type'];

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'refund_id' => 'int',
        'order_item_id' => 'int',
        'parent_id' => 'int',
        'product_id' => 'int',
        'sku' => 'string',
        'name' => 'string',
        'description' => 'string',
        'qty' => 'int',
        'price' => 'float',
        'base_price' => 'float',
        'price_incl_tax' => 'float',
        'base_price_incl_tax' => 'float',
        'total' => 'float',
        'base_total' => 'float',
        'total_incl_tax' => 'float',
        'base_total_incl_tax' => 'float',
        'tax_amount' => 'float',
        'base_tax_amount' => 'float',
        'discount_percent' => 'float',
        'discount_amount' => 'float',
        'base_discount_amount' => 'float',
        'additional' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** @var array */
    protected $appends = [
        'formatted_price',
        'formatted_price_incl_tax',
        'formatted_total',
        'formatted_total_incl_tax',
        'formatted_tax_amount',
        'formatted_discount_amount',
    ];

    public function getFormattedPriceAttribute(): ?string
    {
        return $this->formatAmount('price');
    }

    public function getFormattedPriceInclTaxAttribute(): ?string
    {
        return $this->formatAmount('price_incl_tax');
    }

    public function getFormattedTotalAttribute(): ?string
    {
        return $this->formatAmount('total');
    }

    public function getFormattedTotalInclTaxAttribute(): ?string
    {
        return $this->formatAmount('total_incl_tax');
    }

    public function getFormattedTaxAmountAttribute(): ?string
    {
        return $this->formatAmount('tax_amount');
    }

    public function getFormattedDiscountAmountAttribute(): ?string
    {
        return $this->formatAmount('discount_amount');
    }

    /**
     * Format an item amount in the currency of the refund it belongs to.
     */
    private function formatAmount(string $column): ?string
    {
        $amount = $this->getAttribute($column);

        if ($amount === null) {
            return null;
        }

        return core()->formatPrice((float) $amount, $this->currencyCode());
    }

    /**
     * Order currency of the parent refund, looked up once per refund.
     */
    private function currencyCode(): ?string
    {
        $refundId = (int) $this->getAttribute('refund_id');

        if (! array_key_exists($refundId, static::$currencyCodes)) {
            static::$currencyCodes[$refundId] = DB::table('refunds')->where('id', $refundId)->value('order_currency_code');
        }

        return static::$currencyCodes[$refundId];
    }
}
