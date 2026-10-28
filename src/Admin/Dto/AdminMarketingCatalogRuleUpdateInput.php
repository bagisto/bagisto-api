<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingCatalogRuleUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/catalog-rules/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The name.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $description = null;

    /**
     * The starts from.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $starts_from = null;

    /**
     * The ends till.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $ends_till = null;

    /**
     * The status.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;

    /**
     * The sort order.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $sort_order = null;

    /**
     * The condition type.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $condition_type = null;

    /**
     * The conditions a product must meet for the catalog rule to apply.
     *
     * @var array<int,array<string,mixed>>|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $conditions = null;

    /**
     * The end other rules.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $end_other_rules = null;

    /**
     * The action type.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $action_type = null;

    /**
     * The discount amount.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?float $discount_amount = null;

    /**
     * The ids of the channels the catalog rule applies in.
     *
     * @var int[]|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $channels = null;

    /**
     * The ids of the customer groups the catalog rule applies to.
     *
     * @var int[]|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $customer_groups = null;
}
