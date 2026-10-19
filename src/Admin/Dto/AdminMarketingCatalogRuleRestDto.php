<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminMarketingCatalogRuleRestDto
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
     * The sort order.
     */
    #[ApiProperty(writable: false)]
    public ?int $sort_order = null;

    /**
     * The condition type.
     */
    #[ApiProperty(writable: false)]
    public ?int $condition_type = null;

    /** @var array<int,mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $conditions = null;

    /**
     * The end other rules.
     */
    #[ApiProperty(writable: false)]
    public ?int $end_other_rules = null;

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

    /** @var array<int,array{id:int, code:string|null, name:string|null}>|null */
    #[ApiProperty(writable: false)]
    public ?array $channels = null;

    /** @var array<int,array{id:int, code:string|null, name:string|null}>|null */
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
