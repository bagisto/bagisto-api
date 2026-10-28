<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

class AdminShipmentCreateInput
{
    /**
     * The order id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $orderId = null;

    /**
     * The source.
     */
    #[ApiProperty(description: 'Inventory source id all items ship from.')]
    #[Groups(['mutation'])]
    public ?int $source = null;

    /**
     * The items.
     */
    #[ApiProperty(description: 'Items to ship: array of { orderItemId, inventorySourceId, quantity }.')]
    #[Groups(['mutation'])]
    public array $items = [];

    /**
     * The carrier title.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $carrierTitle = null;

    /**
     * The track number.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $trackNumber = null;
}
