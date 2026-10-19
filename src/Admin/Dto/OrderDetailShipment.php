<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderDetailShipment
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The status.
     */
    public ?string $status = null;

    /**
     * The total qty.
     */
    public ?int $totalQty = null;

    /**
     * The total weight.
     */
    public ?float $totalWeight = null;

    /**
     * The carrier code.
     */
    public ?string $carrierCode = null;

    /**
     * The carrier title.
     */
    public ?string $carrierTitle = null;

    /**
     * The track number.
     */
    public ?string $trackNumber = null;

    /**
     * The email sent.
     */
    public ?bool $emailSent = null;

    /**
     * The inventory source name.
     */
    public ?string $inventorySourceName = null;

    /**
     * The created at.
     */
    public ?string $createdAt = null;
}
