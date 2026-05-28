<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class RefundItemInput
{
    #[ApiProperty]
    public ?int $orderItemId = null;

    #[ApiProperty]
    public ?int $quantity = null;
}
