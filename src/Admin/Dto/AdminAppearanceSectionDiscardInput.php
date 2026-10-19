<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAppearanceSectionDiscardInput
{
    /**
     * The code.
     */
    #[ApiProperty(description: 'Theme whose staged edits are being discarded')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The channel.
     */
    #[ApiProperty(description: 'Channel being discarded; defaults to the current channel')]
    #[Groups(['mutation'])]
    public ?int $channel = null;
}
