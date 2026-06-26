<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

/**
 * REST output for AdminSettingsChannel (detail + listing). Snake_case props
 * surface as camelCase via the central converter (provider writes camelCase;
 * the trait maps it).
 *
 * REST exposes the nested data as flat arrays of objects (no connections):
 *   - `locales` / `currencies` / `inventory_sources` → arrays of `{id, code, ...}`
 *     (these REPLACE the old localeIds/currencyIds/inventorySourceIds int arrays)
 *   - `translations` → flat array of `{locale, name, description, ...}`
 *   - `home_seo` → object `{meta_title, meta_description, meta_keywords}`
 *
 * IMPORTANT (the output:-DTO name-match trap, see CLAUDE.md OrderDetail notes):
 * with `output:` set, API Platform only serialises DTO props whose names match
 * an attribute/relation on the AdminSettingsChannel Eloquent resource — so the
 * nested blocks MUST be named `locales`/`currencies`/`inventory_sources`/
 * `translations`/`home_seo` (the relations), not any other name.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminSettingsChannelRestDto
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

    #[ApiProperty(writable: false)]
    public ?string $hostname = null;

    #[ApiProperty(writable: false)]
    public ?string $theme = null;

    #[ApiProperty(writable: false)]
    public ?string $timezone = null;

    #[ApiProperty(writable: false)]
    public ?int $default_locale_id = null;

    #[ApiProperty(writable: false)]
    public ?int $base_currency_id = null;

    #[ApiProperty(writable: false)]
    public ?int $root_category_id = null;

    #[ApiProperty(writable: false)]
    public ?bool $is_maintenance_on = null;

    #[ApiProperty(writable: false)]
    public ?string $maintenance_mode_text = null;

    /** @var array<int,string>|null */
    #[ApiProperty(writable: false)]
    public ?array $allowed_ips = null;

    #[ApiProperty(writable: false)]
    public ?string $logo = null;

    #[ApiProperty(writable: false)]
    public ?string $logo_url = null;

    #[ApiProperty(writable: false)]
    public ?string $favicon = null;

    #[ApiProperty(writable: false)]
    public ?string $favicon_url = null;

    /** @var array<int,array{id:int, code:string|null, name:string|null, direction:string|null}>|null */
    #[ApiProperty(writable: false)]
    public ?array $locales = null;

    /** @var array<int,array{id:int, code:string|null, name:string|null, symbol:string|null}>|null */
    #[ApiProperty(writable: false)]
    public ?array $currencies = null;

    /** @var array<int,array{id:int, code:string|null, name:string|null, status:int|null}>|null */
    #[ApiProperty(writable: false)]
    public ?array $inventory_sources = null;

    /** @var array<string,mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $home_seo = null;

    /** @var array<int,array<string,mixed>>|null */
    #[ApiProperty(writable: false)]
    public ?array $translations = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
