<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAppearanceSectionUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The name.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The type.
     */
    #[ApiProperty(description: 'Section type: image_carousel, product_carousel, category_carousel, footer_links, static_content or services_content')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    /**
     * The sort order.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $sort_order = null;

    /**
     * The channel id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $channel_id = null;

    /**
     * The theme code.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $theme_code = null;

    /**
     * The status.
     */
    #[ApiProperty(description: 'Published on/off state')]
    #[Groups(['mutation'])]
    public ?bool $status = null;

    /**
     * The locale.
     */
    #[ApiProperty(description: 'Locale the options belong to; defaults to the channel locale')]
    #[Groups(['mutation'])]
    public ?string $locale = null;

    /**
     * The options published for the locale.
     *
     * @var array<string, mixed>|null
     */
    #[ApiProperty(description: 'Published options for the locale')]
    #[Groups(['mutation'])]
    public ?array $options = null;
}
