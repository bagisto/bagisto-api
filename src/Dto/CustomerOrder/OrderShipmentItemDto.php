<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderShipmentItemDto
{
    public ?int $id = null;

    public ?int $order_item_id = null;

    public ?int $qty = null;

    public ?float $weight = null;
}
