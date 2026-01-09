<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * PaymentMethodOutput - GraphQL Output DTO for Payment Methods
 *
 * Output for retrieving available payment methods during checkout
 */
class PaymentMethodOutput
{
    #[Groups(['query'])]
    #[ApiProperty(identifier: true, readable: true, writable: false)]
    public ?string $id = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $method = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $title = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $description = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $icon = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?array $additionalData = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $isAllowed = null;
}
