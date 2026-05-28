<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * List-view shape for /customer-orders.
 *
 * Mirrors the GraphQL `customerOrders` query field set so REST and GraphQL
 * lists return the same payload. Heavy or admin-only fields (-invoiced,
 * -refunded, polymorphic IDs, cart references, dangling relation IRIs) live
 * only on the detail endpoint.
 *
 * Relations (items, addresses, payment, shipments) intentionally omitted —
 * the detail endpoint serves those.
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class CustomerOrderListDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $increment_id = null;

    public ?string $status = null;

    public ?string $channel_name = null;

    public ?string $customer_email = null;

    public ?string $customer_first_name = null;

    public ?string $customer_last_name = null;

    public ?string $shipping_method = null;

    public ?string $shipping_title = null;

    public ?string $coupon_code = null;

    public ?int $total_item_count = null;

    public ?int $total_qty_ordered = null;

    public ?float $grand_total = null;

    public ?float $base_grand_total = null;

    public ?float $sub_total = null;

    public ?float $base_sub_total = null;

    public ?float $tax_amount = null;

    public ?float $shipping_amount = null;

    public ?float $discount_amount = null;

    public ?string $base_currency_code = null;

    public ?string $order_currency_code = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}
