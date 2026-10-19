<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminInvoiceRestDto
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
     * The order id.
     */
    #[ApiProperty(writable: false)]
    public ?int $order_id = null;

    /**
     * The order increment id.
     */
    #[ApiProperty(writable: false)]
    public ?string $order_increment_id = null;

    /**
     * The state.
     */
    #[ApiProperty(writable: false)]
    public ?string $state = null;

    /**
     * The email sent.
     */
    #[ApiProperty(writable: false)]
    public ?bool $email_sent = null;

    /**
     * The total qty.
     */
    #[ApiProperty(writable: false)]
    public ?int $total_qty = null;

    /**
     * The order currency code.
     */
    #[ApiProperty(writable: false)]
    public ?string $order_currency_code = null;

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
     * The sub total.
     */
    #[ApiProperty(writable: false)]
    public ?float $sub_total = null;

    /**
     * The formatted sub total.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_sub_total = null;

    /**
     * The base sub total.
     */
    #[ApiProperty(writable: false)]
    public ?float $base_sub_total = null;

    /**
     * The formatted base sub total.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_base_sub_total = null;

    /**
     * The sub total incl tax.
     */
    #[ApiProperty(writable: false)]
    public ?float $sub_total_incl_tax = null;

    /**
     * The formatted sub total incl tax.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_sub_total_incl_tax = null;

    /**
     * The base sub total incl tax.
     */
    #[ApiProperty(writable: false)]
    public ?float $base_sub_total_incl_tax = null;

    /**
     * The formatted base sub total incl tax.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_base_sub_total_incl_tax = null;

    /**
     * The grand total.
     */
    #[ApiProperty(writable: false)]
    public ?float $grand_total = null;

    /**
     * The formatted grand total.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_grand_total = null;

    /**
     * The base grand total.
     */
    #[ApiProperty(writable: false)]
    public ?float $base_grand_total = null;

    /**
     * The formatted base grand total.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_base_grand_total = null;

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
     * The base tax amount.
     */
    #[ApiProperty(writable: false)]
    public ?float $base_tax_amount = null;

    /**
     * The formatted base tax amount.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_base_tax_amount = null;

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
     * The base discount amount.
     */
    #[ApiProperty(writable: false)]
    public ?float $base_discount_amount = null;

    /**
     * The formatted base discount amount.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_base_discount_amount = null;

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
     * The base shipping amount.
     */
    #[ApiProperty(writable: false)]
    public ?float $base_shipping_amount = null;

    /**
     * The formatted base shipping amount.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_base_shipping_amount = null;

    /**
     * The shipping amount incl tax.
     */
    #[ApiProperty(writable: false)]
    public ?float $shipping_amount_incl_tax = null;

    /**
     * The formatted shipping amount incl tax.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_shipping_amount_incl_tax = null;

    /**
     * The base shipping amount incl tax.
     */
    #[ApiProperty(writable: false)]
    public ?float $base_shipping_amount_incl_tax = null;

    /**
     * The formatted base shipping amount incl tax.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_base_shipping_amount_incl_tax = null;

    /**
     * The shipping tax amount.
     */
    #[ApiProperty(writable: false)]
    public ?float $shipping_tax_amount = null;

    /**
     * The formatted shipping tax amount.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_shipping_tax_amount = null;

    /**
     * The base shipping tax amount.
     */
    #[ApiProperty(writable: false)]
    public ?float $base_shipping_tax_amount = null;

    /**
     * The formatted base shipping tax amount.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_base_shipping_tax_amount = null;

    /**
     * The transaction id.
     */
    #[ApiProperty(writable: false)]
    public ?string $transaction_id = null;

    /**
     * The reminders.
     */
    #[ApiProperty(writable: false)]
    public ?int $reminders = null;

    /**
     * The next reminder at.
     */
    #[ApiProperty(writable: false)]
    public ?string $next_reminder_at = null;

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
     * The order status.
     */
    #[ApiProperty(writable: false)]
    public ?string $order_status = null;

    /**
     * The order status label.
     */
    #[ApiProperty(writable: false)]
    public ?string $order_status_label = null;

    /**
     * The order date.
     */
    #[ApiProperty(writable: false)]
    public ?string $order_date = null;

    /**
     * The channel name.
     */
    #[ApiProperty(writable: false)]
    public ?string $channel_name = null;

    /**
     * The customer name.
     */
    #[ApiProperty(writable: false)]
    public ?string $customer_name = null;

    /**
     * The customer email.
     */
    #[ApiProperty(writable: false)]
    public ?string $customer_email = null;

    /**
     * The invoice's order — `{ id, addresses: [...] }`. Billing/shipping live on
     * the order; over GraphQL they're `order { addresses { edges { node } } }`.
     */
    #[ApiProperty(writable: false)]
    public ?array $order = null;

    /** @var array<int, array<string, mixed>> */
    #[ApiProperty(writable: false)]
    public array $items = [];
}
