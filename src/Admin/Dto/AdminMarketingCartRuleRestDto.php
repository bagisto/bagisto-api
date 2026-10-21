<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminMarketingCartRuleRestDto
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The name.
     */
    #[ApiProperty(writable: false)]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty(writable: false)]
    public ?string $description = null;

    /**
     * The starts from.
     */
    #[ApiProperty(writable: false)]
    public ?string $starts_from = null;

    /**
     * The ends till.
     */
    #[ApiProperty(writable: false)]
    public ?string $ends_till = null;

    /**
     * The status.
     */
    #[ApiProperty(writable: false)]
    public ?int $status = null;

    /**
     * The coupon type.
     */
    #[ApiProperty(writable: false)]
    public ?int $coupon_type = null;

    /**
     * The use auto generation.
     */
    #[ApiProperty(writable: false)]
    public ?int $use_auto_generation = null;

    /**
     * The usage per customer.
     */
    #[ApiProperty(writable: false)]
    public ?int $usage_per_customer = null;

    /**
     * The uses per coupon.
     */
    #[ApiProperty(writable: false)]
    public ?int $uses_per_coupon = null;

    /**
     * The times used.
     */
    #[ApiProperty(writable: false)]
    public ?int $times_used = null;

    /**
     * The condition type.
     */
    #[ApiProperty(writable: false)]
    public ?int $condition_type = null;

    /**
     * The conditions a cart must meet for the cart rule to apply.
     *
     * @var array<int,mixed>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $conditions = null;

    /**
     * The action type.
     */
    #[ApiProperty(writable: false)]
    public ?string $action_type = null;

    /**
     * The discount amount.
     */
    #[ApiProperty(writable: false)]
    public ?float $discount_amount = null;

    /**
     * The discount quantity.
     */
    #[ApiProperty(writable: false)]
    public ?int $discount_quantity = null;

    /**
     * The discount step.
     */
    #[ApiProperty(writable: false)]
    public ?string $discount_step = null;

    /**
     * The apply to shipping.
     */
    #[ApiProperty(writable: false)]
    public ?int $apply_to_shipping = null;

    /**
     * The free shipping.
     */
    #[ApiProperty(writable: false)]
    public ?int $free_shipping = null;

    /**
     * The end other rules.
     */
    #[ApiProperty(writable: false)]
    public ?int $end_other_rules = null;

    /**
     * The uses attribute conditions.
     */
    #[ApiProperty(writable: false)]
    public ?int $uses_attribute_conditions = null;

    /**
     * The sort order.
     */
    #[ApiProperty(writable: false)]
    public ?int $sort_order = null;

    /**
     * The coupon code.
     */
    #[ApiProperty(writable: false)]
    public ?string $coupon_code = null;

    /**
     * The channels the cart rule applies in.
     *
     * @var array<int,array{id:int, code:string|null, name:string|null}>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $channels = null;

    /**
     * The customer groups the cart rule applies to.
     *
     * @var array<int,array{id:int, code:string|null, name:string|null}>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $customer_groups = null;

    /**
     * The created at.
     */
    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
