<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class CustomerOrderListDto
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The increment id.
     */
    public ?string $increment_id = null;

    /**
     * The status.
     */
    public ?string $status = null;

    /**
     * The channel name.
     */
    public ?string $channel_name = null;

    /**
     * The customer email.
     */
    public ?string $customer_email = null;

    /**
     * The customer first name.
     */
    public ?string $customer_first_name = null;

    /**
     * The customer last name.
     */
    public ?string $customer_last_name = null;

    /**
     * The shipping method.
     */
    public ?string $shipping_method = null;

    /**
     * The shipping title.
     */
    public ?string $shipping_title = null;

    /**
     * The coupon code.
     */
    public ?string $coupon_code = null;

    /**
     * The total item count.
     */
    public ?int $total_item_count = null;

    /**
     * The total qty ordered.
     */
    public ?int $total_qty_ordered = null;

    /**
     * The grand total.
     */
    public ?float $grand_total = null;

    /**
     * The base grand total.
     */
    public ?float $base_grand_total = null;

    /**
     * The sub total.
     */
    public ?float $sub_total = null;

    /**
     * The base sub total.
     */
    public ?float $base_sub_total = null;

    /**
     * The tax amount.
     */
    public ?float $tax_amount = null;

    /**
     * The shipping amount.
     */
    public ?float $shipping_amount = null;

    /**
     * The discount amount.
     */
    public ?float $discount_amount = null;

    /**
     * The base currency code.
     */
    public ?string $base_currency_code = null;

    /**
     * The order currency code.
     */
    public ?string $order_currency_code = null;

    /**
     * The created at.
     */
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    public ?string $updated_at = null;
}
