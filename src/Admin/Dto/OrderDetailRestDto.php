<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderDetailRestDto
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
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
     * The is gift.
     */
    #[ApiProperty(writable: false)]
    public ?bool $is_gift = null;

    /**
     * The customer email.
     */
    #[ApiProperty(writable: false)]
    public ?string $customer_email = null;

    /**
     * The customer first name.
     */
    #[ApiProperty(writable: false)]
    public ?string $customer_first_name = null;

    /**
     * The customer last name.
     */
    #[ApiProperty(writable: false)]
    public ?string $customer_last_name = null;

    /**
     * The shipping method.
     */
    #[ApiProperty(writable: false)]
    public ?string $shipping_method = null;

    /**
     * The shipping title.
     */
    #[ApiProperty(writable: false)]
    public ?string $shipping_title = null;

    /**
     * The shipping description.
     */
    #[ApiProperty(writable: false)]
    public ?string $shipping_description = null;

    /**
     * The payment method.
     */
    #[ApiProperty(writable: false)]
    public ?string $payment_method = null;

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
     * The base currency code.
     */
    #[ApiProperty(writable: false)]
    public ?string $base_currency_code = null;

    /**
     * The channel currency code.
     */
    #[ApiProperty(writable: false)]
    public ?string $channel_currency_code = null;

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
     * The grand total invoiced.
     */
    #[ApiProperty(writable: false)]
    public ?float $grand_total_invoiced = null;

    /**
     * The formatted grand total invoiced.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_grand_total_invoiced = null;

    /**
     * The grand total refunded.
     */
    #[ApiProperty(writable: false)]
    public ?float $grand_total_refunded = null;

    /**
     * The formatted grand total refunded.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_grand_total_refunded = null;

    /**
     * The sub total.
     */
    #[ApiProperty(writable: false)]
    public ?float $sub_total = null;

    /**
     * The base sub total.
     */
    #[ApiProperty(writable: false)]
    public ?float $base_sub_total = null;

    /**
     * The formatted sub total.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_sub_total = null;

    /**
     * The tax amount.
     */
    #[ApiProperty(writable: false)]
    public ?float $tax_amount = null;

    /**
     * The formatted tax amount.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_tax_amount = null;

    /**
     * The discount amount.
     */
    #[ApiProperty(writable: false)]
    public ?float $discount_amount = null;

    /**
     * The formatted discount amount.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_discount_amount = null;

    /**
     * The shipping amount.
     */
    #[ApiProperty(writable: false)]
    public ?float $shipping_amount = null;

    /**
     * The formatted shipping amount.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_shipping_amount = null;

    /**
     * The total due.
     */
    #[ApiProperty(writable: false)]
    public ?float $total_due = null;

    /**
     * The base total due.
     */
    #[ApiProperty(writable: false)]
    public ?float $base_total_due = null;

    /**
     * The formatted total due.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_total_due = null;

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
     * The customer.
     */
    #[ApiProperty(writable: false)]
    public ?array $customer = null;

    /**
     * Billing + shipping addresses (each carries `addressType`). A flat array —
     * the GraphQL counterpart is the `addresses` connection. NOTE: separate.
     *
     * @var array<int, array<string, mixed>>
     */
    #[ApiProperty(writable: false)]
    public array $addresses = [];

    /**
     * The items of the order.
     *
     * @var array<int, array<string, mixed>>
     */
    #[ApiProperty(writable: false)]
    public array $items = [];

    /**
     * The invoices raised against the order.
     *
     * @var array<int, array<string, mixed>>
     */
    #[ApiProperty(writable: false)]
    public array $invoices = [];

    /**
     * The shipments created for the order.
     *
     * @var array<int, array<string, mixed>>
     */
    #[ApiProperty(writable: false)]
    public array $shipments = [];

    /**
     * The refunds issued against the order.
     *
     * @var array<int, array<string, mixed>>
     */
    #[ApiProperty(writable: false)]
    public array $refunds = [];

    /**
     * The comments added to the order.
     *
     * @var array<int, array<string, mixed>>
     */
    #[ApiProperty(writable: false)]
    public array $comments = [];
}
