<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderShipmentItemDto
{
    /**
     * The id.
     */
    public ?int $id = null;

    /**
     * The order item id.
     */
    public ?int $order_item_id = null;

    /**
     * The qty.
     */
    public ?int $qty = null;

    /**
     * The weight.
     */
    public ?float $weight = null;
}
