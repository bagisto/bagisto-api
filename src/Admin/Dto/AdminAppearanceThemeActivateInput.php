<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAppearanceThemeActivateInput
{
    #[ApiProperty(description: 'Theme code to activate')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * @var array<int, int>|null
     */
    #[ApiProperty(description: 'Channels the theme is applied to')]
    #[Groups(['mutation'])]
    public ?array $channel_ids = null;
}
