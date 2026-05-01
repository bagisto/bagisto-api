<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderShipmentDto
{
    public ?int $id = null;

    public ?string $shipping_number = null;

    public ?string $carrier_title = null;

    public ?string $carrier_code = null;

    public ?string $track_number = null;

    public ?int $total_qty = null;

    public ?float $total_weight = null;

    public ?bool $email_sent = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;

    /** @var OrderShipmentItemDto[] */
    #[ApiProperty(readableLink: true)]
    public array $items = [];
}
