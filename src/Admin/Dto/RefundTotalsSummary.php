<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class RefundTotalsSummary
{
    use AcceptsCamelCaseWrites;

    /**
     * The order id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $order_id = null;

    /**
     * The subtotal.
     */
    public ?float $subtotal = null;

    /**
     * The formatted subtotal.
     */
    public ?string $formatted_subtotal = null;

    /**
     * The discount.
     */
    public ?float $discount = null;

    /**
     * The formatted discount.
     */
    public ?string $formatted_discount = null;

    /**
     * The tax.
     */
    public ?float $tax = null;

    /**
     * The formatted tax.
     */
    public ?string $formatted_tax = null;

    /**
     * The shipping.
     */
    public ?float $shipping = null;

    /**
     * The formatted shipping.
     */
    public ?string $formatted_shipping = null;

    /**
     * The adjustment refund.
     */
    public ?float $adjustment_refund = null;

    /**
     * The adjustment fee.
     */
    public ?float $adjustment_fee = null;

    /**
     * The grand total.
     */
    public ?float $grand_total = null;

    /**
     * The formatted grand total.
     */
    public ?string $formatted_grand_total = null;
}
