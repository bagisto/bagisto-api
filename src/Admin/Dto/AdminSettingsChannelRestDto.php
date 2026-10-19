<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminSettingsChannelRestDto
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The code.
     */
    #[ApiProperty(writable: false)]
    public ?string $code = null;

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
     * The hostname.
     */
    #[ApiProperty(writable: false)]
    public ?string $hostname = null;

    /**
     * The theme.
     */
    #[ApiProperty(writable: false)]
    public ?string $theme = null;

    /**
     * The timezone.
     */
    #[ApiProperty(writable: false)]
    public ?string $timezone = null;

    /**
     * The default locale id.
     */
    #[ApiProperty(writable: false)]
    public ?int $default_locale_id = null;

    /**
     * The base currency id.
     */
    #[ApiProperty(writable: false)]
    public ?int $base_currency_id = null;

    /**
     * The root category id.
     */
    #[ApiProperty(writable: false)]
    public ?int $root_category_id = null;

    /**
     * The is maintenance on.
     */
    #[ApiProperty(writable: false)]
    public ?bool $is_maintenance_on = null;

    /**
     * The maintenance mode text.
     */
    #[ApiProperty(writable: false)]
    public ?string $maintenance_mode_text = null;

    /** @var array<int,string>|null */
    #[ApiProperty(writable: false)]
    public ?array $allowed_ips = null;

    /**
     * The logo.
     */
    #[ApiProperty(writable: false)]
    public ?string $logo = null;

    /**
     * The logo url.
     */
    #[ApiProperty(writable: false)]
    public ?string $logo_url = null;

    /**
     * The favicon.
     */
    #[ApiProperty(writable: false)]
    public ?string $favicon = null;

    /**
     * The favicon url.
     */
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
