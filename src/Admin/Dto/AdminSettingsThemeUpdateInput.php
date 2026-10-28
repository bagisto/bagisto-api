<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/settings/themes/{id} and the update/delete mutations.
 */
class AdminSettingsThemeUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/themes/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Theme customization name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Sort order (integer).')]
    #[Groups(['mutation'])]
    public ?int $sort_order = null;

    #[ApiProperty(description: 'Customization type — see create input for allowed values.')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    #[ApiProperty(description: 'Channel id this customization belongs to.')]
    #[Groups(['mutation'])]
    public ?int $channel_id = null;

    #[ApiProperty(description: 'Theme code this customization belongs to.')]
    #[Groups(['mutation'])]
    public ?string $theme_code = null;

    #[ApiProperty(description: 'Status (true = enabled).')]
    #[Groups(['mutation'])]
    public ?bool $status = null;

    #[ApiProperty(description: 'Locale code under which translatable options are stored.')]
    #[Groups(['mutation'])]
    public ?string $locale = null;

    /** @var array<string,mixed>|null */
    #[ApiProperty(description: 'Per-locale options blob (associative array). Shape depends on `type`. Image uploads deferred — use path strings.')]
    #[Groups(['mutation'])]
    public ?array $options = null;
}
