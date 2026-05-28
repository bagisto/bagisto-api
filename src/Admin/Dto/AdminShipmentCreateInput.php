<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

class AdminShipmentCreateInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $orderId = null;

    #[ApiProperty(description: 'Inventory source id all items ship from.')]
    #[Groups(['mutation'])]
    public ?int $source = null;

    #[ApiProperty(description: 'Items to ship: array of { orderItemId, inventorySourceId, quantity }.')]
    #[Groups(['mutation'])]
    public array $items = [];

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $carrierTitle = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $trackNumber = null;
}
