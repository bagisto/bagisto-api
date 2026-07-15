<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Invoice block embedded in the order detail.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderDetailInvoice
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $incrementId = null;

    public ?string $state = null;

    public ?bool $emailSent = null;

    public ?int $totalQty = null;

    public ?float $subTotal = null;

    public ?string $formattedSubTotal = null;

    public ?float $grandTotal = null;

    public ?string $formattedGrandTotal = null;

    public ?float $taxAmount = null;

    public ?float $discountAmount = null;

    public ?float $shippingAmount = null;

    public ?string $transactionId = null;

    public ?string $createdAt = null;
}
