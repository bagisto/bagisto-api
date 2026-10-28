<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAppearanceSectionUpdateInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Section type: image_carousel, product_carousel, category_carousel, footer_links, static_content or services_content')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $sort_order = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $channel_id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $theme_code = null;

    #[ApiProperty(description: 'Published on/off state')]
    #[Groups(['mutation'])]
    public ?bool $status = null;

    #[ApiProperty(description: 'Locale the options belong to; defaults to the channel locale')]
    #[Groups(['mutation'])]
    public ?string $locale = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ApiProperty(description: 'Published options for the locale')]
    #[Groups(['mutation'])]
    public ?array $options = null;
}
