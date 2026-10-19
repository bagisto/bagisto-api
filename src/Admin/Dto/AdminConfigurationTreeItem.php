<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminConfigurationTreeItem
{
    /**
     * The key.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $key = null;

    /**
     * The name.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $name = null;

    /**
     * The info.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $info = null;

    /**
     * The icon.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $icon = null;

    /**
     * The sort.
     */
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
