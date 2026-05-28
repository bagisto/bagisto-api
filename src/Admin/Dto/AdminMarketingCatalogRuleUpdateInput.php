<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/marketing/catalog-rules/{id} and the delete mutation.
 */
class AdminMarketingCatalogRuleUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/catalog-rules/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $description = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $starts_from = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $ends_till = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $sort_order = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $condition_type = null;

    /**
     * @var array<int,array<string,mixed>>|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $conditions = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $end_other_rules = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $action_type = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?float $discount_amount = null;

    /**
     * @var int[]|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $channels = null;

    /**
     * @var int[]|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $customer_groups = null;
}
