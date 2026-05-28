<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/marketing/cart-rules/{id} and the GraphQL
 * update/delete mutations (delete reuses this DTO — only `id` is required).
 */
class AdminMarketingCartRuleUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/cart-rules/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $description = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?array $channels = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?array $customer_groups = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $starts_from = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $ends_till = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $status = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $coupon_type = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $use_auto_generation = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $coupon_code = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $usage_per_customer = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $uses_per_coupon = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $condition_type = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?array $conditions = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $action_type = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?float $discount_amount = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $discount_quantity = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $discount_step = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $apply_to_shipping = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $free_shipping = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $end_other_rules = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $uses_attribute_conditions = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $sort_order = null;
}
