<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderShipmentDto
{
    /**
     * The id.
     */
    public ?int $id = null;

    /**
     * The shipping number.
     */
    public ?string $shipping_number = null;

    /**
     * The carrier title.
     */
    public ?string $carrier_title = null;

    /**
     * The carrier code.
     */
    public ?string $carrier_code = null;

    /**
     * The track number.
     */
    public ?string $track_number = null;

    /**
     * The total qty.
     */
    public ?int $total_qty = null;

    /**
     * The total weight.
     */
    public ?float $total_weight = null;

    /**
     * The email sent.
     */
    public ?bool $email_sent = null;

    /**
     * The created at.
     */
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    public ?string $updated_at = null;

    /**
     * The order items included in the shipment.
     *
     * @var OrderShipmentItemDto[]
     */
    #[ApiProperty(readableLink: true)]
    public array $items = [];
}
