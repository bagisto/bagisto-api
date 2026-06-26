<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

/**
 * REST output for AdminOrder detail. Snake_case props surface as camelCase via
 * the central output converter (provider writes camelCase; AcceptsCamelCaseWrites
 * maps it onto the snake props — required so the converter can read values by
 * the snake name). Quirk: a MULTI-WORD snake_case `?array` prop
 * (`billing_address`) is silently dropped by the serializer, while a single-word
 * `?array` (`customer`) and multi-word string props are fine — so the billing /
 * shipping objects use single-word props with an explicit #[SerializedName].
 *
 * Nested data is plain JSON: customer / billingAddress / shippingAddress as
 * objects, items / invoices / shipments / refunds / comments as flat arrays.
 * (Over GraphQL the same data is served as connections / typed objects off the
 * OrderDetail Eloquent resource.)
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderDetailRestDto
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $increment_id = null;

    #[ApiProperty(writable: false)]
    public ?string $status = null;

    #[ApiProperty(writable: false)]
    public ?string $status_label = null;

    #[ApiProperty(writable: false)]
    public ?string $channel_name = null;

    #[ApiProperty(writable: false)]
    public ?bool $is_guest = null;

    #[ApiProperty(writable: false)]
    public ?bool $is_gift = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_email = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_first_name = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_last_name = null;

    #[ApiProperty(writable: false)]
    public ?string $shipping_method = null;

    #[ApiProperty(writable: false)]
    public ?string $shipping_title = null;

    #[ApiProperty(writable: false)]
    public ?string $shipping_description = null;

    #[ApiProperty(writable: false)]
    public ?string $payment_method = null;

    #[ApiProperty(writable: false)]
    public ?string $payment_title = null;

    #[ApiProperty(writable: false)]
    public ?string $coupon_code = null;

    #[ApiProperty(writable: false)]
    public ?int $total_item_count = null;

    #[ApiProperty(writable: false)]
    public ?int $total_qty_ordered = null;

    #[ApiProperty(writable: false)]
    public ?string $base_currency_code = null;

    #[ApiProperty(writable: false)]
    public ?string $channel_currency_code = null;

    #[ApiProperty(writable: false)]
    public ?string $order_currency_code = null;

    #[ApiProperty(writable: false)]
    public ?float $grand_total = null;

    #[ApiProperty(writable: false)]
    public ?float $base_grand_total = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_grand_total = null;

    #[ApiProperty(writable: false)]
    public ?float $grand_total_invoiced = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_grand_total_invoiced = null;

    #[ApiProperty(writable: false)]
    public ?float $grand_total_refunded = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_grand_total_refunded = null;

    #[ApiProperty(writable: false)]
    public ?float $sub_total = null;

    #[ApiProperty(writable: false)]
    public ?float $base_sub_total = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_sub_total = null;

    #[ApiProperty(writable: false)]
    public ?float $tax_amount = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_tax_amount = null;

    #[ApiProperty(writable: false)]
    public ?float $discount_amount = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_discount_amount = null;

    #[ApiProperty(writable: false)]
    public ?float $shipping_amount = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_shipping_amount = null;

    #[ApiProperty(writable: false)]
    public ?float $total_due = null;

    #[ApiProperty(writable: false)]
    public ?float $base_total_due = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_total_due = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;

    #[ApiProperty(writable: false)]
    public ?array $customer = null;

    /**
     * Billing + shipping addresses (each carries `addressType`). A flat array —
     * the GraphQL counterpart is the `addresses` connection. NOTE: separate
     * `billingAddress` / `shippingAddress` props cannot be used here — with an
     * `output:` DTO, API Platform only serialises DTO props whose names match an
     * attribute/relation on the AdminOrderDetail resource, and that resource
     * exposes `addresses` (not billing/shipping), so only `addresses` survives.
     *
     * @var array<int, array<string, mixed>>
     */
    #[ApiProperty(writable: false)]
    public array $addresses = [];

    /** @var array<int, array<string, mixed>> */
    #[ApiProperty(writable: false)]
    public array $items = [];

    /** @var array<int, array<string, mixed>> */
    #[ApiProperty(writable: false)]
    public array $invoices = [];

    /** @var array<int, array<string, mixed>> */
    #[ApiProperty(writable: false)]
    public array $shipments = [];

    /** @var array<int, array<string, mixed>> */
    #[ApiProperty(writable: false)]
    public array $refunds = [];

    /** @var array<int, array<string, mixed>> */
    #[ApiProperty(writable: false)]
    public array $comments = [];
}
