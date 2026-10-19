<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsThemeCreateInput
{
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
    #[ApiProperty(description: 'Customization type — one of product_carousel, category_carousel, static_content, image_carousel, footer_links, services_content.')]
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
    #[ApiProperty(description: 'Theme code this customization belongs to (e.g. default).')]
    #[Groups(['mutation'])]
    public ?string $theme_code = null;

    /**
     * The status.
     */
    #[ApiProperty(description: 'Status (true = enabled).')]
    #[Groups(['mutation'])]
    public ?bool $status = null;
}
