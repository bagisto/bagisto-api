<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

/**
 * Output of POST /api/admin/orders/{id}/refunds/preview — `subtotal`, `discount`,
 * `tax`, `shipping`, `grand_total` plus pre-formatted variants, computed via
 * `RefundRepository::getOrderItemsRefundSummary()`.
 *
 * Properties are snake_case so they resolve over both REST and GraphQL; the name
 * converter surfaces them as camelCase. The AcceptsCamelCaseWrites trait lets the
 * processor keep assigning camelCase ($summary->grandTotal = …).
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class RefundTotalsSummary
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $order_id = null;

    public ?float $subtotal = null;

    public ?string $formatted_subtotal = null;

    public ?float $discount = null;

    public ?string $formatted_discount = null;

    public ?float $tax = null;

    public ?string $formatted_tax = null;

    public ?float $shipping = null;

    public ?string $formatted_shipping = null;

    public ?float $adjustment_refund = null;

    public ?float $adjustment_fee = null;

    public ?float $grand_total = null;

    public ?string $formatted_grand_total = null;
}
