<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class DownloadableProductDownloadLinkOutput
{
    /**
     * The purchased link id.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $id = null;

    /**
     * The download url.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $url = null;
}
