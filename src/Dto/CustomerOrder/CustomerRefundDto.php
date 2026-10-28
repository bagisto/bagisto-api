<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * REST shape for /customer-refunds and /customer-refunds/{id}, with the refunded items embedded.
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class CustomerRefundDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $order_id = null;

    public ?string $order_increment_id = null;

    public ?string $increment_id = null;

    public ?string $state = null;

    public ?int $total_qty = null;

    public ?string $base_currency_code = null;

    public ?string $channel_currency_code = null;

    public ?string $order_currency_code = null;

    public ?float $sub_total = null;

    public ?float $base_sub_total = null;

    public ?float $sub_total_incl_tax = null;

    public ?float $base_sub_total_incl_tax = null;

    public ?float $shipping_amount = null;

    public ?float $base_shipping_amount = null;

    public ?float $shipping_amount_incl_tax = null;

    public ?float $base_shipping_amount_incl_tax = null;

    public ?float $shipping_tax_amount = null;

    public ?float $base_shipping_tax_amount = null;

    public ?float $tax_amount = null;

    public ?float $base_tax_amount = null;

    public ?float $discount_percent = null;

    public ?float $discount_amount = null;

    public ?float $base_discount_amount = null;

    public ?float $adjustment_refund = null;

    public ?float $base_adjustment_refund = null;

    public ?float $adjustment_fee = null;

    public ?float $base_adjustment_fee = null;

    public ?float $grand_total = null;

    public ?float $base_grand_total = null;

    public ?string $formatted_sub_total = null;

    public ?string $formatted_sub_total_incl_tax = null;

    public ?string $formatted_shipping_amount = null;

    public ?string $formatted_shipping_amount_incl_tax = null;

    public ?string $formatted_tax_amount = null;

    public ?string $formatted_discount_amount = null;

    public ?string $formatted_adjustment_refund = null;

    public ?string $formatted_adjustment_fee = null;

    public ?string $formatted_grand_total = null;

    public ?string $formatted_base_grand_total = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;

    /** @var RefundItemDto[] */
    #[ApiProperty(readableLink: true)]
    public array $items = [];
}
