<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class PaymentMethodOutput
{
    /**
     * The id.
     */
    #[Groups(['query'])]
    #[ApiProperty(identifier: true, readable: true, writable: false)]
    public ?string $id = null;

    /**
     * The method.
     */
    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $method = null;

    /**
     * The title.
     */
    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $title = null;

    /**
     * The description.
     */
    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $description = null;

    /**
     * The icon.
     */
    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $icon = null;

    /**
     * The additional data.
     */
    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?array $additional_data = null;

    /**
     * The is allowed.
     */
    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $is_allowed = null;
}
