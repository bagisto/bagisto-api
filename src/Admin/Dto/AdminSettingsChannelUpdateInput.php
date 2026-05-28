<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/settings/channels/{id} and the delete mutation.
 *
 * Update validation: code/hostname uniqueness excludes the current id;
 * translatable fields use the locale-nested `translations` map.
 */
class AdminSettingsChannelUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/channels/3). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Channel code (alpha-dash, unique).')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty(description: 'Display name (broadcast to all configured locales unless `translations` overrides).')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Description.')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    #[ApiProperty(description: 'Hostname (unique when set).')]
    #[Groups(['mutation'])]
    public ?string $hostname = null;

    #[ApiProperty(description: 'Optional theme code.')]
    #[Groups(['mutation'])]
    public ?string $theme = null;

    #[ApiProperty(description: 'Optional timezone.')]
    #[Groups(['mutation'])]
    public ?string $timezone = null;

    /** @var array<int>|null */
    #[ApiProperty(description: 'Locale ids attached to this channel.')]
    #[Groups(['mutation'])]
    public ?array $locales = null;

    #[ApiProperty(description: 'Default locale id.')]
    #[Groups(['mutation'])]
    public ?int $default_locale_id = null;

    /** @var array<int>|null */
    #[ApiProperty(description: 'Currency ids attached to this channel.')]
    #[Groups(['mutation'])]
    public ?array $currencies = null;

    #[ApiProperty(description: 'Base currency id.')]
    #[Groups(['mutation'])]
    public ?int $base_currency_id = null;

    /** @var array<int>|null */
    #[ApiProperty(description: 'Inventory source ids attached to this channel.')]
    #[Groups(['mutation'])]
    public ?array $inventory_sources = null;

    #[ApiProperty(description: 'Root category id.')]
    #[Groups(['mutation'])]
    public ?int $root_category_id = null;

    #[ApiProperty(description: 'SEO title.')]
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

    #[ApiProperty(description: 'Storage path of an already-uploaded logo (binary upload deferred).')]
    #[Groups(['mutation'])]
    public ?string $logo = null;

    #[ApiProperty(description: 'Storage path of an already-uploaded favicon (binary upload deferred).')]
    #[Groups(['mutation'])]
    public ?string $favicon = null;

    /** @var array<string,array<string,mixed>>|null */
    #[ApiProperty(description: 'Locale-nested overrides — see Create DTO.')]
    #[Groups(['mutation'])]
    public ?array $translations = null;
}
