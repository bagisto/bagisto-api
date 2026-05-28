<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Detail-view shape for /customer-orders/{id}.
 *
 * Mirrors all order-level fields the legacy response exposed, but replaces the
 * dangling IRI relations (items, addresses, payment, shipments) with embedded
 * objects so a single request returns everything the detail UI needs.
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class CustomerOrderDetailDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $increment_id = null;

    public ?string $status = null;

    public ?string $channel_name = null;

    public ?bool $is_guest = null;

    public ?string $customer_email = null;

    public ?string $customer_first_name = null;

    public ?string $customer_last_name = null;

    public ?string $customer_full_name = null;

    public ?string $shipping_method = null;

    public ?string $shipping_title = null;

    public ?string $shipping_description = null;

    public ?string $coupon_code = null;

    public ?bool $is_gift = null;

    public ?int $total_item_count = null;

    public ?int $total_qty_ordered = null;

    public ?string $base_currency_code = null;

    public ?string $channel_currency_code = null;

    public ?string $order_currency_code = null;

    public ?float $grand_total = null;

    public ?float $base_grand_total = null;

    public ?float $grand_total_invoiced = null;

    public ?float $base_grand_total_invoiced = null;

    public ?float $grand_total_refunded = null;

    public ?float $base_grand_total_refunded = null;

    public ?float $sub_total = null;

    public ?float $base_sub_total = null;

    public ?float $sub_total_invoiced = null;

    public ?float $base_sub_total_invoiced = null;

    public ?float $sub_total_refunded = null;

    public ?float $base_sub_total_refunded = null;

    public ?float $discount_percent = null;

    public ?float $discount_amount = null;

    public ?float $base_discount_amount = null;

    public ?float $discount_invoiced = null;

    public ?float $base_discount_invoiced = null;

    public ?float $discount_refunded = null;

    public ?float $base_discount_refunded = null;

    public ?float $tax_amount = null;

    public ?float $base_tax_amount = null;

    public ?float $tax_amount_invoiced = null;

    public ?float $base_tax_amount_invoiced = null;

    public ?float $tax_amount_refunded = null;

    public ?float $base_tax_amount_refunded = null;

    public ?float $shipping_amount = null;

    public ?float $base_shipping_amount = null;

    public ?float $shipping_invoiced = null;

    public ?float $base_shipping_invoiced = null;

    public ?float $shipping_refunded = null;

    public ?float $base_shipping_refunded = null;

    public ?float $shipping_discount_amount = null;

    public ?float $base_shipping_discount_amount = null;

    public ?float $shipping_tax_amount = null;

    public ?float $base_shipping_tax_amount = null;

    public ?float $shipping_tax_refunded = null;

    public ?float $base_shipping_tax_refunded = null;

    public ?float $sub_total_incl_tax = null;

    public ?float $base_sub_total_incl_tax = null;

    public ?float $shipping_amount_incl_tax = null;

    public ?float $base_shipping_amount_incl_tax = null;

    public ?int $customer_id = null;

    public ?int $channel_id = null;

    public ?int $cart_id = null;

    public ?string $applied_cart_rule_ids = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;

    /** @var OrderItemDto[] */
    #[ApiProperty(readableLink: true)]
    public array $items = [];

    /** @var OrderAddressDto[] */
    #[ApiProperty(readableLink: true)]
    public array $addresses = [];

    #[ApiProperty(readableLink: true)]
    public ?OrderPaymentDto $payment = null;

    /** @var OrderShipmentDto[] */
    #[ApiProperty(readableLink: true)]
    public array $shipments = [];
}
