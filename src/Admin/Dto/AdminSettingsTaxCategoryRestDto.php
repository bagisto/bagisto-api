<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

/**
 * REST output for AdminSettingsTaxCategory (detail + listing). Snake_case props
 * surface as camelCase via the central converter (provider writes camelCase; the
 * trait maps it). `taxRates` is a flat JSON array (`[{id, identifier, taxRate}]`)
 * over REST; over GraphQL the same data is served as a connection off the
 * AdminSettingsTaxCategory Eloquent resource.
 *
 * IMPORTANT (the output:-DTO name-match trap, see CLAUDE.md OrderDetail notes):
 * with `output:` set, API Platform only serialises DTO props whose names match
 * an attribute/relation on the AdminSettingsTaxCategory Eloquent resource — so
 * the rates block MUST be named `tax_rates` (the relation), not `taxRates` or
 * any other name.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminSettingsTaxCategoryRestDto
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $code = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $description = null;

    /**
     * @var array<int, array{id:int, identifier:string|null, taxRate:float|null}>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $tax_rates = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
