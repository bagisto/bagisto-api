<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminConfigurationTreeOption
{
    /**
     * The title.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $title = null;

    /**
     * The value.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $value = null;
}
