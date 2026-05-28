<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * One line in an Admin Shipment create body —
 * `{ orderItemId, inventorySourceId, quantity }`.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class ShipmentItemInput
{
    #[ApiProperty]
    public ?int $orderItemId = null;

    #[ApiProperty]
    public ?int $inventorySourceId = null;

    #[ApiProperty]
    public ?int $quantity = null;
}
