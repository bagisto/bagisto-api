<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class OrderRefundDto
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The increment id.
     */
    public ?string $increment_id = null;

    /**
     * The created at.
     */
    public ?string $created_at = null;

    /**
     * The sub total.
     */
    public ?float $sub_total = null;

    /**
     * The base sub total.
     */
    public ?float $base_sub_total = null;

    /**
     * The grand total.
     */
    public ?float $grand_total = null;

    /**
     * The base grand total.
     */
    public ?float $base_grand_total = null;

    /**
     * The tax amount.
     */
    public ?float $tax_amount = null;

    /**
     * The base tax amount.
     */
    public ?float $base_tax_amount = null;

    /**
     * The shipping amount.
     */
    public ?float $shipping_amount = null;

    /**
     * The base shipping amount.
     */
    public ?float $base_shipping_amount = null;

    /**
     * The discount amount.
     */
    public ?float $discount_amount = null;

    /**
     * The base discount amount.
     */
    public ?float $base_discount_amount = null;

    /**
     * The adjustment fee.
     */
    public ?float $adjustment_fee = null;

    /**
     * The base adjustment fee.
     */
    public ?float $base_adjustment_fee = null;

    /**
     * The adjustment refund.
     */
    public ?float $adjustment_refund = null;

    /**
     * The base adjustment refund.
     */
    public ?float $base_adjustment_refund = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];
}
