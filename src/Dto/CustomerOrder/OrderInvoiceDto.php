<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class OrderInvoiceDto
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
     * The sub total incl tax.
     */
    public ?float $sub_total_incl_tax = null;

    /**
     * The base sub total incl tax.
     */
    public ?float $base_sub_total_incl_tax = null;

    /**
     * The shipping amount incl tax.
     */
    public ?float $shipping_amount_incl_tax = null;

    /**
     * The base shipping amount incl tax.
     */
    public ?float $base_shipping_amount_incl_tax = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];
}
