<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class ShipmentItemInput
{
    /**
     * The order item id.
     */
    #[ApiProperty]
    public ?int $orderItemId = null;

    /**
     * The inventory source id.
     */
    #[ApiProperty]
    public ?int $inventorySourceId = null;

    /**
     * The quantity.
     */
    #[ApiProperty]
    public ?int $quantity = null;
}
