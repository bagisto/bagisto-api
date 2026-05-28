<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/themes.
 *
 * Mirrors Bagisto admin ThemeController::store validation:
 *   - name: required
 *   - sort_order: required, numeric
 *   - type: required, in (product_carousel, category_carousel, static_content,
 *                         image_carousel, footer_links, services_content)
 *   - channel_id: required, must exist in channels
 *   - theme_code: required string
 *
 * Note: options upload (image_carousel/services_content slides + static_content
 * inline images) is deferred — only path strings are accepted in v1.
 */
class AdminSettingsThemeCreateInput
{
    #[ApiProperty(description: 'Theme customization name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Sort order (integer).')]
    #[Groups(['mutation'])]
    public ?int $sort_order = null;

    #[ApiProperty(description: 'Customization type — one of product_carousel, category_carousel, static_content, image_carousel, footer_links, services_content.')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    #[ApiProperty(description: 'Channel id this customization belongs to.')]
    #[Groups(['mutation'])]
    public ?int $channel_id = null;

    #[ApiProperty(description: 'Theme code this customization belongs to (e.g. default).')]
    #[Groups(['mutation'])]
    public ?string $theme_code = null;

    #[ApiProperty(description: 'Status (true = enabled).')]
    #[Groups(['mutation'])]
    public ?bool $status = null;
}
