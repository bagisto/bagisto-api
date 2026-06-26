<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

/**
 * REST output for AdminMarketingCatalogRule (detail + listing). Snake_case props
 * surface as camelCase via the central converter (the provider writes camelCase;
 * the trait maps it).
 *
 * REST exposes the assigned channels / customer groups as flat arrays of objects
 * (no connections) — these REPLACE the old bare int-array `channels` / `customer_groups`:
 *   - `channels`        → array of `{id, code, name}`
 *   - `customer_groups` → array of `{id, code, name}`  (surfaces as customerGroups)
 *   - `conditions`      → JSON (dynamic rule rows, left as-is)
 *
 * IMPORTANT (the output:-DTO name-match trap, see CLAUDE.md): with `output:` set,
 * API Platform only serialises DTO props whose names match an attribute/relation
 * on the AdminMarketingCatalogRule Eloquent resource — so the nested blocks MUST
 * be named `channels` / `customer_groups` (the relations).
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminMarketingCatalogRuleRestDto
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $description = null;

    #[ApiProperty(writable: false)]
    public ?string $starts_from = null;

    #[ApiProperty(writable: false)]
    public ?string $ends_till = null;

    #[ApiProperty(writable: false)]
    public ?int $status = null;

    #[ApiProperty(writable: false)]
    public ?int $sort_order = null;

    #[ApiProperty(writable: false)]
    public ?int $condition_type = null;

    /** @var array<int,mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $conditions = null;

    #[ApiProperty(writable: false)]
    public ?int $end_other_rules = null;

    #[ApiProperty(writable: false)]
    public ?string $action_type = null;

    #[ApiProperty(writable: false)]
    public ?float $discount_amount = null;

    /** @var array<int,array{id:int, code:string|null, name:string|null}>|null */
    #[ApiProperty(writable: false)]
    public ?array $channels = null;

    /** @var array<int,array{id:int, code:string|null, name:string|null}>|null */
    #[ApiProperty(writable: false)]
    public ?array $customer_groups = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
