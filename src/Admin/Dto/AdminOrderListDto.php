<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * REST output for the AdminOrder listing. Flat shape — `items` is a plain array
 * (the historical REST payload). Snake_case props surface as camelCase via the
 * central output converter (the provider writes snake_case). Over GraphQL the
 * same data is served off the AdminOrder Eloquent resource with `items` as a
 * field-selectable connection.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminOrderListDto
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $increment_id = null;

    #[ApiProperty(writable: false)]
    public ?string $status = null;

    #[ApiProperty(writable: false)]
    public ?string $status_label = null;

    #[ApiProperty(writable: false)]
    public ?int $channel_id = null;

    #[ApiProperty(writable: false)]
    public ?string $channel_name = null;

    #[ApiProperty(writable: false)]
    public ?bool $is_guest = null;

    #[ApiProperty(writable: false)]
    public ?int $customer_id = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_email = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_name = null;

    #[ApiProperty(writable: false)]
    public ?string $payment_title = null;

    #[ApiProperty(writable: false)]
    public ?string $coupon_code = null;

    #[ApiProperty(writable: false)]
    public ?int $total_item_count = null;

    #[ApiProperty(writable: false)]
    public ?int $total_qty_ordered = null;

    #[ApiProperty(writable: false)]
    public ?string $order_currency_code = null;

    #[ApiProperty(writable: false)]
    public ?float $grand_total = null;

    #[ApiProperty(writable: false)]
    public ?float $base_grand_total = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_grand_total = null;

    #[ApiProperty(writable: false)]
    public ?string $location = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;

    /** @var array<int, array<string, mixed>> */
    #[ApiProperty(writable: false)]
    public array $items = [];
}
