<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAppearanceSectionPublishInput
{
    /**
     * The code.
     */
    #[ApiProperty(description: 'Theme whose staged edits are being published')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The channel.
     */
    #[ApiProperty(description: 'Channel being published; defaults to the current channel')]
    #[Groups(['mutation'])]
    public ?int $channel = null;
}
