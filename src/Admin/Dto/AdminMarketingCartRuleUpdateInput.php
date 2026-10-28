<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingCartRuleUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/cart-rules/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The name.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $description = null;

    /**
     * The channels.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?array $channels = null;

    /**
     * The customer groups.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?array $customer_groups = null;

    /**
     * The starts from.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $starts_from = null;

    /**
     * The ends till.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $ends_till = null;

    /**
     * The status.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $status = null;

    /**
     * The coupon type.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $coupon_type = null;

    /**
     * The use auto generation.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $use_auto_generation = null;

    /**
     * The coupon code.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $coupon_code = null;

    /**
     * The usage per customer.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $usage_per_customer = null;

    /**
     * The uses per coupon.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $uses_per_coupon = null;

    /**
     * The condition type.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $condition_type = null;

    /**
     * The conditions.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?array $conditions = null;

    /**
     * The action type.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $action_type = null;

    /**
     * The discount amount.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?float $discount_amount = null;

    /**
     * The discount quantity.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $discount_quantity = null;

    /**
     * The discount step.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $discount_step = null;

    /**
     * The apply to shipping.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $apply_to_shipping = null;

    /**
     * The free shipping.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $free_shipping = null;

    /**
     * The end other rules.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $end_other_rules = null;

    /**
     * The uses attribute conditions.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $uses_attribute_conditions = null;

    /**
     * The sort order.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $sort_order = null;
}
