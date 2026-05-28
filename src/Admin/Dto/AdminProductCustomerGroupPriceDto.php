<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Schema-only DTO for a customer-group price entry embedded inside
 * AdminCatalogProduct detail.
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductCustomerGroupPriceDto
{
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $customerGroupId = null;

    #[ApiProperty(writable: false)]
    public ?int $qty = null;

    #[ApiProperty(writable: false)]
    public ?string $valueType = null;

    #[ApiProperty(writable: false)]
    public ?string $value = null;

    #[ApiProperty(writable: false)]
    public ?string $uniqueId = null;
}
