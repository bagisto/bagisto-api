<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class OrderRefundDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $increment_id = null;

    public ?string $created_at = null;

    public ?float $sub_total = null;

    public ?float $base_sub_total = null;

    public ?float $grand_total = null;

    public ?float $base_grand_total = null;

    public ?float $tax_amount = null;

    public ?float $base_tax_amount = null;

    public ?float $shipping_amount = null;

    public ?float $base_shipping_amount = null;

    public ?float $discount_amount = null;

    public ?float $base_discount_amount = null;

    public ?float $adjustment_fee = null;

    public ?float $base_adjustment_fee = null;

    public ?float $adjustment_refund = null;

    public ?float $base_adjustment_refund = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];
}
