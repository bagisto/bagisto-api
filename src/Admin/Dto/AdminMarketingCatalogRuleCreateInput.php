<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingCatalogRuleCreateInput
{
    /**
     * The name.
     */
    #[ApiProperty(description: 'Rule display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty(description: 'Long-form description.')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    /**
     * The starts from.
     */
    #[ApiProperty(description: 'Start date (Y-m-d). Nullable = no lower bound.')]
    #[Groups(['mutation'])]
    public ?string $starts_from = null;

    /**
     * The ends till.
     */
    #[ApiProperty(description: 'End date (Y-m-d). Nullable = no upper bound.')]
    #[Groups(['mutation'])]
    public ?string $ends_till = null;

    /**
     * The status.
     */
    #[ApiProperty(description: 'Enabled flag (0/1).')]
    #[Groups(['mutation'])]
    public ?int $status = null;

    /**
     * The sort order.
     */
    #[ApiProperty(description: 'Sort order (lower runs first).')]
    #[Groups(['mutation'])]
    public ?int $sort_order = null;

    /**
     * The condition type.
     */
    #[ApiProperty(description: 'Condition matching: 1 = ALL, 0 = ANY.')]
    #[Groups(['mutation'])]
    public ?int $condition_type = null;

    /**
     * The conditions a product must meet for the catalog rule to apply.
     *
     * @var array<int,array<string,mixed>>|null
     */
    #[ApiProperty(description: 'Conditions JSON — array of {attribute, operator, value, ...} rows.')]
    #[Groups(['mutation'])]
    public ?array $conditions = null;

    /**
     * The end other rules.
     */
    #[ApiProperty(description: 'Stop processing other rules after this one matches (0/1).')]
    #[Groups(['mutation'])]
    public ?int $end_other_rules = null;

    /**
     * The action type.
     */
    #[ApiProperty(description: 'Discount action type: by_percent | by_fixed | to_percent | to_fixed.')]
    #[Groups(['mutation'])]
    public ?string $action_type = null;

    /**
     * The discount amount.
     */
    #[ApiProperty(description: 'Discount amount. For by_percent: 0..100.')]
    #[Groups(['mutation'])]
    public ?float $discount_amount = null;

    /**
     * The ids of the channels the catalog rule applies in.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Channel ids to attach via catalog_rule_channels pivot.')]
    #[Groups(['mutation'])]
    public ?array $channels = null;

    /**
     * The ids of the customer groups the catalog rule applies to.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Customer group ids to attach via catalog_rule_customer_groups pivot.')]
    #[Groups(['mutation'])]
    public ?array $customer_groups = null;
}
