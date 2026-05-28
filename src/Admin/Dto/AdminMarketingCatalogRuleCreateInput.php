<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/marketing/catalog-rules.
 *
 * Mirrors Webkul\Admin\Http\Requests\CatalogRuleRequest validation:
 *   - name             required
 *   - channels         required|array|min:1
 *   - customer_groups  required|array|min:1
 *   - starts_from      nullable|date
 *   - ends_till        nullable|date|after_or_equal:starts_from
 *   - action_type      required (in: by_percent / by_fixed / to_percent / to_fixed)
 *   - discount_amount  required|numeric (0..100 when action_type=by_percent)
 */
class AdminMarketingCatalogRuleCreateInput
{
    #[ApiProperty(description: 'Rule display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Long-form description.')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    #[ApiProperty(description: 'Start date (Y-m-d). Nullable = no lower bound.')]
    #[Groups(['mutation'])]
    public ?string $starts_from = null;

    #[ApiProperty(description: 'End date (Y-m-d). Nullable = no upper bound.')]
    #[Groups(['mutation'])]
    public ?string $ends_till = null;

    #[ApiProperty(description: 'Enabled flag (0/1).')]
    #[Groups(['mutation'])]
    public ?int $status = null;

    #[ApiProperty(description: 'Sort order (lower runs first).')]
    #[Groups(['mutation'])]
    public ?int $sort_order = null;

    #[ApiProperty(description: 'Condition matching: 1 = ALL, 0 = ANY.')]
    #[Groups(['mutation'])]
    public ?int $condition_type = null;

    /**
     * @var array<int,array<string,mixed>>|null
     */
    #[ApiProperty(description: 'Conditions JSON — array of {attribute, operator, value, ...} rows.')]
    #[Groups(['mutation'])]
    public ?array $conditions = null;

    #[ApiProperty(description: 'Stop processing other rules after this one matches (0/1).')]
    #[Groups(['mutation'])]
    public ?int $end_other_rules = null;

    #[ApiProperty(description: 'Discount action type: by_percent | by_fixed | to_percent | to_fixed.')]
    #[Groups(['mutation'])]
    public ?string $action_type = null;

    #[ApiProperty(description: 'Discount amount. For by_percent: 0..100.')]
    #[Groups(['mutation'])]
    public ?float $discount_amount = null;

    /**
     * @var int[]|null
     */
    #[ApiProperty(description: 'Channel ids to attach via catalog_rule_channels pivot.')]
    #[Groups(['mutation'])]
    public ?array $channels = null;

    /**
     * @var int[]|null
     */
    #[ApiProperty(description: 'Customer group ids to attach via catalog_rule_customer_groups pivot.')]
    #[Groups(['mutation'])]
    public ?array $customer_groups = null;
}
