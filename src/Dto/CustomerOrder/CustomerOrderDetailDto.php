<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class CustomerOrderDetailDto
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
     * The is guest.
     */
    public ?bool $is_guest = null;

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
     * The customer full name.
     */
    public ?string $customer_full_name = null;

    /**
     * The shipping method.
     */
    public ?string $shipping_method = null;

    /**
     * The shipping title.
     */
    public ?string $shipping_title = null;

    /**
     * The shipping description.
     */
    public ?string $shipping_description = null;

    /**
     * The coupon code.
     */
    public ?string $coupon_code = null;

    /**
     * The is gift.
     */
    public ?bool $is_gift = null;

    /**
     * The total item count.
     */
    public ?int $total_item_count = null;

    /**
     * The total qty ordered.
     */
    public ?int $total_qty_ordered = null;

    /**
     * The base currency code.
     */
    public ?string $base_currency_code = null;

    /**
     * The channel currency code.
     */
    public ?string $channel_currency_code = null;

    /**
     * The order currency code.
     */
    public ?string $order_currency_code = null;

    /**
     * The grand total.
     */
    public ?float $grand_total = null;

    /**
     * The base grand total.
     */
    public ?float $base_grand_total = null;

    /**
     * The grand total invoiced.
     */
    public ?float $grand_total_invoiced = null;

    /**
     * The base grand total invoiced.
     */
    public ?float $base_grand_total_invoiced = null;

    /**
     * The grand total refunded.
     */
    public ?float $grand_total_refunded = null;

    /**
     * The base grand total refunded.
     */
    public ?float $base_grand_total_refunded = null;

    /**
     * The sub total.
     */
    public ?float $sub_total = null;

    /**
     * The base sub total.
     */
    public ?float $base_sub_total = null;

    /**
     * The sub total invoiced.
     */
    public ?float $sub_total_invoiced = null;

    /**
     * The base sub total invoiced.
     */
    public ?float $base_sub_total_invoiced = null;

    /**
     * The sub total refunded.
     */
    public ?float $sub_total_refunded = null;

    /**
     * The base sub total refunded.
     */
    public ?float $base_sub_total_refunded = null;

    /**
     * The discount percent.
     */
    public ?float $discount_percent = null;

    /**
     * The discount amount.
     */
    public ?float $discount_amount = null;

    /**
     * The base discount amount.
     */
    public ?float $base_discount_amount = null;

    /**
     * The discount invoiced.
     */
    public ?float $discount_invoiced = null;

    /**
     * The base discount invoiced.
     */
    public ?float $base_discount_invoiced = null;

    /**
     * The discount refunded.
     */
    public ?float $discount_refunded = null;

    /**
     * The base discount refunded.
     */
    public ?float $base_discount_refunded = null;

    /**
     * The tax amount.
     */
    public ?float $tax_amount = null;

    /**
     * The base tax amount.
     */
    public ?float $base_tax_amount = null;

    /**
     * The tax amount invoiced.
     */
    public ?float $tax_amount_invoiced = null;

    /**
     * The base tax amount invoiced.
     */
    public ?float $base_tax_amount_invoiced = null;

    /**
     * The tax amount refunded.
     */
    public ?float $tax_amount_refunded = null;

    /**
     * The base tax amount refunded.
     */
    public ?float $base_tax_amount_refunded = null;

    /**
     * The shipping amount.
     */
    public ?float $shipping_amount = null;

    /**
     * The base shipping amount.
     */
    public ?float $base_shipping_amount = null;

    /**
     * The shipping invoiced.
     */
    public ?float $shipping_invoiced = null;

    /**
     * The base shipping invoiced.
     */
    public ?float $base_shipping_invoiced = null;

    /**
     * The shipping refunded.
     */
    public ?float $shipping_refunded = null;

    /**
     * The base shipping refunded.
     */
    public ?float $base_shipping_refunded = null;

    /**
     * The shipping discount amount.
     */
    public ?float $shipping_discount_amount = null;

    /**
     * The base shipping discount amount.
     */
    public ?float $base_shipping_discount_amount = null;

    /**
     * The shipping tax amount.
     */
    public ?float $shipping_tax_amount = null;

    /**
     * The base shipping tax amount.
     */
    public ?float $base_shipping_tax_amount = null;

    /**
     * The shipping tax refunded.
     */
    public ?float $shipping_tax_refunded = null;

    /**
     * The base shipping tax refunded.
     */
    public ?float $base_shipping_tax_refunded = null;

    /**
     * The sub total incl tax.
     */
    public ?float $sub_total_incl_tax = null;

    /**
     * The base sub total incl tax.
     */
    public ?float $base_sub_total_incl_tax = null;

    /**
     * The shipping amount incl tax.
     */
    public ?float $shipping_amount_incl_tax = null;

    /**
     * The base shipping amount incl tax.
     */
    public ?float $base_shipping_amount_incl_tax = null;

    /**
     * The customer id.
     */
    public ?int $customer_id = null;

    /**
     * The channel id.
     */
    public ?int $channel_id = null;

    /**
     * The cart id.
     */
    public ?int $cart_id = null;

    /**
     * The applied cart rule ids.
     */
    public ?string $applied_cart_rule_ids = null;

    /**
     * The created at.
     */
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    public ?string $updated_at = null;

    /**
     * The total due.
     */
    public ?float $total_due = null;

    /**
     * The base total due.
     */
    public ?float $base_total_due = null;

    /**
     * The can cancel.
     */
    public ?bool $can_cancel = null;

    /**
     * The can reorder.
     */
    public ?bool $can_reorder = null;

    /**
     * The have stockable items.
     */
    public ?bool $have_stockable_items = null;

    /** @var OrderItemDto[] */
    #[ApiProperty(readableLink: true)]
    public array $items = [];

    /** @var OrderAddressDto[] */
    #[ApiProperty(readableLink: true)]
    public array $addresses = [];

    /**
     * The payment.
     */
    #[ApiProperty(readableLink: true)]
    public ?OrderPaymentDto $payment = null;

    /** @var OrderShipmentDto[] */
    #[ApiProperty(readableLink: true)]
    public array $shipments = [];

    /** @var OrderInvoiceDto[] */
    #[ApiProperty(readableLink: true)]
    public array $invoices = [];

    /** @var OrderRefundDto[] */
    #[ApiProperty(readableLink: true)]
    public array $refunds = [];
}
