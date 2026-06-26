<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

/**
 * REST output for AdminInvoice (detail + listing). Snake_case props surface as
 * camelCase via the central converter (provider writes camelCase; the trait
 * maps it). Nested data is plain JSON — `addresses` (billing + shipping, each
 * with `addressType`) and `items` as flat arrays; over GraphQL the same data is
 * served as connections off the AdminInvoice Eloquent resource.
 *
 * IMPORTANT (the output:-DTO name-match trap, see CLAUDE.md OrderDetail notes):
 * with `output:` set, API Platform only serialises DTO props whose names match
 * an attribute/relation on the AdminInvoice Eloquent resource — so the address
 * block MUST be named `addresses` (a relation), not billingAddress/shippingAddress.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminInvoiceRestDto
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $increment_id = null;

    #[ApiProperty(writable: false)]
    public ?int $order_id = null;

    #[ApiProperty(writable: false)]
    public ?string $order_increment_id = null;

    #[ApiProperty(writable: false)]
    public ?string $state = null;

    #[ApiProperty(writable: false)]
    public ?bool $email_sent = null;

    #[ApiProperty(writable: false)]
    public ?int $total_qty = null;

    #[ApiProperty(writable: false)]
    public ?string $order_currency_code = null;

    #[ApiProperty(writable: false)]
    public ?string $base_currency_code = null;

    #[ApiProperty(writable: false)]
    public ?string $channel_currency_code = null;

    #[ApiProperty(writable: false)]
    public ?float $sub_total = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_sub_total = null;

    #[ApiProperty(writable: false)]
    public ?float $base_sub_total = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_base_sub_total = null;

    #[ApiProperty(writable: false)]
    public ?float $sub_total_incl_tax = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_sub_total_incl_tax = null;

    #[ApiProperty(writable: false)]
    public ?float $base_sub_total_incl_tax = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_base_sub_total_incl_tax = null;

    #[ApiProperty(writable: false)]
    public ?float $grand_total = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_grand_total = null;

    #[ApiProperty(writable: false)]
    public ?float $base_grand_total = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_base_grand_total = null;

    #[ApiProperty(writable: false)]
    public ?float $tax_amount = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_tax_amount = null;

    #[ApiProperty(writable: false)]
    public ?float $base_tax_amount = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_base_tax_amount = null;

    #[ApiProperty(writable: false)]
    public ?float $discount_amount = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_discount_amount = null;

    #[ApiProperty(writable: false)]
    public ?float $base_discount_amount = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_base_discount_amount = null;

    #[ApiProperty(writable: false)]
    public ?float $shipping_amount = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_shipping_amount = null;

    #[ApiProperty(writable: false)]
    public ?float $base_shipping_amount = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_base_shipping_amount = null;

    #[ApiProperty(writable: false)]
    public ?float $shipping_amount_incl_tax = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_shipping_amount_incl_tax = null;

    #[ApiProperty(writable: false)]
    public ?float $base_shipping_amount_incl_tax = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_base_shipping_amount_incl_tax = null;

    #[ApiProperty(writable: false)]
    public ?float $shipping_tax_amount = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_shipping_tax_amount = null;

    #[ApiProperty(writable: false)]
    public ?float $base_shipping_tax_amount = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_base_shipping_tax_amount = null;

    #[ApiProperty(writable: false)]
    public ?string $transaction_id = null;

    #[ApiProperty(writable: false)]
    public ?int $reminders = null;

    #[ApiProperty(writable: false)]
    public ?string $next_reminder_at = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;

    #[ApiProperty(writable: false)]
    public ?string $order_status = null;

    #[ApiProperty(writable: false)]
    public ?string $order_status_label = null;

    #[ApiProperty(writable: false)]
    public ?string $order_date = null;

    #[ApiProperty(writable: false)]
    public ?string $channel_name = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_name = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_email = null;

    /**
     * The invoice's order — `{ id, addresses: [...] }`. Billing/shipping live on
     * the order; over GraphQL they're `order { addresses { edges { node } } }`.
     * The prop is named `order` (not `addresses`) to match the AdminInvoice
     * `order()` relation — the output:-DTO name-match rule.
     */
    #[ApiProperty(writable: false)]
    public ?array $order = null;

    /** @var array<int, array<string, mixed>> */
    #[ApiProperty(writable: false)]
    public array $items = [];
}
