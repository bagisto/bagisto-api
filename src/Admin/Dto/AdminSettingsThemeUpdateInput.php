<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsThemeUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/themes/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The name.
     */
    #[ApiProperty(description: 'Theme customization name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The sort order.
     */
    #[ApiProperty(description: 'Sort order (integer).')]
    #[Groups(['mutation'])]
    public ?int $sort_order = null;

    /**
     * The type.
     */
    #[ApiProperty(description: 'Customization type — see create input for allowed values.')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    /**
     * The channel id.
     */
    #[ApiProperty(description: 'Channel id this customization belongs to.')]
    #[Groups(['mutation'])]
    public ?int $channel_id = null;

    /**
     * The theme code.
     */
    #[ApiProperty(description: 'Theme code this customization belongs to.')]
    #[Groups(['mutation'])]
    public ?string $theme_code = null;

    /**
     * The status.
     */
    #[ApiProperty(description: 'Status (true = enabled).')]
    #[Groups(['mutation'])]
    public ?bool $status = null;

    /**
     * The locale.
     */
    #[ApiProperty(description: 'Locale code under which translatable options are stored.')]
    #[Groups(['mutation'])]
    public ?string $locale = null;

    /** @var array<string,mixed>|null */
    #[ApiProperty(description: 'Per-locale options blob (associative array). Shape depends on `type`. Image uploads deferred — use path strings.')]
    #[Groups(['mutation'])]
    public ?array $options = null;
}
