<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminConfigurationTreeOption
{
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $title = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $value = null;
}
