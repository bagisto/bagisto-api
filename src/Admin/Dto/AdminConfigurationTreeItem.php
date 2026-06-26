<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminConfigurationTreeItem
{
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $key = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $name = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $info = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $icon = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?int $sort = null;

    /**
     * @var AdminConfigurationTreeField[]|null
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?array $fields = null;

    /**
     * @var AdminConfigurationTreeItem[]|null
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?array $children = null;
}
