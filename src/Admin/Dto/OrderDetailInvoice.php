<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderDetailInvoice
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The increment id.
     */
    public ?string $incrementId = null;

    /**
     * The state.
     */
    public ?string $state = null;

    /**
     * The email sent.
     */
    public ?bool $emailSent = null;

    /**
     * The total qty.
     */
    public ?int $totalQty = null;

    /**
     * The sub total.
     */
    public ?float $subTotal = null;

    /**
     * The formatted sub total.
     */
    public ?string $formattedSubTotal = null;

    /**
     * The grand total.
     */
    public ?float $grandTotal = null;

    /**
     * The formatted grand total.
     */
    public ?string $formattedGrandTotal = null;

    /**
     * The tax amount.
     */
    public ?float $taxAmount = null;

    /**
     * The discount amount.
     */
    public ?float $discountAmount = null;

    /**
     * The shipping amount.
     */
    public ?float $shippingAmount = null;

    /**
     * The transaction id.
     */
    public ?string $transactionId = null;

    /**
     * The created at.
     */
    public ?string $createdAt = null;
}
