<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminRefundDetailDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $orderId = null;

    public ?string $state = null;

    public ?int $totalQty = null;

    public ?string $orderCurrencyCode = null;

    public ?float $subTotal = null;

    public ?string $formattedSubTotal = null;

    public ?float $grandTotal = null;

    public ?string $formattedGrandTotal = null;

    public ?float $shippingAmount = null;

    public ?string $formattedShippingAmount = null;

    public ?float $adjustmentRefund = null;

    public ?string $formattedAdjustmentRefund = null;

    public ?float $adjustmentFee = null;

    public ?string $formattedAdjustmentFee = null;

    public ?float $taxAmount = null;

    public ?string $formattedTaxAmount = null;

    public ?float $discountAmount = null;

    public ?string $formattedDiscountAmount = null;

    public ?string $createdAt = null;

    public ?string $updatedAt = null;

    /** @var OrderActionItemDto[] */
    public array $items = [];
}
