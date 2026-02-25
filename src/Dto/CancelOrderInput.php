<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * DTO for canceling a customer order
 *
 * Defines input structure for the cancelOrder mutation
 */
#[ApiResource]
class CancelOrderInput
{
    /**
     * The ID of the order to cancel
     */
    #[ApiProperty(
        description: 'The ID of the order to cancel',
        required: true
    )]
    #[Groups(['mutation'])]
    public ?int $orderId = null;
}
