<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAppearanceSectionCreateInput
{
    #[ApiProperty(description: 'Theme code the section belongs to')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty(description: 'Channel the section is created for; defaults to the current channel')]
    #[Groups(['mutation'])]
    public ?int $channel = null;

    #[ApiProperty(description: 'Section name shown in the editor')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Section type: image_carousel, product_carousel, category_carousel, footer_links, static_content or services_content')]
    #[Groups(['mutation'])]
    public ?string $type = null;
}
