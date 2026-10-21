<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsChannelCreateInput
{
    /**
     * The code.
     */
    #[ApiProperty(description: 'Unique alpha-dash channel code (e.g. "default", "us_store").')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The name.
     */
    #[ApiProperty(description: 'Display name (broadcast to all configured locales).')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty(description: 'Optional description (translatable).')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    /**
     * The hostname.
     */
    #[ApiProperty(description: 'Hostname (e.g. "shop.example.com"). Must be unique when set.')]
    #[Groups(['mutation'])]
    public ?string $hostname = null;

    /**
     * The theme.
     */
    #[ApiProperty(description: 'Optional theme code.')]
    #[Groups(['mutation'])]
    public ?string $theme = null;

    /**
     * The timezone.
     */
    #[ApiProperty(description: 'Optional timezone (e.g. "UTC", "America/New_York").')]
    #[Groups(['mutation'])]
    public ?string $timezone = null;

    /**
     * The ids of the locales attached to the channel.
     *
     * @var array<int>|null
     */
    #[ApiProperty(description: 'Locale ids attached to this channel.')]
    #[Groups(['mutation'])]
    public ?array $locales = null;

    /**
     * The default locale id.
     */
    #[ApiProperty(description: 'Default locale id (must appear in `locales`).')]
    #[Groups(['mutation'])]
    public ?int $default_locale_id = null;

    /**
     * The ids of the currencies attached to the channel.
     *
     * @var array<int>|null
     */
    #[ApiProperty(description: 'Currency ids attached to this channel.')]
    #[Groups(['mutation'])]
    public ?array $currencies = null;

    /**
     * The base currency id.
     */
    #[ApiProperty(description: 'Base currency id (must appear in `currencies`).')]
    #[Groups(['mutation'])]
    public ?int $base_currency_id = null;

    /**
     * The ids of the inventory sources attached to the channel.
     *
     * @var array<int>|null
     */
    #[ApiProperty(description: 'Inventory source ids attached to this channel.')]
    #[Groups(['mutation'])]
    public ?array $inventory_sources = null;

    /**
     * The root category id.
     */
    #[ApiProperty(description: 'Root category id.')]
    #[Groups(['mutation'])]
    public ?int $root_category_id = null;

    /**
     * The seo title.
     */
    #[ApiProperty(description: 'SEO title (rolled into home_seo).')]
    #[Groups(['mutation'])]
    public ?string $seo_title = null;

    /**
     * The seo description.
     */
    #[ApiProperty(description: 'SEO description.')]
    #[Groups(['mutation'])]
    public ?string $seo_description = null;

    /**
     * The seo keywords.
     */
    #[ApiProperty(description: 'SEO keywords.')]
    #[Groups(['mutation'])]
    public ?string $seo_keywords = null;

    /**
     * The is maintenance on.
     */
    #[ApiProperty(description: 'Maintenance-mode flag.')]
    #[Groups(['mutation'])]
    public ?bool $is_maintenance_on = null;

    /**
     * The maintenance mode text.
     */
    #[ApiProperty(description: 'Maintenance-mode text (translatable).')]
    #[Groups(['mutation'])]
    public ?string $maintenance_mode_text = null;

    /**
     * The allowed ips.
     */
    #[ApiProperty(description: 'Allowed IPs (free-form).')]
    #[Groups(['mutation'])]
    public ?string $allowed_ips = null;

    /**
     * The logo.
     */
    #[ApiProperty(description: 'Optional storage path of an already-uploaded logo (binary upload deferred).')]
    #[Groups(['mutation'])]
    public ?string $logo = null;

    /**
     * The favicon.
     */
    #[ApiProperty(description: 'Optional storage path of an already-uploaded favicon (binary upload deferred).')]
    #[Groups(['mutation'])]
    public ?string $favicon = null;

    /**
     * The per-locale overrides of the channel content, keyed by locale code.
     *
     * @var array<string,array<string,mixed>>|null
     */
    #[ApiProperty(description: 'Optional locale-nested overrides: { "<locale>": { name, description, home_page_content, footer_content, seo_title, seo_description, seo_keywords, maintenance_mode_text } }. Top-level fields are broadcast to every locale unless overridden here.')]
    #[Groups(['mutation'])]
    public ?array $translations = null;
}
