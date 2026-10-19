<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductCustomerGroupPriceDto
{
    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The customer group id.
     */
    #[ApiProperty(writable: false)]
    public ?int $customerGroupId = null;

    /**
     * The qty.
     */
    #[ApiProperty(writable: false)]
    public ?int $qty = null;

    /**
     * The value type.
     */
    #[ApiProperty(writable: false)]
    public ?string $valueType = null;

    /**
     * The value.
     */
    #[ApiProperty(writable: false)]
    public ?string $value = null;

    /**
     * The unique id.
     */
    #[ApiProperty(writable: false)]
    public ?string $uniqueId = null;
}
