<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Output of POST /api/admin/orders/{id}/refunds/preview — `subtotal`, `discount`,
 * `tax`, `shipping`, `grandTotal` plus pre-formatted variants, computed via
 * `RefundRepository::getOrderItemsRefundSummary()`.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class RefundTotalsSummary
{
    #[ApiProperty(identifier: true)]
    public ?int $orderId = null;

    public ?float $subtotal = null;

    public ?string $formattedSubtotal = null;

    public ?float $discount = null;

    public ?string $formattedDiscount = null;

    public ?float $tax = null;

    public ?string $formattedTax = null;

    public ?float $shipping = null;

    public ?string $formattedShipping = null;

    public ?float $adjustmentRefund = null;

    public ?float $adjustmentFee = null;

    public ?float $grandTotal = null;

    public ?string $formattedGrandTotal = null;
}
