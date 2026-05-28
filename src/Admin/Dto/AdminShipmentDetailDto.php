<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminShipmentDetailDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $orderId = null;

    public ?string $status = null;

    public ?int $totalQty = null;

    public ?float $totalWeight = null;

    public ?string $carrierCode = null;

    public ?string $carrierTitle = null;

    public ?string $trackNumber = null;

    public ?bool $emailSent = null;

    public ?int $inventorySourceId = null;

    public ?string $inventorySourceName = null;

    public ?string $createdAt = null;

    public ?string $updatedAt = null;

    /** @var OrderActionItemDto[] */
    public array $items = [];
}
