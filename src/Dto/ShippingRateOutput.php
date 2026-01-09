<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * ShippingRateOutput - GraphQL Output DTO for Shipping Rates
 *
 * Output for retrieving available shipping rates during checkout
 */
class ShippingRateOutput
{
    #[Groups(['query'])]
    #[ApiProperty(identifier: true, readable: true, writable: false)]
    public ?string $id = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $code = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $label = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?float $price = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedPrice = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $description = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $method = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $methodTitle = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $methodDescription = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?float $basePrice = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $baseFormattedPrice = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $carrier = null;

    #[Groups(['query'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $carrierTitle = null;
}
