<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminAppearanceSectionPublishInput
{
    #[ApiProperty(description: 'Theme whose staged edits are being published')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty(description: 'Channel being published; defaults to the current channel')]
    #[Groups(['mutation'])]
    public ?int $channel = null;
}
