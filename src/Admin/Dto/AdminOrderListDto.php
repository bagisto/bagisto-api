<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminOrderListDto
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * The increment id.
     */
    #[ApiProperty(writable: false)]
    public ?string $increment_id = null;

    /**
     * The status.
     */
    #[ApiProperty(writable: false)]
    public ?string $status = null;

    /**
     * The status label.
     */
    #[ApiProperty(writable: false)]
    public ?string $status_label = null;

    /**
     * The channel id.
     */
    #[ApiProperty(writable: false)]
    public ?int $channel_id = null;

    /**
     * The channel name.
     */
    #[ApiProperty(writable: false)]
    public ?string $channel_name = null;

    /**
     * The is guest.
     */
    #[ApiProperty(writable: false)]
    public ?bool $is_guest = null;

    /**
     * The customer id.
     */
    #[ApiProperty(writable: false)]
    public ?int $customer_id = null;

    /**
     * The customer email.
     */
    #[ApiProperty(writable: false)]
    public ?string $customer_email = null;

    /**
     * The customer name.
     */
    #[ApiProperty(writable: false)]
    public ?string $customer_name = null;

    /**
     * The payment title.
     */
    #[ApiProperty(writable: false)]
    public ?string $payment_title = null;

    /**
     * The coupon code.
     */
    #[ApiProperty(writable: false)]
    public ?string $coupon_code = null;

    /**
     * The total item count.
     */
    #[ApiProperty(writable: false)]
    public ?int $total_item_count = null;

    /**
     * The total qty ordered.
     */
    #[ApiProperty(writable: false)]
    public ?int $total_qty_ordered = null;

    /**
     * The order currency code.
     */
    #[ApiProperty(writable: false)]
    public ?string $order_currency_code = null;

    /**
     * The grand total.
     */
    #[ApiProperty(writable: false)]
    public ?float $grand_total = null;

    /**
     * The base grand total.
     */
    #[ApiProperty(writable: false)]
    public ?float $base_grand_total = null;

    /**
     * The formatted grand total.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_grand_total = null;

    /**
     * The location.
     */
    #[ApiProperty(writable: false)]
    public ?string $location = null;

    /**
     * The created at.
     */
    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;

    /**
     * The items of the order.
     *
     * @var array<int, array<string, mixed>>
     */
    #[ApiProperty(writable: false)]
    public array $items = [];
}
