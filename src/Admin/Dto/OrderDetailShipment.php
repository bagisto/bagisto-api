<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Shipment block embedded in the order detail.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderDetailShipment
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $status = null;

    public ?int $totalQty = null;

    public ?float $totalWeight = null;

    public ?string $carrierCode = null;

    public ?string $carrierTitle = null;

    public ?string $trackNumber = null;

    public ?bool $emailSent = null;

    public ?string $inventorySourceName = null;

    public ?string $createdAt = null;
}
