<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/channels.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\ChannelController::store
 * validation:
 *   - code: required, unique, alpha-dash (Webkul\Core\Rules\Code)
 *   - name: required (top-level on create; broadcast to all locales by the repo)
 *   - hostname: unique (nullable)
 *   - locales: required array of locale ids
 *   - default_locale_id: required, must be one of `locales`
 *   - currencies: required array of currency ids
 *   - base_currency_id: required, must be one of `currencies`
 *   - inventory_sources: required array of inventory_source ids
 *   - root_category_id: required, must exist
 *   - seo_title / seo_description / seo_keywords: required strings (rolled into home_seo)
 *
 * Image uploads (logo / favicon) are deferred — accept a string path only.
 * Mirrors the Phase 5.11 image-upload-deferral pattern.
 */
class AdminSettingsChannelCreateInput
{
    #[ApiProperty(description: 'Unique alpha-dash channel code (e.g. "default", "us_store").')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty(description: 'Display name (broadcast to all configured locales).')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Optional description (translatable).')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    #[ApiProperty(description: 'Hostname (e.g. "shop.example.com"). Must be unique when set.')]
    #[Groups(['mutation'])]
    public ?string $hostname = null;

    #[ApiProperty(description: 'Optional theme code.')]
    #[Groups(['mutation'])]
    public ?string $theme = null;

    #[ApiProperty(description: 'Optional timezone (e.g. "UTC", "America/New_York").')]
    #[Groups(['mutation'])]
    public ?string $timezone = null;

    /** @var array<int>|null */
    #[ApiProperty(description: 'Locale ids attached to this channel.')]
    #[Groups(['mutation'])]
    public ?array $locales = null;

    #[ApiProperty(description: 'Default locale id (must appear in `locales`).')]
    #[Groups(['mutation'])]
    public ?int $default_locale_id = null;

    /** @var array<int>|null */
    #[ApiProperty(description: 'Currency ids attached to this channel.')]
    #[Groups(['mutation'])]
    public ?array $currencies = null;

    #[ApiProperty(description: 'Base currency id (must appear in `currencies`).')]
    #[Groups(['mutation'])]
    public ?int $base_currency_id = null;

    /** @var array<int>|null */
    #[ApiProperty(description: 'Inventory source ids attached to this channel.')]
    #[Groups(['mutation'])]
    public ?array $inventory_sources = null;

    #[ApiProperty(description: 'Root category id.')]
    #[Groups(['mutation'])]
    public ?int $root_category_id = null;

    #[ApiProperty(description: 'SEO title (rolled into home_seo).')]
    #[Groups(['mutation'])]
    public ?string $seo_title = null;

    #[ApiProperty(description: 'SEO description.')]
    #[Groups(['mutation'])]
    public ?string $seo_description = null;

    #[ApiProperty(description: 'SEO keywords.')]
    #[Groups(['mutation'])]
    public ?string $seo_keywords = null;

    #[ApiProperty(description: 'Maintenance-mode flag.')]
    #[Groups(['mutation'])]
    public ?bool $is_maintenance_on = null;

    #[ApiProperty(description: 'Maintenance-mode text (translatable).')]
    #[Groups(['mutation'])]
    public ?string $maintenance_mode_text = null;

    #[ApiProperty(description: 'Allowed IPs (free-form).')]
    #[Groups(['mutation'])]
    public ?string $allowed_ips = null;

    #[ApiProperty(description: 'Optional storage path of an already-uploaded logo (binary upload deferred).')]
    #[Groups(['mutation'])]
    public ?string $logo = null;

    #[ApiProperty(description: 'Optional storage path of an already-uploaded favicon (binary upload deferred).')]
    #[Groups(['mutation'])]
    public ?string $favicon = null;

    /** @var array<string,array<string,mixed>>|null */
    #[ApiProperty(description: 'Optional locale-nested overrides: { "<locale>": { name, description, home_page_content, footer_content, seo_title, seo_description, seo_keywords, maintenance_mode_text } }. Top-level fields are broadcast to every locale unless overridden here.')]
    #[Groups(['mutation'])]
    public ?array $translations = null;
}
