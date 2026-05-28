<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Slim row for the admin Shipments listing (GET /api/admin/shipments).
 * Mirrors Webkul\Admin\DataGrids\Sales\OrderShipmentDataGrid.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminShipmentListDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $orderId = null;

    public ?string $orderIncrementId = null;

    public ?int $totalQty = null;

    public ?string $inventorySourceName = null;

    public ?string $shippedTo = null;

    public ?string $orderDate = null;

    public ?string $createdAt = null;
}
