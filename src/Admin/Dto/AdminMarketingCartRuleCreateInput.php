<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/marketing/cart-rules.
 *
 * Mirrors Bagisto admin CartRuleRequest validation.
 */
class AdminMarketingCartRuleCreateInput
{
    #[ApiProperty(description: 'Rule name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Description.')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    #[ApiProperty(description: 'Channel ids the rule applies to.')]
    #[Groups(['mutation'])]
    public ?array $channels = null;

    #[ApiProperty(description: 'Customer-group ids the rule applies to.')]
    #[Groups(['mutation'])]
    public ?array $customer_groups = null;

    #[ApiProperty(description: 'Start date (ISO 8601 / Y-m-d).')]
    #[Groups(['mutation'])]
    public ?string $starts_from = null;

    #[ApiProperty(description: 'End date (ISO 8601 / Y-m-d).')]
    #[Groups(['mutation'])]
    public ?string $ends_till = null;

    #[ApiProperty(description: 'Active flag (1 or 0).')]
    #[Groups(['mutation'])]
    public ?int $status = null;

    #[ApiProperty(description: 'Coupon type: 1=no coupon, 2=specific coupon.')]
    #[Groups(['mutation'])]
    public ?int $coupon_type = null;

    #[ApiProperty(description: 'Auto-generate coupon codes (only valid when coupon_type=2).')]
    #[Groups(['mutation'])]
    public ?int $use_auto_generation = null;

    #[ApiProperty(description: 'Coupon code (required when coupon_type=2 and use_auto_generation=0).')]
    #[Groups(['mutation'])]
    public ?string $coupon_code = null;

    #[ApiProperty(description: 'Per-customer usage cap (0 = unlimited).')]
    #[Groups(['mutation'])]
    public ?int $usage_per_customer = null;

    #[ApiProperty(description: 'Total uses per coupon (0 = unlimited).')]
    #[Groups(['mutation'])]
    public ?int $uses_per_coupon = null;

    #[ApiProperty(description: 'Condition combinator: 1=ALL, 0=ANY.')]
    #[Groups(['mutation'])]
    public ?int $condition_type = null;

    #[ApiProperty(description: 'Conditions JSON tree.')]
    #[Groups(['mutation'])]
    public ?array $conditions = null;

    #[ApiProperty(description: 'Action type: by_percent | by_fixed | cart_fixed | buy_x_get_y.')]
    #[Groups(['mutation'])]
    public ?string $action_type = null;

    #[ApiProperty(description: 'Discount amount.')]
    #[Groups(['mutation'])]
    public ?float $discount_amount = null;

    #[ApiProperty(description: 'Buy quantity (buy_x_get_y).')]
    #[Groups(['mutation'])]
    public ?int $discount_quantity = null;

    #[ApiProperty(description: 'Discount step (buy_x_get_y).')]
    #[Groups(['mutation'])]
    public ?string $discount_step = null;

    #[ApiProperty(description: 'Apply discount to shipping amount.')]
    #[Groups(['mutation'])]
    public ?int $apply_to_shipping = null;

    #[ApiProperty(description: 'Provide free shipping.')]
    #[Groups(['mutation'])]
    public ?int $free_shipping = null;

    #[ApiProperty(description: 'Stop processing further rules when this matches.')]
    #[Groups(['mutation'])]
    public ?int $end_other_rules = null;

    #[ApiProperty(description: 'Use attribute conditions extension.')]
    #[Groups(['mutation'])]
    public ?int $uses_attribute_conditions = null;

    #[ApiProperty(description: 'Sort order priority (lower = earlier).')]
    #[Groups(['mutation'])]
    public ?int $sort_order = null;
}
