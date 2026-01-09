<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * ShippingRateOutput - GraphQL Output DTO for Shipping Rates
 *
 * Output for retrieving available shipping rates during checkout
 */
class SubscribeToNewsletterOutput
{
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public bool $success;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public string $message;
}
