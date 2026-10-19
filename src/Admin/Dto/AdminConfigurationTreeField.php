<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminConfigurationTreeField
{
    /**
     * The name.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $name = null;

    /**
     * The code.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $code = null;

    /**
     * The title.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $title = null;

    /**
     * The type.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $type = null;

    /**
     * The custom view.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $customView = null;

    /**
     * The default.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $default = null;

    /**
     * The channel based.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?bool $channelBased = null;

    /**
     * The locale based.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?bool $localeBased = null;

    /**
     * The validation.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $validation = null;

    /**
     * The depends.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $depends = null;

    /**
     * The info.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $info = null;

    /**
     * The value.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public mixed $value = null;

    /**
     * @var AdminConfigurationTreeOption[]|null
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?array $options = null;
}
