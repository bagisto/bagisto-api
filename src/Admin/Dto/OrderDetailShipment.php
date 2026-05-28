<?php

namespace Webkul\BagistoApi\Admin\Dto;

/**
 * Shipment block embedded in the order detail.
 */
#[\ApiPlatform\Metadata\ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderDetailShipment
{
    #[\ApiPlatform\Metadata\ApiProperty(identifier: true)]
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
