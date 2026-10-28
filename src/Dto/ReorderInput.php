<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
class ReorderInput
{
    /**
     * The ID of the order to reorder.
     */
    #[ApiProperty(
        description: 'The ID of the order to reorder from',
        required: true
    )]
    #[Groups(['mutation'])]
    public ?int $orderId = null;
}
